<?php
/**
 * Sample field data for testing
 */

function get_test_fields(){

    return array(
        '1628083572151' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'text',
            'id' => '1628083572151',
            'position' => '0',
            'name' => 'Single-Line',
            'slug' => 'singleLine',
            'required' => false,
            'inputType' => 'single',
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084420404' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'text',
            'id' => '1628084420404',
            'position' => '10000',
            'name' => 'Single-Line-Required',
            'slug' => 'singleLineRequired',
            'required' => true,
            'isTitle' => true,
            'inputType' => 'single',
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084435181' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'text',
            'id' => '1628084435181',
            'position' => '20000',
            'name' => 'Single-Line-Limited',
            'slug' => 'singleLineLimited',
            'required' => false,
            'isTitle' => false,
            'inputType' => 'single',
            'minChars' => 10,
            'maxChars' => 280
        ),
        '1628084465380' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'text',
            'id' => '1628084465380',
            'position' => '30000',
            'name' => 'Multi-Line',
            'slug' => 'multiLine',
            'required' => false,
            'isTitle' => false,
            'inputType' => 'multi',
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084488045' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'richtext',
            'id' => '1628084488045',
            'position' => '40000',
            'name' => 'Rich-Text',
            'slug' => 'richText',
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084499767' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'number',
            'id' => '1628084499767',
            'position' => '50000',
            'name' => 'Number-Integer',
            'slug' => 'numberInteger',
            'required' => false,
            'minChars' => '',
            'maxChars' => '',
            'numberType' => 'integer',
            'minValue' => '',
            'maxValue' => '',
            'step' => ''
        ),
        '1628084512254' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'number',
            'id' => '1628084512254',
            'position' => '60000',
            'name' => 'Number-Decimal',
            'slug' => 'numberDecimal',
            'required' => false,
            'minChars' => '',
            'maxChars' => '',
            'numberType' => 'decimal',
            'minValue' => '',
            'maxValue' => '',
            'step' => ''
        ),
        '1628084527723' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'number',
            'id' => '1628084527723',
            'position' => '70000',
            'name' => 'Number-Interger-Required',
            'slug' => 'numberIntergerRequired',
            'required' => true,
            'minChars' => '',
            'maxChars' => '',
            'numberType' => 'integer',
            'minValue' => '',
            'maxValue' => '',
            'step' => ''
        ),
        '1628084535867' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'number',
            'id' => '1628084535867',
            'position' => '80000',
            'name' => 'Number-Integer-Limited',
            'slug' => 'numberIntegerLimited',
            'required' => false,
            'minChars' => '',
            'maxChars' => '',
            'numberType' => 'integer',
            'minValue' => 1,
            'maxValue' => 10,
            'step' => 2
        ),
        '1628084760779' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'date',
            'id' => '1628084760779',
            'position' => '90000',
            'name' => 'Date',
            'slug' => 'date',
            'required' => false,
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084773117' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'date',
            'id' => '1628084773117',
            'position' => '100000',
            'name' => 'Date-Required',
            'slug' => 'dateRequired',
            'required' => true,
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084908530' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'media',
            'id' => '1628084908530',
            'position' => '110000',
            'name' => 'Media',
            'slug' => 'media',
            'required' => false,
            'minChars' => '',
            'maxChars' => '',
            'allowedTypes' => ''
        ),
        '1628084918406' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'media',
            'id' => '1628084918406',
            'position' => '120000',
            'name' => 'Media-Required',
            'slug' => 'mediaRequired',
            'required' => true,
            'minChars' => '',
            'maxChars' => '',
            'allowedTypes' => ''
        ),
        '1628084929251' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'media',
            'id' => '1628084929251',
            'position' => '130000',
            'name' => 'Media-PDF',
            'slug' => 'mediaPDF',
            'required' => false,
            'minChars' => '',
            'maxChars' => '',
            'allowedTypes' => 'pdf'
        ),
        '1628084952497' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'boolean',
            'id' => '1628084952497',
            'position' => '140000',
            'name' => 'Boolean',
            'slug' => 'boolean',
            'required' => false,
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628084963946' => array(
            'show_in_rest' => true,
            'show_in_graphql' => true,
            'type' => 'boolean',
            'id' => '1628084963946',
            'position' => '150000',
            'name' => 'Boolean-Required',
            'slug' => 'booleanRequired',
            'required' => true,
            'minChars' => '',
            'maxChars' => ''
        ),
        '1628083572341' => array(
            'show_in_rest' => false,
            'show_in_graphql' => false,
            'type' => 'text',
            'id' => '1628083572341',
            'position' => '160000',
            'name' => 'Hidden-Field',
            'slug' => 'hiddenField',
            'required' => false,
            'inputType' => 'single',
            'minChars' => '',
            'maxChars' => ''
        ),
    );
}
