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

	/**
	 * Ensure a user can add a featured image media field to the model and see it within the list.
	 */
	public function i_can_add_a_featured_image_media_field_to_a_content_model( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Primary Image' );
		$i->see( '17/50', 'span.count' );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'The Primary Image', '.field-list div.widest' );
	}

	/**
	 * Ensure a user can only add one featured image media field to the model.
	 */
	public function i_can_only_add_one_featured_image_media_field_to_a_content_model( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Primary Image' );
		$i->see( '17/50', 'span.count' );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'The Primary Image', '.field-list div.widest' );

		$i->click( '.add-item' );
		$i->wait( 1 );

		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Second Image' );
		$i->see( '16/50', 'span.count' );
		$i->seeInField( '#slug', 'theSecondImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->wait( 1 );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->dontSeeCheckboxIsChecked( 'isFeatured ' );
	}

	/**
	 * Ensure a featured image field is properly denoted in publisher.
	 */
	public function i_can_denote_a_featured_image_field_in_publisher( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Primary Image' );
		$i->see( '17/50', 'span.count' );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'The Primary Image', '.field-list div.widest' );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );
		$i->seeInField( '.button-primary', 'Add Featured Image' );
	}
}
