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
}
