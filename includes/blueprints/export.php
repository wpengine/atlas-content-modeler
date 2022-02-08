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
function collect_posts( $post_types = [] ) {
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
	$saved      = $wp_filesystem->put_contents( $write_path, wp_json_encode( $manifest ) );

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
