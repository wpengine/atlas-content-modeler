<?php

class CreateContentModelCest {

	/**
	 * Ensure user sees a message when no content models are present.
	 */
	public function i_see_a_message_when_i_have_no_content_models( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnWPEngineContentModelPage();
		$i->wait( 1 );
		$i->see( 'have no Content Models' );
	}

	/**
	 * Ensure a content model can be created.
	 */
	public function i_can_create_a_content_model( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnWPEngineCreateContentModelPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Candy' );
		$i->fillField( [ 'name' => 'plural' ], 'Candies' );
		$i->fillField( [ 'name' => 'description' ], 'My candy content model' );
		$i->see( '22/250', 'span.count' );
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );

		$i->amOnWPEngineContentModelPage();
		$i->wait( 1 );
		$i->see( 'Candies', '.model-list' );
		$i->see( 'My candy content model', '.model-list' );
	}
}
