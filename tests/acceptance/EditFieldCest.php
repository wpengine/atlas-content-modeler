<?php

class EditFieldCest {

	/**
	 * Ensure a user can add a text field and set it as the entry title.
	 */
	public function i_cannot_edit_a_field_slug_after_the_field_has_been_created( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );
		$i->click( '.open-field label.checkbox.is-title' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Text', '.field-list div.type' );
		$i->see( 'Name', '.field-list div.widest' );
		$i->see( 'entry title', '.field-list div.tags' );

		$i->click( '.field-list div.widest' );
		$i->see( 'Editing “Name” Field' );
		$i->seeElement( '#slug', array( 'readonly' => true ) );
	}
}
