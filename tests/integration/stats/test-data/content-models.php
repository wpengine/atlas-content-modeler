<?php
/**
 * Content model test data.
 */

return [
	'person'  => [
		'singular' => 'Person',
		'plural'   => 'People',
		'slug'     => 'person',
		'fields'   => [
			10 => [
				'type'    => 'text',
				'id'      => '10',
				'name'    => 'Name',
				'slug'    => 'name',
				'isTitle' => true,
			],
		],
	],
	'company' => [
		'singular' => 'Company',
		'plural'   => 'Companies',
		'slug'     => 'company',
		'fields'   => [
			20 => [
				'type'    => 'text',
				'id'      => '20',
				'name'    => 'Company Name',
				'slug'    => 'companyName',
				'isTitle' => true,
			],
			21 => [
				'type'        => 'relationship',
				'id'          => '21',
				'name'        => 'Employees',
				'slug'        => 'employees',
				'cardinality' => 'many-to-many',
				'reference'   => 'person',
			],
		],
	],
];
