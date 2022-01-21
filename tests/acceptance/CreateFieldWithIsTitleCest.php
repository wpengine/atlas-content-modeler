<?php

use Codeception\Util\Locator;

class CreateFieldWithIsTitleCest {

	/**
	 * Ensure a user can add a text field and set it as the entry title.
	 */
	public function i_can_create_a_content_model_text_field_with_is_title( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

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
	}

	public function i_can_not_create_a_second_text_field_with_is_title( AcceptanceTester $i ) {
		$this->i_can_create_a_content_model_text_field_with_is_title( $i );

		// Add a second text field.
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Text', '.field-buttons' );

		// Should not be possible to set another text field as the title.
		$i->dontSee( 'Use this field as the entry title', '.field-form' );
	}
}
