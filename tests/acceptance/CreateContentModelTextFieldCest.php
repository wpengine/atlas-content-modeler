<?php

class CreateContentModelTextFieldCest {

	/**
	 * Ensure a user can add a text field to the model and see it within the list.
	 */
	public function i_can_create_a_content_model_text_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->see( '5/50', 'span.count' );
		$i->seeInField( '#slug', 'color' );
		$i->fillField( [ 'name' => 'description' ], 'Description.' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Text', '.field-list div.type' );
		$i->see( 'Color', '.field-list div.widest' );
	}

	/**
	 * Ensure a user can add a text field to the model with a repeatable property.
	 */
	public function i_can_create_a_content_model_text_repeatable_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Color' );

		// Set the Input Type to “multiple lines” instead of “Single line”.
		$i->click( '.is-repeatable' );

		// Save the field.
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Create a new Candies entry.
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		// Check that the first input in the index of available list items is rendering indicating a multiple being set.
		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][color][0]' ]
		);

		// The first text field should be in focus as the first field on the page.
		$active_element = $i->executeJS( "return document.activeElement.getAttribute('name');" );
		$i->assertEquals( 'atlas-content-modeler[candy][color][0]', $active_element );
    
    // Confirm new repeating text inputs gain focus when they are added.
		$i->click( 'Add Item', '.add-option' );
		$active_element = $i->executeJS( "return document.activeElement.getAttribute('name');" );
		$i->assertEquals( 'atlas-content-modeler[candy][color][1]', $active_element );
	}

	public function i_can_create_a_content_model_text_field_as_a_textarea( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->seeInField( '#slug', 'color' );

		// Set the Input Type to “multiple lines” instead of “Single line”.
		$i->click( '#multi' );

		// Save the field.
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Create a new Candies entry.
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		// Check that the “Color” field uses a textarea instead of an input field.
		$i->seeElement(
			'textarea',
			[ 'name' => 'atlas-content-modeler[candy][color]' ]
		);
	}
}
