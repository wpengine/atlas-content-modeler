<?php

class CreateRepeatableDateFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1280, 1024 );
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->click( 'Date', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Dates' );
		$i->click( '.is-repeatable' );
		$i->click( '.is-required' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );
	}

	public function i_can_see_a_date_repeatable_field_on_the_publisher_page( AcceptanceTester $i ) {
		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][dates][0]' ]
		);

		// The date field should be in focus as the first field on the page.
		$active_element = $i->executeJS( "return document.activeElement.getAttribute('name');" );
		$i->assertEquals( 'atlas-content-modeler[candy][dates][0]', $active_element );
	}

	/**
	 * Ensure a user can add a Date field to the model with a repeatable property.
	 */
	public function i_can_create_a_content_model_date_repeatable_field( AcceptanceTester $i ) {
		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][dates][0]' ]
		);
	}

	/**
	 * Ensure a user cannot save a Date field with a required repeatable property.
	 */
	public function i_cannot_save_empty_required_date_repeatable_field( AcceptanceTester $i ) {
		// Create a new Candies entry.
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );
		$i->seeElement( 'input[name="atlas-content-modeler[candy][dates][0]"]:invalid' );
	}

}
