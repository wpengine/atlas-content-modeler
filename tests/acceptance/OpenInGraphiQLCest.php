<?php

class OpenInGraphiQLCest
{
	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a pre-filled query.
	 */
	public function i_can_open_my_model_in_graphiql(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->haveContentModel('Dog', 'Dogs');
		$I->amOnWPEngineEditContentModelPage('dogs');
		$I->wait(1);

		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Wags per minute');
		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->click('.heading .options'); // Model options button.
		$I->click('.show-in-graphiql');
		$I->switchToNextTab(); // GraphiQL opens in a new tab.
		$I->wait(1);

		$I->see('wagsPerMinute', '#graphiql');
	}
}
