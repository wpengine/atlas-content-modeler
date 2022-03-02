<?php

class CreateContentModelNumberFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Employee', 'Employees' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
	}

	/**
	 * Ensure a user can add a number field to the model and see it within the list.
	 */
	public function i_can_add_a_number_field_to_a_content_model( AcceptanceTester $i ) {
		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Count' );
		$i->see( '5/50', 'span.count' );
		$i->seeInField( '#slug', 'count' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Number', '.field-list div.type' );
		$i->see( 'Count', '.field-list div.widest' );
	}

	public function i_can_add_a_step_setting_without_a_max_setting( AcceptanceTester $i ) {
		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );

		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minValue' ], '1' );
		$i->fillField( [ 'name' => 'step' ], '2' );
		$i->dontSee( 'Step must be lower than max.', '.error' );
	}

	public function i_must_use_intergers_for_advanced_interger_field_settings( AcceptanceTester $i ) {
		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );

		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minValue' ], '1.2' );
		$i->see( 'The value must be an integer.', '.error' );
		$i->fillField( [ 'name' => 'minValue' ], '1' );
		$i->dontSee( 'The value must be an integer.', '.error' );

		$i->fillField( [ 'name' => 'maxValue' ], '1.2' );
		$i->see( 'The value must be an integer.', '.error' );
		$i->fillField( [ 'name' => 'maxValue' ], '1' );
		$i->dontSee( 'The value must be an integer.', '.error' );

		$i->fillField( [ 'name' => 'step' ], '1.2' );
		$i->see( 'The value must be an integer.', '.error' );
		$i->fillField( [ 'name' => 'step' ], '1' );
		$i->dontSee( 'The value must be an integer.', '.error' );
	}

	/**
	 * Ensure a user can add a Number field to the model with a repeatable property.
	 */
	public function i_can_create_a_content_model_number_repeatable_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'positionxyz' );

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
			[ 'name' => 'atlas-content-modeler[candy][positionxyz][0]' ]
		);
	}

	/**
	 * Ensure a user cannot save a Number field with a required repeatable property.
	 */
	public function i_cannot_save_empty_required_number_repeatable_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'positionxyz' );

		$i->click( '.is-repeatable' );
		$i->click( '.is-required' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Create a new Candies entry.
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );
		$i->seeElement( 'input[name="atlas-content-modeler[candy][positionxyz][0]"]:invalid' );
	}

}
