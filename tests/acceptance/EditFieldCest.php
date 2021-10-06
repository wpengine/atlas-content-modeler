<?php

class EditFieldCest
{
	/**
	 * Ensure a user can add a text field and set it as the entry title.
	 */
	public function i_cannot_edit_a_field_slug_after_the_field_has_been_created(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->haveContentModel('Candy', 'Candies');
		$I->wait(1);

		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');
		$I->click('.open-field label.checkbox.is-title');
		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->see('Text', '.field-list div.type');
		$I->see('Name', '.field-list div.widest');
		$I->see('entry title', '.field-list div.tags');

        $I->click( '.field-list div.widest' );
		$I->see( 'Editing “Name” Field' );
        $I->seeElement( '#slug', array( 'readonly'=> true ) );
	}
}
