<?php

class CreateContentModelEmailFieldCest {
	public function i_can_create_a_content_model_email_field( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$email = 'Email';
		$i->click( $email, '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], $email );
		$i->see( '5/50', 'span.count' );
		$i->seeInField( '#slug', 'email' );
		$i->fillField( [ 'name' => 'description' ], 'Description.' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( $email, '.field-list div.type' );
		$i->see( $email, '.field-list div.widest' );
	}
}


