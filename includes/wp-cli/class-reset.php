<?php
/**
 * WP-CLI command to reset ACM content by deleting models, taxonomies, posts,
 * taxonomy terms and media.
 *
 * `wp acm reset`
 * `wp acm reset --yes` to skip the confirmation prompt.
 * `wp acm reset --all` to delete all post types and media (core posts, pages).
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\WP_CLI;

use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;

/**
 * Reset ACM data.
 */
class Reset {
	const ACM_MODEL_OPTION = 'atlas_content_modeler_post_types';

	const ACM_TAXONOMY_OPTION = 'atlas_content_modeler_taxonomies';

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
	 * Arrays of post IDs, each keyed by model ID.
	 *
	 * @var array
	 */
	private $posts;

	/**
	 * Array of media IDs.
	 *
	 * @var array
	 */
	private $media;

	/**
	 * Stats to count deleted items of different types.
	 *
	 * @var array
	 */
	private $stats;

	/**
	 * Sets up data needed for the reset command.
	 */
	public function __construct() {
		$this->taxonomies = get_acm_taxonomies();
		$this->models     = get_registered_content_types();
		$this->posts      = $this->get_post_ids();
		$this->media      = $this->get_media_ids();
		$this->stats      = [
			'taxonomy_terms' => 0,
			'taxonomies'     => 0,
			'relationships'  => 0,
			'posts'          => 0,
			'media'          => 0,
			'models'         => 0,
		];
	}

	/**
	 * Resets ACM by deleting models, taxonomies, taxonomy terms, posts, relationship data and media items relating to ACM models.
	 *
	 * ## OPTIONS
	 *
	 * [--yes]
	 * : Skip prompt to confirm deletion.
	 *
	 * [--all]
	 * : Delete all published posts, pages, custom posts and media, not just posts and media associated with ACM models.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acm reset
	 *     wp acm reset --yes
	 *     wp acm reset --all
	 *     wp acm reset --yes --all
	 *
	 * @param array $args Options passed to the command, keyed by integer.
	 * @param array $assoc_args Options keyed by string.
	 */
	public function __invoke( $args, $assoc_args ) {
		\WP_CLI::confirm( 'Delete ACM data, including posts, media, taxonomies and models?', $assoc_args );

		$delete_all = $assoc_args['all'] ?? false;

		\WP_CLI::log( 'Deleting taxonomy terms.' );
		$this->delete_taxonomy_terms();

		\WP_CLI::log( 'Deleting ACM taxonomies.' );
		$this->delete_taxonomies();

		\WP_CLI::log( 'Deleting relationship data.' );
		$this->delete_relationships();

		\WP_CLI::log( 'Deleting posts.' );
		$this->delete_posts( $delete_all );

		\WP_CLI::log( 'Deleting media.' );
		$this->delete_media( $delete_all );

		\WP_CLI::log( 'Deleting models.' );
		$this->delete_models();

		$this->log_stats();
		\WP_CLI::success( 'ACM reset complete.' );
	}

	/**
	 * Deletes taxonomy terms
	 */
	public function delete_taxonomy_terms() {
		foreach ( $this->taxonomies as $taxonomy ) {
			$terms = get_terms(
				[
					'taxonomy'   => $taxonomy['slug'],
					'hide_empty' => false,
				]
			);
			foreach ( $terms as $term ) {
				if ( (bool) wp_delete_term( $term->term_id, $taxonomy['slug'] ) ) {
					$this->stats['taxonomy_terms']++;
				}
			}
		}
	}

	/**
	 * Deletes ACM taxonomies.
	 */
	public function delete_taxonomies() {
		if ( delete_option( self::ACM_TAXONOMY_OPTION ) ) {
			$this->stats['taxonomies'] = count( (array) $this->taxonomies );
			$this->taxonomies          = [];
		}
	}

	/**
	 * Deletes all ACM relationship data.
	 */
	public function delete_relationships() {
		global $wpdb;

		$table        = ContentConnect::instance()->get_table( 'p2p' );
		$post_to_post = esc_sql( $table->get_table_name() );

		$deleted_rows = $wpdb->query("DELETE FROM `{$post_to_post}`;"); // phpcs:ignore -- table name escaped earlier.

		if ( is_int( $deleted_rows ) ) {
			$this->stats['relationships'] = $deleted_rows;
		}
	}

	/**
	 * Deletes published posts.
	 *
	 * @param bool $delete_all Pass true to also delete post types unrelated to
	 *                         ACM, such as core posts and pages.
	 */
	public function delete_posts( $delete_all = false ) {
		if ( $delete_all ) {
			$posts = new \WP_Query(
				[
					'post_type'      => 'any',
					'posts_per_page' => -1,
				]
			);

			foreach ( $posts->posts as $post ) {
				if ( (bool) wp_delete_post( $post->ID, true ) ) {
					$this->stats['posts']++;
				}
			}
		}

		// Delete ACM-specific posts.
		foreach ( $this->posts as $post_ids ) {
			foreach ( $post_ids as $post_id ) {
				if ( (bool) wp_delete_post( $post_id, true ) ) {
					$this->stats['posts']++;
				}
			}
		}
	}

	/**
	 * Deletes media, including files and their database records.
	 *
	 * @param bool $delete_all Pass true to delete all media including files
	 *                         unrelated to ACM entries.
	 */
	public function delete_media( $delete_all = false ) {
		if ( $delete_all ) {
			$media = new \WP_Query(
				[
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'posts_per_page' => -1,
				]
			);

			foreach ( $media->posts as $post ) {
				if ( (bool) wp_delete_attachment( $post->ID, true ) ) {
					$this->stats['media']++;
				}
			}

			return;
		}

		// Otherwise, just delete ACM-specific media.
		foreach ( $this->media as $media_id ) {
			if ( (bool) wp_delete_attachment( $media_id, true ) ) {
				$this->stats['media']++;
			}
		}
	}

	/**
	 * Deletes ACM model data.
	 */
	public function delete_models() {
		if ( delete_option( self::ACM_MODEL_OPTION ) ) {
			$this->stats['models'] = count( (array) $this->models );
			$this->models          = [];
		}
	}

	/**
	 * Gets IDs of posts for all ACM models.
	 *
	 * ```
	 * [ 'cats' => [1, 2, 3], 'dogs' => [4, 5, 6] ]
	 * ```
	 *
	 * Model IDs are preserved so that `get_media_ids()` can check for media
	 * fields linked to that model and retrieve related media for each post.
	 */
	public function get_post_ids() : array {
		$posts = [];
		foreach ( $this->models as $model_id => $model ) {
			$post_ids = get_posts(
				[
					'post_type'   => $model_id,
					'numberposts' => -1,
					'fields'      => 'ids',
				]
			);

			$posts[ $model_id ] = $post_ids;
		}

		return $posts;
	}

	/**
	 * Gets all media IDs associated with ACM posts.
	 *
	 * Includes IDs stored in media fields as well as thumbnail_id.
	 */
	public function get_media_ids() : array {
		$media_ids = [];

		foreach ( $this->posts as $model_id => $post_ids ) {
			$media_fields = $this->get_media_fields( $model_id );

			foreach ( $post_ids as $post_id ) {
				$media_ids[] = get_post_meta( $post_id, '_thumbnail_id', true );

				foreach ( $media_fields as $media_field_id ) {
					$meta        = get_post_meta( $post_id, $media_field_id, true );
					$media_ids[] = $meta;
				}
			}
		}

		/**
		 * Flatten to prevent nesting of repeating media field IDs.
		 * We want a flat array with only unique media IDs at the top level.
		 */
		$media_ids_flattened = [];

		array_walk_recursive(
			$media_ids,
			function( $a ) use ( &$media_ids_flattened ) {
				$media_ids_flattened[] = $a;
			}
		);

		return array_filter( array_unique( $media_ids_flattened ) );
	}

	/**
	 * Gets media fields for the given `$model`.
	 *
	 * @param string $model_id The model ID.
	 * @return array Media field slugs for the given model.
	 */
	public function get_media_fields( $model_id ) : array {
		$fields = $this->models[ $model_id ]['fields'] ?? [];

		$media_fields = array_filter(
			$fields,
			function( $field ) {
				return $field['type'] === 'media' ?? false;
			}
		);

		return wp_list_pluck( $media_fields, 'slug' );
	}

	/**
	 * Logs stats about what was deleted.
	 */
	private function log_stats() {
		$stats_strings = [
			/* translators: 1: singular number of terms, 2: plural number of terms  */
			'taxonomy_terms' => sprintf( _n( '%s term', '%s terms', $this->stats['taxonomy_terms'], 'atlas-content-modeler' ), $this->stats['taxonomy_terms'] ),
			/* translators: 1: singular number of taxonomies, 2: plural number of taxonomies  */
			'taxonomies'     => sprintf( _n( '%s taxonomy', '%s taxonomies', $this->stats['taxonomies'], 'atlas-content-modeler' ), $this->stats['taxonomies'] ),
			/* translators: 1: singular number of relationships, 2: plural number of relationships  */
			'relationships'  => sprintf( _n( '%s relationship', '%s relationships', $this->stats['relationships'], 'atlas-content-modeler' ), $this->stats['relationships'] ),
			/* translators: 1: singular number of posts, 2: plural number of posts  */
			'posts'          => sprintf( _n( '%s post', '%s posts', $this->stats['posts'], 'atlas-content-modeler' ), $this->stats['posts'] ),
			/* translators: 1: singular number of media, 2: plural number of media  */
			'media'          => sprintf( _n( '%s media', '%s media', $this->stats['media'], 'atlas-content-modeler' ), $this->stats['media'] ),
			/* translators: 1: singular number of models, 2: plural number of models  */
			'models'         => sprintf( _n( '%s model', '%s models', $this->stats['models'], 'atlas-content-modeler' ), $this->stats['models'] ),
		];

		/**
		 * Puts highest counts first for friendlier output.
		 * "9 posts, 0 media, 0 models" instead of "0 media, 0 models, 9 posts”.
		 */
		arsort( $this->stats, SORT_NUMERIC );

		/**
		 * Replaces plain stat counts with the above strings, modifying the
		 * $stats class property in place.
		 * Input: `['posts' => 23, 'models' => 1]`
		 * Output: `['posts' => '23 posts', 'models' => '1 model']`
		 */
		array_walk(
			$this->stats,
			function( &$stat_value, $stat_key ) use ( $stats_strings ) {
				$stat_value = $stats_strings[ $stat_key ];
			}
		);

		\WP_CLI::log( 'Deleted: ' . join( ', ', $this->stats ) . '.' );
	}
}
