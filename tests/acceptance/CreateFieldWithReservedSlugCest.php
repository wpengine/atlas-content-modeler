<?php

use Codeception\Util\Locator;

class CreateFieldWithReservedSlugCest {

	/**
	 * Checks that a field does not use a reserved slug that was registered by
	 * WPGraphQL itself.
	 *
	 * The 'title' slug is not allowed by default unless 'isTitle' is also true.
	 *
	 * @covers WPE\AtlasContentModeler\REST_API\GraphQL\is_allowed_field_id
	 */
	public function i_can_not_create_a_field_with_a_reserved_default_slug( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'title' );
		$i->seeInField( '#slug', 'title' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );

		$i->waitForElementVisible( '.field-messages .error' );
		$i->see( 'Identifier in use or reserved', '.field-messages .error' );
	}

	/**
	 * Checks that a text field with a 'title' slug is allowed when 'use this
	 * field as the entry title' is ticked.
	 *
	 * @covers WPE\AtlasContentModeler\REST_API\GraphQL\is_field_id_exception
	 */
	public function i_can_create_a_title_field_with_a_slug_of_title( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'title' );
		$i->seeInField( '#slug', 'title' );
		$i->checkOption( 'input[name="isTitle"]' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Text', '.field-list div.type' );
		$i->see( 'Title', '.field-list div.widest' );
		$i->see( 'entry title', '.field-list div.tags' );
	}

	/**
	 * Checks that a field slug registered with WPGraphQL at runtime cannot be
	 * used as the slug for a new field within the same model.
	 *
	 * For simplicity, this test checks for a collision with another ACM field.
	 * Because ACM fields are registered with WPGraphQL at runtime, this test
	 * covers conflicts for other plugins such as `wordpress-seo` combined with
	 * `add-wpgraphql-seo` (which registers an “seo” field with WPGraphQL).
	 * This removes the need to install those plugins to run a conflict test.
	 */
	public function i_can_not_create_a_field_with_a_reserved_dynamic_slug( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->waitForElement( '.add-item' );

		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->waitForElement( '.add-item' );

		$i->waitForElementVisible( '.field-messages .error' );
		$i->see( 'Identifier in use or reserved', '.field-messages .error' );
	}

}
