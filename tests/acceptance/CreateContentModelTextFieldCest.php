<?php

class CreateContentModelTextFieldCest {

	/**
	 * Ensure a user can add a text field to the model and see it within the list.
	 */
	public function i_can_create_a_content_model_text_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->see( '5/50', 'span.count' );
		$i->seeInField( '#slug', 'color' );
		$i->fillField( [ 'name' => 'description' ], 'Description.' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Text', '.field-list div.type' );
		$i->see( 'Color', '.field-list div.widest' );
	}

	public function i_can_create_a_content_model_text_field_as_a_textarea( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->seeInField( '#slug', 'color' );

		// Set the Input Type to “multiple lines” instead of “Single line”.
		$i->click( '#multi' );

		// Save the field.
		$i->click( '.open-field button.primary' );
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
