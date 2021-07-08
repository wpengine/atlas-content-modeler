<?php

class OpenInGraphiQLCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->loginAsAdmin();
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query when the model has no fields.
	 */
	public function i_can_open_model_with_no_fields_in_graphiql(AcceptanceTester $I)
	{
		$I->haveContentModel('Dog', 'Dogs');
		$I->wait(1);

		$I->click('.heading .options'); // Model options button.
		$I->click('.show-in-graphiql');
		$I->switchToNextTab(); // GraphiQL opens in a new tab.
		$I->wait(2); // Give GraphiQL time for linting.

		$I->see('Dog', '#graphiql');
		// Check for query errors marked by GraphiQL linter.
		$I->dontSeeElementInDOM('#graphiql .CodeMirror-lint-mark-error');
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query, including the model's fields.
	 */
	public function i_can_open_a_model_with_fields_in_graphiql(AcceptanceTester $I)
	{
		$I->haveContentModel('Dog', 'Dogs');
		$I->wait(1);

		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Wags per minute');
		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->click('.heading .options'); // Model options button.
		$I->click('.show-in-graphiql');
		$I->switchToNextTab(); // GraphiQL opens in a new tab.
		$I->wait(2); // Give GraphiQL time for linting.

		$I->see('wagsPerMinute', '#graphiql');
		// Check for query errors marked by GraphiQL linter.
		$I->dontSeeElementInDOM('#graphiql .CodeMirror-lint-mark-error');
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query when the model singular and plural names are not capitalized.
	 */
	public function i_can_open_model_with_lowercased_model_names_in_graphiql(AcceptanceTester $I)
	{
		$I->haveContentModel('cat', 'cats');
		$I->wait(1);

		$I->click('.heading .options'); // Model options button.
		$I->click('.show-in-graphiql');
		$I->switchToNextTab(); // GraphiQL opens in a new tab.
		$I->wait(2); // Give GraphiQL time for linting.

		$I->see('Cat', '#graphiql');
		// Check for query errors marked by GraphiQL linter.
		$I->dontSeeElementInDOM('#graphiql .CodeMirror-lint-mark-error');
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query when the model singular and plural names are identical.
	 */
	public function i_can_open_model_with_same_singular_and_plural_name_in_graphiql(AcceptanceTester $I)
	{
		$I->haveContentModel('deer', 'deer');
		$I->wait(1);

		$I->click('.heading .options'); // Model options button.
		$I->click('.show-in-graphiql');
		$I->switchToNextTab(); // GraphiQL opens in a new tab.
		$I->wait(2); // Give GraphiQL time for linting.

		$I->see('Deer', '#graphiql');
		// Check for query errors marked by GraphiQL linter.
		$I->dontSeeElementInDOM('#graphiql .CodeMirror-lint-mark-error');
	}
}
