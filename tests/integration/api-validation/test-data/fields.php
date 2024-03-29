<?php
/**
 * Sample field data for testing
 */
function get_test_fields() {
	return array(
		'1630411218064' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'text',
			'id'              => '1630411218064',
			'position'        => '0',
			'name'            => 'Single-Line',
			'slug'            => 'singleLine',
			'inputType'       => 'single',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411257237' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'text',
			'id'              => '1630411257237',
			'position'        => '10000',
			'name'            => 'Single-Line-Required',
			'slug'            => 'singleLineRequired',
			'isTitle'         => true,
			'inputType'       => 'single',
			'required'        => true,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1642799164165' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'text',
			'id'              => '1642799164165',
			'position'        => '10000',
			'name'            => 'Single-Line-Text-Repeater',
			'slug'            => 'singleLineTextRepeater',
			'isTitle'         => false,
			'isRepeatable'    => true,
			'inputType'       => 'single',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411276223' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'text',
			'id'              => '1630411276223',
			'position'        => '20000',
			'name'            => 'Single-Line-Limited',
			'slug'            => 'singleLineLimited',
			'isTitle'         => false,
			'inputType'       => 'single',
			'required'        => false,
			'minChars'        => 10,
			'maxChars'        => 200,
		),
		'1630411296844' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'text',
			'id'              => '1630411296844',
			'position'        => '30000',
			'name'            => 'Multi-Line',
			'slug'            => 'multiLine',
			'isTitle'         => false,
			'inputType'       => 'multi',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411306418' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'richtext',
			'id'              => '1630411306418',
			'position'        => '40000',
			'name'            => 'Rich-Text',
			'slug'            => 'richText',
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411318747' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'number',
			'id'              => '1630411318747',
			'position'        => '50000',
			'name'            => 'Number-Integer',
			'slug'            => 'numberInteger',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'numberType'      => 'integer',
			'minValue'        => '',
			'maxValue'        => '',
			'step'            => '',
		),
		'1630411332185' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'number',
			'id'              => '1630411332185',
			'position'        => '60000',
			'name'            => 'Number-Decimal',
			'slug'            => 'numberDecimal',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'numberType'      => 'decimal',
			'minValue'        => '',
			'maxValue'        => '',
			'step'            => '',
		),
		'1630411352654' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'number',
			'id'              => '1630411352654',
			'position'        => '70000',
			'name'            => 'Number-Interger-Required',
			'slug'            => 'numberIntergerRequired',
			'required'        => true,
			'minChars'        => '',
			'maxChars'        => '',
			'numberType'      => 'integer',
			'minValue'        => '',
			'maxValue'        => '',
			'step'            => '',
		),
		'1630411364971' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'number',
			'id'              => '1630411364971',
			'position'        => '80000',
			'name'            => 'Number-Integer-Limited',
			'slug'            => 'numberIntegerLimited',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'numberType'      => 'integer',
			'minValue'        => 1,
			'maxValue'        => 10,
			'step'            => 2,
		),
		'1630411384975' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'date',
			'id'              => '1630411384975',
			'position'        => '90000',
			'name'            => 'Date',
			'slug'            => 'dateNotRequired',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411395253' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'date',
			'id'              => '1630411395253',
			'position'        => '100000',
			'name'            => 'Date-Required',
			'slug'            => 'dateRequired',
			'required'        => true,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411405899' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'media',
			'id'              => '1630411405899',
			'position'        => '110000',
			'name'            => 'Media',
			'slug'            => 'media',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'allowedTypes'    => '',
		),
		'1630411419039' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'media',
			'id'              => '1630411419039',
			'position'        => '120000',
			'name'            => 'Media-Required',
			'slug'            => 'mediaRequired',
			'required'        => true,
			'minChars'        => '',
			'maxChars'        => '',
			'allowedTypes'    => '',
		),
		'1630411429321' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'media',
			'id'              => '1630411429321',
			'position'        => '130000',
			'name'            => 'Media-PDF',
			'slug'            => 'mediaPDF',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'allowedTypes'    => 'pdf',
		),
		'1630411446543' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'boolean',
			'id'              => '1630411446543',
			'position'        => '140000',
			'name'            => 'Boolean',
			'slug'            => 'boolean',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411456473' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'boolean',
			'id'              => '1630411456473',
			'position'        => '150000',
			'name'            => 'Boolean-Required',
			'slug'            => 'booleanRequired',
			'required'        => true,
			'minChars'        => '',
			'maxChars'        => '',
		),
		'1630411556098' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'relationship',
			'id'              => '1630411556098',
			'position'        => '160000',
			'name'            => 'many-to-one-Relationship',
			'slug'            => 'manytoOneRelationship',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'reference'       => 'private',
			'cardinality'     => 'many-to-one',
			'description'     => '',
		),
		'1630411590613' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'relationship',
			'id'              => '1630411590613',
			'position'        => '170000',
			'name'            => 'many-to-many-Relationship',
			'slug'            => 'manytoManyRelationship',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'reference'       => 'public',
			'cardinality'     => 'many-to-many',
			'description'     => '',
		),
		'1630411592343' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'relationship',
			'id'              => '1630411592343',
			'position'        => '180000',
			'name'            => 'Many-to-Many-Relationship-Reverse',
			'slug'            => 'manytoManyRelationshipReverse',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'reference'       => 'public',
			'cardinality'     => 'many-to-many',
			'description'     => '',
			'enableReverse'   => true,
			'reverseName'     => 'PPosts',
			'reverseSlug'     => 'pposts' . random_int( 0, PHP_INT_MAX ),
		),
		'1636398361391' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'multipleChoice',
			'id'              => '1636398361391',
			'position'        => '190000',
			'name'            => 'multiple-choice-single',
			'slug'            => 'multiSingle',
			'listType'        => 'single',
			'choices'         =>
				array(
					0 =>
					array(
						'name' => 'red apple',
						'slug' => 'apple',
					),
					1 =>
					array(
						'name' => 'yellow banana',
						'slug' => 'banana',
					),
					2 =>
					array(
						'name' => 'green kiwi',
						'slug' => 'kiwi',
					),
				),
			'required'        => true,
		),
		'1636398361392' => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'multipleChoice',
			'id'              => '1636398361392',
			'position'        => '200000',
			'name'            => 'multiple-choice-multi',
			'slug'            => 'multipleMulti',
			'listType'        => 'multiple',
			'choices'         =>
			array(
				0 =>
				array(
					'name' => 'red apple',
					'slug' => 'apple',
				),
				1 =>
				array(
					'name' => 'yellow banana',
					'slug' => 'banana',
				),
				2 =>
				array(
					'name' => 'green kiwi',
					'slug' => 'kiwi',
				),
			),
			'required'        => true,
		),
		'1637243600'    => array(
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'type'            => 'media',
			'id'              => '1637243600',
			'position'        => '190000',
			'name'            => 'Media-Featured',
			'slug'            => 'mediaFeatured',
			'required'        => false,
			'minChars'        => '',
			'maxChars'        => '',
			'allowedTypes'    => '',
			'isFeatured'      => true,
		),
		'1646070396'    => array(
			'show_in_rest'       => true,
			'show_in_graphql'    => true,
			'type'               => 'number',
			'id'                 => '1646070396',
			'position'           => '200000',
			'name'               => 'Number-Integer-Repeat',
			'slug'               => 'numberIntegerRepeat',
			'required'           => false,
			'minChars'           => '',
			'maxChars'           => '',
			'numberType'         => 'integer',
			'isRepeatableNumber' => 'true',
			'minValue'           => '',
			'maxValue'           => '',
			'step'               => '',
		),
		'1646070397'    => array(
			'show_in_rest'         => true,
			'show_in_graphql'      => true,
			'type'                 => 'richtext',
			'id'                   => '1646070397',
			'position'             => '400000',
			'name'                 => 'RichTextRepeatable',
			'slug'                 => 'richTextRepeatable',
			'required'             => false,
			'isRepeatableRichText' => 'true',
		),
		'1646070398'    => array(
			'show_in_rest'      => true,
			'show_in_graphql'   => true,
			'type'              => 'media',
			'id'                => '1646070397',
			'position'          => '300000',
			'name'              => 'Media-Repeat',
			'slug'              => 'mediaRepeat',
			'required'          => false,
			'isRepeatableMedia' => 'true',
			'minChars'          => '',
			'maxChars'          => '',
			'allowedTypes'      => '',
		),
		'1648153658'    => array(
			'show_in_rest'     => true,
			'show_in_graphql'  => true,
			'type'             => 'date',
			'id'               => '1648153658',
			'position'         => '400000',
			'name'             => 'Date-Repeatable',
			'slug'             => 'dateRepeatable',
			'isRepeatableDate' => 'true',
			'required'         => false,
			'minChars'         => '',
			'maxChars'         => '',
		),
		'1648828035'    => array(
			'show_in_rest'     => true,
			'show_in_graphql'  => true,
			'type'             => 'email',
			'id'               => '1648828035',
			'position'         => '400000',
			'name'             => 'Email',
			'slug'             => 'email',
			'isRepeatableDate' => 'true',
			'required'         => false,
			'minChars'         => '',
			'maxChars'         => '',
		),
		'1651005478'    => array(
			'show_in_rest'      => true,
			'show_in_graphql'   => true,
			'type'              => 'email',
			'id'                => '1651005478',
			'position'          => '400000',
			'name'              => 'Email-Repeater',
			'slug'              => 'emailRepeater',
			'isRepeatableEmail' => 'true',
			'required'          => true,
			'minChars'          => '',
			'maxChars'          => '',
		),
	);
}
