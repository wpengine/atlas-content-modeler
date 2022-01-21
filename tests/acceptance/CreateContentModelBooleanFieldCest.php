<?php

class CreateContentModelBooleanFieldCest {

	/**
	 * Ensure a user can add a boolean field to the model and see it within the list.
	 */
	public function i_can_create_a_content_model_boolean_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Boolean', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Accept Terms' );
		$i->see( '12/50', 'span.count' );
		$i->seeInField( '#slug', 'acceptTerms' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Boolean', '.field-list div.type' );
		$i->see( 'Accept Terms', '.field-list div.widest' );
	}
}
