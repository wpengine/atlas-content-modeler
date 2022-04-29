<?php

class CreateContentModelEmailFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Movie', 'Movies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
	}

	public function i_can_create_a_content_model_email_field( AcceptanceTester $i ) {
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

	public function i_can_create_a_content_model_email_repeater_field( \AcceptanceTester $i ) {
		$email = 'Email';
		$i->click( $email, '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], $email );
		$i->click( '.is-repeatable' );

		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=movie' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[movie][email][0]' ]
		);

		$active_element = $i->executeJS( "return document.activeElement.getAttribute('name');" );
		$i->assertEquals( 'atlas-content-modeler[movie][email][0]', $active_element );
	}
}


