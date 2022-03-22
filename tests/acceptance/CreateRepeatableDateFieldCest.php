<?php

class CreateContentModelRepeatableDateCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1280, 1024 );
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->click( 'Date', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'dates' );
		$i->click( '.is-repeatable' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );
	}

}
