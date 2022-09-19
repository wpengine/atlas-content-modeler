<?php
/**
 * WP-CLI command to alter and migrate ACM model data.
 *
 * `wp acm model change-id [oldid] [newid]`
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\WP_CLI;

require_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . '/wp-cli/trait-acm-log.php';

use WP_Error;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\reserved_post_types;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Adjust ACM model data.
 */
class Model {
	use ACM_Log;

	/**
	 * ACM taxonomies.
	 *
	 * @var array
	 */
	private $taxonomies;

	/**
	 * ACM models.
	 *
	 * @var array
	 */
	private $models;

	/**
	 * Reserved post types.
	 *
	 * @var array
	 */
	private $reserved_post_types;

	/**
	 * Other post types, including those registered by other plugins.
	 *
	 * @var array
	 */
	private $post_types;

	/**
	 * Sets up data needed for the reset command.
	 */
	public function __construct() {
		$this->taxonomies          = get_acm_taxonomies();
		$this->models              = get_registered_content_types();
		$this->reserved_post_types = reserved_post_types();
		$this->post_types          = get_post_types();
	}

	/**
	 * Change a model's ID (post type slug), migrating posts and taxonomy data.
	 *
	 * ## OPTIONS
	 *
	 * <oldid>
	 * : The original ID of the model to change.
	 *
	 * <newid>
	 * : The new ID of the model. 20 character max, lowercase alphanumeric, underscores and hyphens only.
	 *
	 * [--yes]
	 * : Skip prompt to confirm model ID change.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acm model change-id oldid newid
	 *
	 * @subcommand change-id
	 * @param array $args Options passed to the command, keyed by integer.
	 * @param array $assoc_args Options keyed by string.
	 * @return bool True if ID change succeeded. Useful when running outside of the context of WP_CLI.
	 * @throws \Exception Exception if ID change failed and function is run outside of the context of WP_CLI.
	 */
	public function change_id( $args, $assoc_args = [] ) {
		$old_id = $args[0];
		$new_id = $args[1];

		if ( ! array_key_exists( $old_id, $this->models ) ) {
			self::error( sprintf( 'No model exists with the old ID of ‘%s’.', $old_id ) );
		}

		$is_new_model_id_valid = $this->is_new_model_id_valid( $new_id );

		if ( is_wp_error( $is_new_model_id_valid ) ) {
			self::error( $is_new_model_id_valid );
		}

		self::confirm( sprintf( 'Change model ID from ‘%1$s’ to ‘%2$s’?', $old_id, $new_id ), $assoc_args );

		$model_id_changed = $this->update_model_id( $old_id, $new_id );

		if ( ! $model_id_changed ) {
			self::error( 'Model ID update failed.' );
		} else {
			self::log( 'Model ID updated.' );
		}

		$posts_changed = $this->change_post_type( $old_id, $new_id );

		if ( is_numeric( $posts_changed ) ) {
			self::log(
				/* translators: 1: singular number of posts, 2: plural number of posts  */
				sprintf( _n( '%s post updated.', '%s posts updated.', $posts_changed, 'atlas-content-modeler' ), $posts_changed )
			);
		}

		$taxonomies_changed = $this->update_taxonomy_ids( $old_id, $new_id );

		if ( $taxonomies_changed ) {
			self::log( 'Taxonomies updated.' );
		}

		self::success( sprintf( 'Model ID changed from ‘%1$s’ to ‘%2$s’.', $old_id, $new_id ) );

		return true;
	}

	/**
	 * Checks if `$new_id` is valid and available for use as an ACM model.
	 *
	 * @param string $new_id The new model ID.
	 * @return bool|WP_Error True if valid, else WPError with explanation in message.
	 */
	private function is_new_model_id_valid( string $new_id ) {
		if ( strlen( $new_id ) > 20 ) {
			return new WP_Error(
				'acm_invalid_model_id',
				__( 'New model ID must not exceed 20 characters.', 'atlas-content-modeler' )
			);
		}

		if ( is_numeric( $new_id[0] ) ) {
			return new WP_Error(
				'acm_invalid_model_id',
				__( 'New model ID must not start with a number.', 'atlas-content-modeler' )
			);
		}

		if ( sanitize_key( $new_id ) !== $new_id ) {
			return new WP_Error(
				'acm_invalid_model_id',
				__( 'New model ID must only contain lowercase alphanumeric characters, underscores and hyphens.', 'atlas-content-modeler' )
			);
		}

		if ( array_key_exists( $new_id, $this->models ) ) {
			return new WP_Error(
				'acm_invalid_model_id',
				// translators: model name.
				sprintf( __( 'New ID of ‘%s’ is in use by another model.', 'atlas-content-modeler' ), $new_id )
			);
		}

		if ( in_array( $new_id, $this->reserved_post_types, true ) ) {
			return new WP_Error(
				'acm_invalid_model_id',
				// translators: model name.
				sprintf( __( 'New ID of ‘%s’ is reserved or in use by WordPress Core.', 'atlas-content-modeler' ), $new_id )
			);
		}

		if ( in_array( $new_id, $this->post_types, true ) ) {
			return new WP_Error(
				'acm_invalid_model_id',
				// translators: model name.
				sprintf( __( 'New ID of ‘%s’ is in use by a custom post type.', 'atlas-content-modeler' ), $new_id )
			);
		}

		return true;
	}

	/**
	 * Updates the ACM model ID from `$old_type` to `$new_type`.
	 *
	 * Also updates references to `$old_type` in field data, such as the
	 * 'reference' key in relationship fields.
	 *
	 * @param string $old_type Old model ID.
	 * @param string $new_type New model ID.
	 * @return boolean True if content type was updated, false if update failed or `$old_type` does not exist.
	 */
	private function update_model_id( string $old_type, string $new_type ) {
		if ( ! array_key_exists( $old_type, $this->models ) ) {
			return false;
		}

		$this->models[ $new_type ] = $this->models[ $old_type ];
		unset( $this->models[ $old_type ] );

		$this->models[ $new_type ]['slug'] = $new_type;

		$this->replace_relationship_references( $old_type, $new_type );

		return update_registered_content_types( $this->models );
	}

	/**
	 * Changes post types from `$old_type` to `$new_type` in the WP posts table.
	 *
	 * @param string $old_type Old model ID. Should be an existing post type slug.
	 * @param string $new_type New model ID. Confirm this is a valid post slug before passing to this function.
	 * @return int|bool Number of rows affected or false on error.
	 */
	private function change_post_type( string $old_type, string $new_type ) {
		global $wpdb;

		return $wpdb->query( // phpcs:ignore -- direct database call for speed/simplicity.
			$wpdb->prepare(
				"UPDATE `{$wpdb->posts}` SET `post_type` = %s WHERE `post_type` = %s;",
				sanitize_key( $new_type ),
				sanitize_key( $old_type )
			)
		);
	}

	/**
	 * Updates ACM taxonomies that reference `$old_type` to use `$new_type`.
	 *
	 * @param string $old_type Old model ID.
	 * @param string $new_type New model ID.
	 * @return bool True if taxonomy data changed, false otherwise.
	 */
	private function update_taxonomy_ids( string $old_type, string $new_type ) {
		foreach ( $this->taxonomies as $taxonomy => $data ) {
			if ( ! array_key_exists( 'types', $data ) ) {
				continue;
			}

			$this->taxonomies[ $taxonomy ]['types'] = str_replace(
				$old_type,
				$new_type,
				$data['types']
			);
		}

		return update_option( 'atlas_content_modeler_taxonomies', $this->taxonomies );
	}

	/**
	 * Replaces `$old_type` with `$new_type` in relationship field references.
	 *
	 * Example: `replace_relationship_references( 'old', 'new' )` turns a field
	 * with these properties:
	 *
	 * `[ 'type' => 'relationship', 'reference' => 'old', … ]`
	 *
	 * Into this:
	 *
	 * `[ 'type' => 'relationship', 'reference' => 'new', … ]`
	 *
	 * The `$this->models` property is mutated directly, so nothing is returned.
	 * Callers should do `update_registered_content_types( $this->models )` if
	 * they want to commit changes to the WP database.
	 *
	 * @param string $old_type Old model ID.
	 * @param string $new_type New model ID.
	 */
	private function replace_relationship_references( string $old_type, string $new_type ) {
		foreach ( $this->models as $model_id => $model_data ) {
			if ( ! array_key_exists( 'fields', $model_data ) ) {
				continue;
			}

			foreach ( $model_data['fields'] as $field_id => $field_data ) {
				if ( $field_data['type'] !== 'relationship' ) {
					continue;
				}

				if ( ! array_key_exists( 'reference', $field_data ) ) {
					continue;
				}

				$this->models[ $model_id ]['fields'][ $field_id ]['reference'] = str_replace(
					$old_type,
					$new_type,
					$field_data['reference']
				);
			}
		}
	}
}
