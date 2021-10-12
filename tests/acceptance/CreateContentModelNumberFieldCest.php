<?php

class CreateContentModelNumberFieldCest {

	/**
	 * Ensure a user can add a number field to the model and see it within the list.
	 */
	public function i_can_add_a_number_field_to_a_content_model( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Count' );
		$i->see( '5/50', 'span.count' );
		$i->seeInField( '#slug', 'count' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Number', '.field-list div.type' );
		$i->see( 'Count', '.field-list div.widest' );
	}

	public function i_can_add_a_step_setting_without_a_max_setting( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Number', '.field-buttons' );
		$i->wait( 1 );

		$i->click( 'button.settings' );
		$i->fillField( [ 'name' => 'minValue' ], '1' );
		$i->fillField( [ 'name' => 'step' ], '2' );
		$i->dontSee( 'Step must be lower than max.', '.error' );
	}
}
