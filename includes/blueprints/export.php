<?php
/**
 * Functions that export ACM blueprints.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Blueprint\Export;

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
