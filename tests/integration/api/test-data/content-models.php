<?php
/**
 * Content model test data.
 */

return [
	'person'     => [
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Person',
		'plural'          => 'Persons',
		'slug'            => 'person',
		'api_visibility'  => 'private',
		'model_icon'      => 'dashicons-admin-post',
		'description'     => '',
		'fields'          => [
			1648575961490 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'text',
				'id'              => '1648575961490',
				'position'        => '0',
				'name'            => 'Name',
				'slug'            => 'name',
				'isRepeatable'    => false,
				'isTitle'         => true,
				'inputType'       => 'single',
				'required'        => true,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
			],
			1648576059444 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'relationship',
				'id'              => '1648576059444',
				'position'        => '10000',
				'name'            => 'Cars',
				'slug'            => 'cars',
				'required'        => false,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
				'reference'       => 'car',
				'cardinality'     => 'one-to-many',
				'enableReverse'   => false,
				'reverseName'     => 'Persons',
				'reverseSlug'     => 'persons',
			],
			1649187797304 => [
				'show_in_rest'         => true,
				'show_in_graphql'      => true,
				'type'                 => 'richtext',
				'id'                   => '1649187797304',
				'position'             => '20000',
				'name'                 => 'ReechText',
				'slug'                 => 'reechText',
				'description'          => '',
				'minChars'             => '',
				'maxChars'             => '',
				'minRepeatable'        => '',
				'maxRepeatable'        => '',
				'isRepeatableRichText' => false,
			],
			1649187864231 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'multipleChoice',
				'id'              => '1649187864231',
				'position'        => '30000',
				'name'            => 'Choize',
				'slug'            => 'choize',
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
				'choices'         => [
					0 => [
						'name' => 'test 1',
						'slug' => 'test1',
					],
					1 => [
						'name' => 'test 2',
						'slug' => 'test2',
					],
					2 => [
						'name' => 'test 3',
						'slug' => 'test3',
					],
				],
				'listType'        => 'single',
			],
		],
	],
	'car'        => [
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Car',
		'plural'          => 'Cars',
		'slug'            => 'car',
		'api_visibility'  => 'private',
		'model_icon'      => 'dashicons-admin-post',
		'description'     => '',
		'fields'          => [
			1648575981679 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'text',
				'id'              => '1648575981679',
				'position'        => '0',
				'name'            => 'Name',
				'slug'            => 'name',
				'isRepeatable'    => false,
				'isTitle'         => true,
				'inputType'       => 'single',
				'required'        => true,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
			],
			1648660598654 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'text',
				'id'              => '1648660598654',
				'position'        => '10000',
				'name'            => 'Test',
				'slug'            => 'test',
				'isRepeatable'    => false,
				'inputType'       => 'single',
				'required'        => false,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
			],
		],
	],
	'validation' => [
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'singular'        => 'Validation',
		'plural'          => 'Validations',
		'slug'            => 'validation',
		'api_visibility'  => 'private',
		'model_icon'      => 'dashicons-admin-post',
		'description'     => 'Used for testing validation',
		'fields'          => [
			1649787479673 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'text',
				'id'              => '1649787479673',
				'position'        => '0',
				'name'            => 'Text Field',
				'slug'            => 'textField',
				'isRepeatable'    => false,
				'isTitle'         => true,
				'inputType'       => 'single',
				'required'        => false,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
			],
			1649787498608 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'text',
				'id'              => '1649787498608',
				'position'        => '10000',
				'name'            => 'Repeatable Text Field',
				'slug'            => 'repeatableTextField',
				'isRepeatable'    => true,
				'inputType'       => 'single',
				'required'        => false,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
			],
			1649787509847 => [
				'show_in_rest'         => true,
				'show_in_graphql'      => true,
				'type'                 => 'richtext',
				'id'                   => '1649787509847',
				'position'             => '20000',
				'name'                 => 'Rich Text Field',
				'slug'                 => 'richTextField',
				'description'          => '',
				'minChars'             => '',
				'maxChars'             => '',
				'minRepeatable'        => '',
				'maxRepeatable'        => '',
				'isRepeatableRichText' => false,
			],
			1649787528544 => [
				'show_in_rest'         => true,
				'show_in_graphql'      => true,
				'type'                 => 'richtext',
				'id'                   => '1649787528544',
				'position'             => '30000',
				'name'                 => 'Repeatable Rich Text Field',
				'slug'                 => 'repeatableRichTextField',
				'description'          => '',
				'minChars'             => '',
				'maxChars'             => '',
				'minRepeatable'        => '',
				'maxRepeatable'        => '',
				'isRepeatableRichText' => true,
			],
			1649787543496 => [
				'show_in_rest'       => true,
				'show_in_graphql'    => true,
				'type'               => 'number',
				'id'                 => '1649787543496',
				'position'           => '40000',
				'name'               => 'Number Field',
				'slug'               => 'numberField',
				'required'           => false,
				'description'        => '',
				'minChars'           => '',
				'maxChars'           => '',
				'minRepeatable'      => '',
				'maxRepeatable'      => '',
				'isRepeatableNumber' => false,
				'numberType'         => 'integer',
				'minValue'           => '',
				'maxValue'           => '',
				'step'               => '',
			],
			1649787560968 => [
				'show_in_rest'       => true,
				'show_in_graphql'    => true,
				'type'               => 'number',
				'id'                 => '1649787560968',
				'position'           => '50000',
				'name'               => 'Repeatable Number Field',
				'slug'               => 'repeatableNumberField',
				'required'           => false,
				'description'        => '',
				'minChars'           => '',
				'maxChars'           => '',
				'minRepeatable'      => '',
				'maxRepeatable'      => '',
				'isRepeatableNumber' => true,
				'numberType'         => 'integer',
				'minValue'           => '',
				'maxValue'           => '',
				'step'               => '',
			],
			1649787611492 => [
				'show_in_rest'     => true,
				'show_in_graphql'  => true,
				'type'             => 'date',
				'id'               => '1649787611492',
				'position'         => '60000',
				'name'             => 'Date Field',
				'slug'             => 'dateField',
				'required'         => false,
				'description'      => '',
				'minChars'         => '',
				'maxChars'         => '',
				'minRepeatable'    => '',
				'maxRepeatable'    => '',
				'isRepeatableDate' => false,
			],
			1649787623430 => [
				'show_in_rest'     => true,
				'show_in_graphql'  => true,
				'type'             => 'date',
				'id'               => '1649787623430',
				'position'         => '70000',
				'name'             => 'Repeatable Date Field',
				'slug'             => 'repeatableDateField',
				'required'         => false,
				'description'      => '',
				'minChars'         => '',
				'maxChars'         => '',
				'minRepeatable'    => '',
				'maxRepeatable'    => '',
				'isRepeatableDate' => true,
			],
			1649787634770 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'boolean',
				'id'              => '1649787634770',
				'position'        => '80000',
				'name'            => 'Boolean Field',
				'slug'            => 'booleanField',
				'required'        => false,
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
			],
			1649787666652 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'multipleChoice',
				'id'              => '1649787666652',
				'position'        => '90000',
				'name'            => 'Single Multiple Choice Field',
				'slug'            => 'singleMultipleChoiceField',
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
				'choices'         => [
					0 => [
						'name' => 'Choice 1',
						'slug' => 'choice1',
					],
					1 => [
						'name' => 'Choice 2',
						'slug' => 'choice2',
					],
					2 => [
						'name' => 'Choice 3',
						'slug' => 'choice3',
					],
				],
				'listType'        => 'single',
			],
			1649787701753 => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'type'            => 'multipleChoice',
				'id'              => '1649787701753',
				'position'        => '100000',
				'name'            => 'Multi Multiple Choice Field',
				'slug'            => 'multiMultipleChoiceField',
				'description'     => '',
				'minChars'        => '',
				'maxChars'        => '',
				'minRepeatable'   => '',
				'maxRepeatable'   => '',
				'choices'         => [
					0 => [
						'name' => 'Choice 1',
						'slug' => 'choice1',
					],
					1 => [
						'name' => 'Choice 2',
						'slug' => 'choice2',
					],
					2 => [
						'name' => 'Choice 3',
						'slug' => 'choice3',
					],
				],
				'listType'        => 'multiple',
			],
			1649789115852 => [
				'show_in_rest'      => true,
				'show_in_graphql'   => true,
				'type'              => 'media',
				'id'                => '1649789115852',
				'position'          => '110000',
				'name'              => 'Media Field',
				'slug'              => 'mediaField',
				'isRepeatableMedia' => 'false',
				'required'          => false,
				'isFeatured'        => false,
				'description'       => '',
				'allowedTypes'      => '',
			],
			1651779403259 =>
			[
				'show_in_rest'      => true,
				'show_in_graphql'   => true,
				'type'              => 'media',
				'id'                => '1651779403259',
				'position'          => '120000',
				'name'              => 'Repeatable Media Field',
				'slug'              => 'repeatableMediaField',
				'required'          => false,
				'description'       => '',
				'minChars'          => '',
				'maxChars'          => '',
				'minRepeatable'     => '',
				'maxRepeatable'     => '',
				'isRepeatableMedia' => 'true',
				'isFeatured'        => false,
				'allowedTypes'      => '',
			],
			1653338178066 =>
			[
				'show_in_rest'      => true,
				'show_in_graphql'   => true,
				'type'              => 'email',
				'id'                => '1653338178066',
				'position'          => '5000',
				'name'              => 'Email Field',
				'slug'              => 'emailField',
				'required'          => false,
				'description'       => '',
				'minChars'          => '',
				'maxChars'          => '',
				'minRepeatable'     => '',
				'maxRepeatable'     => '',
				'isRepeatableEmail' => false,
				'allowedDomains'    => '',
			],
			1654024380884 =>
			[
				'show_in_rest'      => true,
				'show_in_graphql'   => true,
				'type'              => 'email',
				'id'                => '1654024380884',
				'position'          => '10000',
				'name'              => 'Repeatable Email Field',
				'slug'              => 'repeatableEmailField',
				'isRepeatableEmail' => true,
				'required'          => false,
				'description'       => '',
				'allowedDomains'    => '',
				'minRepeatable'     => '',
				'maxRepeatable'     => '',
				'exactRepeatable'   => '',
			],
		],
	],
];
