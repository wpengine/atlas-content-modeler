<?php

use Codeception\Util\Locator;

class CreateFieldWithReservedSlugCest {

	/**
	 * Checks that a field does not use one of the reserved default slugs
	 * hard-coded in `includes/settings/reserved-field-slugs.php`.
	 */
	public function i_can_not_create_a_field_with_a_reserved_default_slug( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'id' );
		$i->seeInField( '#slug', 'id' );
		$i->click( '.open-field button.primary' );

		$i->waitForElementVisible( '.field-messages .error' );
		$i->see( 'Identifier in use or reserved', '.field-messages .error' );
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
		$i->click( '.open-field button.primary' );
		$i->waitForElement( '.add-item' );

		$i->click( Locator::lastElement( '.add-item' ) );
		$i->wait( 1 ); // Allow some extra time for the WPGraphQL introspection request that fires when opening a new field.
		$i->click( 'Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->click( '#slug' ); // Triggers onBlur event on the name field to fire the slug collision check.

		$i->waitForElementVisible( '.field-messages .error' );
		$i->see( 'Identifier in use or reserved', '.field-messages .error' );
	}

}
