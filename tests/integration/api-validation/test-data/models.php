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
		'model_icon'      => 'dashicons-admin-post',
		'description'     => "A public model for testing\n",
		'fields'          => array(),
	),
	'private'        => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Private',
		'plural'          => 'Privates',
		'slug'            => 'private',
		'api_visibility'  => 'private',
		'model_icon'      => 'dashicons-admin-post',
		'description'     => 'A private model for testing',
		'fields'          => array(),
	),
	'public-fields'  => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Public-Fields',
		'plural'          => 'Publics-Fields',
		'slug'            => 'public-field',
		'api_visibility'  => 'public',
		'model_icon'      => 'dashicons-admin-post',
		'description'     => 'A public content model with fields',
		'with_front'      => false,
		'fields'          => get_test_fields(),
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
	'different-slug' => array(
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Custom Slug',
		'plural'          => 'Custom Slugs',
		'slug'            => 'different-slug',
		'api_visibility'  => 'public',
		'model_icon'      => 'dashicons-admin-post',
		'description'     => 'Singular name differs from slug.',
		'with_front'      => false,
		'fields'          => [
			'1630411590619' => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'relationship',
				'id'              => '1630411590619',
				'position'        => '170000',
				'name'            => 'many-to-many-Relationship',
				'slug'            => 'manytoManyRelationship',
				'required'        => false,
				'minChars'        => '',
				'maxChars'        => '',
				'reference'       => 'different-slug',
				'cardinality'     => 'many-to-many',
				'description'     => '',
			],
		],
	),
);
