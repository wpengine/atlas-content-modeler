<?php
/**
 * Sets up REST endpoints and callbacks used internally by React applications.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API;

add_action( 'init', __NAMESPACE__ . '\atlas_content_modeler_rest_init' );
/**
 * Sets up REST API endpoints and callbacks.
 *
 * @return void
 */
function atlas_content_modeler_rest_init(): void {
	$rest_files = array(
		// Business logic to manipulate models, fields and taxonomies.
		'graphql.php',
		'models.php',
		'fields.php',
		'taxonomies.php',
		// REST routes resolving to `/wp-json/wp/v2/wpe/atlas/[filename]`.
		'routes/content-model.php',
		'routes/content-models.php',
		'routes/content-model-field.php',
		'routes/content-model-fields.php',
		'routes/taxonomy.php',
		'routes/dismiss-feedback-banner.php',
		'routes/validate-field.php',
	);

	foreach ( $rest_files as $file ) {
		include_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . 'rest-api/' . $file;
	}
}
