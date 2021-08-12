<?php

/**
 * Sample model data for testing
 */

require_once __DIR__ . '/fields.php';

return array(
	'public'         => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Public',
		'plural'          => 'Publics',
		'slug'            => 'public',
		'api_visibility'  => 'public',
		'model_icon'      => 'dashicons-saved',
		'description'     => 'A public content model',
		'fields'          => array(),
	),
	'public-fields'  => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Public-Fields',
		'plural'          => 'Publics-Fields',
		'slug'            => 'publics-fields',
		'api_visibility'  => 'public',
		'model_icon'      => 'dashicons-saved',
		'description'     => 'A public content model with fields',
		'fields'          => get_test_fields(),
	),
	'private'        => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Private',
		'plural'          => 'Privates',
		'slug'            => 'private',
		'api_visibility'  => 'private',
		'model_icon'      => 'dashicons-saved',
		'description'     => 'A private content model',
		'fields'          => array(),
	),
	'private-fields' => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Private-Fields',
		'plural'          => 'Privates-Fields',
		'slug'            => 'privates-fields',
		'api_visibility'  => 'private',
		'model_icon'      => 'dashicons-saved',
		'description'     => 'A private model with fields',
		'fields'          => get_test_fields(),
	),
	'relationship-ref' => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Relationship Reference',
		'plural'          => 'Relationship References',
		'slug'            => 'relationship-ref',
		'api_visibility'  => 'public',
		'model_icon'      => 'dashicons-saved',
		'description'     => 'A model for referencing in relationship fields.',
		'fields'          => array(),
	),
	'attachment'     => array(
		'slug'     => 'attachment',
		'singular' => 'Attachment',
		'plural'   => 'Attachments',
		'fields'   => array(),
	),
);
