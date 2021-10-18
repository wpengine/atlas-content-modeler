<?php

class CreateFieldWithReservedSlugCest {

	public function i_can_not_create_a_field_with_a_reserved_default_slug( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'id' );
		$i->seeInField( '#slug', 'id' );
		$i->click( '.open-field button.primary' );

		$i->waitForElementVisible( '.field-messages .error' );
		$i->see( 'Identifier in use or reserved', '.field-messages .error' );
	}

}
