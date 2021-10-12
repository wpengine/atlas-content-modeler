<?php

class CreateContentModelMediaFieldCest {

	/**
	 * Ensure a user can add a media field to the model and see it within the list.
	 */
	public function i_can_add_a_media_field_to_a_content_model( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Product Photo' );
		$i->see( '13/50', 'span.count' );
		$i->seeInField( '#slug', 'productPhoto' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'Product Photo', '.field-list div.widest' );
	}
}
