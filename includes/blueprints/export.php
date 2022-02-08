<?php
/**
 * Functions that export ACM blueprints.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Blueprint\Export;

use WP_Error;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

/**
 * Generates meta data for ACM blueprints.
 *
 * @param array $args Optional overrides for default meta.
 * @return array
 */
function generate_meta( array $args = [] ): array {
	$acm_blueprint_schema_version = '1.0';
	$plugin                       = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );

	$defaults = [
		'version'     => '1.0',
		'name'        => __( 'ACM Blueprint', 'atlas-content-modeler' ),
		'description' => '',
		'min-wp'      => get_bloginfo( 'version' ),
		'min-acm'     => $plugin['Version'],
	];

	$args = wp_parse_args( $args, $defaults );

	return [
		'schema'      => $acm_blueprint_schema_version,
		'version'     => $args['version'],
		'name'        => $args['name'],
		'description' => $args['description'],
		'requires'    => [
			'wordpress' => $args['min-wp'],
			'acm'       => $args['min-acm'],
		],
	];
}

/**
 * Collects posts for the manifest file.
 *
 * @param array $post_types Optional overrides for post types to collect.
 * @return array
 */
function collect_posts( array $post_types = [] ): array {
	if ( empty( $post_types ) ) {
		$post_types = array_merge(
			array_keys( get_registered_content_types() ),
			[ 'post', 'page' ]
		);
	}

	$posts = get_posts(
		[
			'post_status' => 'publish',
			'post_type'   => $post_types,
			'numberposts' => -1, // All posts.
		]
	);

	$posts_keyed_by_id = [];

	foreach ( $posts as $post ) {
		$post = $post->to_array();
		unset( $post['guid'] ); // Strips the URL of the generating site. GUIDs are regenerated on import.
		$posts_keyed_by_id[ $post['ID'] ] = $post;
	}

	return $posts_keyed_by_id;
}

/**
 * Collects terms for the passed `$taxonomies`.
 *
 * @param array $taxonomies Taxonomy slugs to collect terms for.
 * @return array Term data.
 */
function collect_terms( array $taxonomies ): array {
	$term_data = [];

	foreach ( $taxonomies as $taxonomy ) {
		$terms = get_terms( [ 'taxonomy' => $taxonomy ] );

		if ( ! is_wp_error( $terms ) ) {
			$terms_as_arrays = array_map(
				fn( $term ) => $term->to_array(),
				$terms
			);

			$term_data = array_merge( $term_data, $terms_as_arrays );
		}
	}

	return $term_data;
}

/**
 * Collects post tags for the passed `$taxonomies` and `$posts`.
 *
 * @param array $posts Posts to get tags for.
 * @param array $taxonomies Taxonomy slugs to collect tags from.
 * @return array Term arrays keyed by post ID.
 */
function collect_post_tags( array $posts, array $taxonomies ): array {
	$tag_data       = [];
	$acm_post_types = array_keys( get_registered_content_types() );

	foreach ( $posts as $post ) {
		// Only collect tags for ACM post types.
		if ( ! in_array( $post['post_type'], $acm_post_types, true ) ) {
			continue;
		}

		foreach ( $taxonomies as $taxonomy ) {
			$tags = get_the_terms( $post['ID'], $taxonomy );

			if ( $tags && ! is_wp_error( $tags ) ) {
				$tags_as_arrays = array_map(
					fn( $tag ) => $tag->to_array(),
					$tags
				);
			}

			if ( ! empty( $tags_as_arrays ) ) {
				$tag_data[ $post['ID'] ] = array_merge(
					$tag_data[ $post['ID'] ] ?? [],
					$tags_as_arrays
				);
			}
		}
	}

	return $tag_data;
}

/**
 * Writes the acm.json manifest file to the given `$path`.
 *
 * @param array  $manifest ACM manifest data.
 * @param string $path Where to write the manifest file.
 * @return string|WP_Error The path to manifest or an error if the file could
 *                         not be written.
 */
function write_manifest( array $manifest, string $path ) {
	global $wp_filesystem;

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$path = trailingslashit( $path );

	if ( ! $wp_filesystem->exists( $path ) ) {
		wp_mkdir_p( $path );
	}

	$write_path = $path . 'acm.json';
	$saved      = $wp_filesystem->put_contents( $write_path, wp_json_encode( $manifest, JSON_PRETTY_PRINT ) );

	if ( ! $saved ) {
		return new WP_Error(
			'acm_manifest_write_error',
			/* translators: full path to file. */
			sprintf( esc_html__( 'Error saving temporary file to %s', 'atlas-content-modeler' ), $write_path )
		);
	}

	return $write_path;
}

/**
 * Gives the path to a directory where blueprint files can be temporarily
 * written before they are compressed for download.
 *
 * @param array $manifest The full ACM manifest file, used to determine the
 *                        name of the directory.
 * @return string|WP_error The temporary directory path or an error if the
 *                         manifest name is missing.
 */
function get_acm_temp_dir( $manifest ) {
	if ( empty( $manifest['meta']['name'] ?? '' ) ) {
		return new WP_Error(
			'acm_manifest_name_missing',
			esc_html__( 'The manifest has no meta.name property.', 'atlas-content-modeler' )
		);
	}

	$temp_dir    = get_temp_dir();
	$folder_name = sanitize_title_with_dashes( $manifest['meta']['name'] );

	return "{$temp_dir}acm/{$folder_name}/";
}
