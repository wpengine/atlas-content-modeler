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
		$i->click( 'button[data-testid="create-model-button"]' );
		$i->wait( 1 );

		$i->amOnWPEngineContentModelPage();
		$i->wait( 1 );
		$i->see( 'Candies', '.model-list' );
		$i->see( 'My candy content model', '.model-list' );
	}

	public function i_see_a_warning_if_the_model_singular_name_is_reserved( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnWPEngineCreateContentModelPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Post' ); // 'post' is in use.
		$i->fillField( [ 'name' => 'plural' ], 'Candies' );
		$i->fillField( [ 'name' => 'slug' ], 'Candy' );
		$i->click( 'button[data-testid="create-model-button"]' );
		$i->wait( 1 );

		$i->see( 'singular name is in use', '.card-content' );
	}

	public function i_see_a_warning_if_the_model_names_lead_with_numbers( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnWPEngineCreateContentModelPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], '3dobject' ); // Leading singular with a number is not supported and can fatal error a site.
		$i->fillField( [ 'name' => 'plural' ], '3dobjects' );
		$i->fillField( [ 'name' => 'slug' ], 'threeobject' );
		$i->click( 'button[data-testid="create-model-button"]' );
		$i->wait( 1 );

		$i->see( 'The singular name cannot lead with a number', '.card-content' );

		$i->fillField( [ 'name' => 'singular' ], 'threeobject' );
		$i->fillField( [ 'name' => 'plural' ], '3dobjects' ); // Leading plural with a number is not supported and can fatal error a site.
		$i->fillField( [ 'name' => 'slug' ], 'threeobject' );
		$i->click( 'button[data-testid="create-model-button"]' );
		$i->wait( 1 );

		$i->see( 'The plural name cannot lead with a number', '.card-content' );
	}


	public function i_see_a_warning_if_the_model_plural_name_is_reserved( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnWPEngineCreateContentModelPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Candy' );
		$i->fillField( [ 'name' => 'plural' ], 'Posts' ); // 'posts' is in use.
		$i->click( 'button[data-testid="create-model-button"]' );
		$i->wait( 1 );

		$i->see( 'plural name is in use', '.card-content' );
	}

	public function i_see_a_warning_if_the_model_id_is_reserved( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnWPEngineCreateContentModelPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Type' ); // Results in 'type' for model ID, which is reserved.
		$i->fillField( [ 'name' => 'plural' ], 'Types' );
		$i->click( 'button[data-testid="create-model-button"]' );
		$i->wait( 1 );

		$i->see( 'model ID reserved or in use', '.card-content' );
	}
}
