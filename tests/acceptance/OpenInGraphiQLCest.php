<?php

class OpenInGraphiQLCest {

	public function _before( \AcceptanceTester $i ) {
		$i->loginAsAdmin();
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query when the model has no fields.
	 */
	public function i_can_open_model_with_no_fields_in_graphiql( AcceptanceTester $i ) {
		$content_model = $i->haveContentModel( 'Dog', 'Dogs' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( '.heading .options' ); // Model options button.
		$i->click( '.show-in-graphiql' );
		$i->switchToNextTab(); // GraphiQL opens in a new tab.
		$i->wait( 2 ); // Give GraphiQL time for linting.

		$i->see( 'Dog', '#graphiql' );
		// Check for query errors marked by GraphiQL linter.
		$i->dontSeeElementInDOM( '#graphiql .CodeMirror-lint-mark-error' );
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query, including the model's fields.
	 */
	public function i_can_open_a_model_with_fields_in_graphiql( AcceptanceTester $i ) {
		$content_model = $i->haveContentModel( 'Dog', 'Dogs' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Wags per minute' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.heading .options' ); // Model options button.
		$i->click( '.show-in-graphiql' );
		$i->switchToNextTab(); // GraphiQL opens in a new tab.
		$i->wait( 2 ); // Give GraphiQL time for linting.

		$i->see( 'wagsPerMinute', '#graphiql' );
		// Check for query errors marked by GraphiQL linter.
		$i->dontSeeElementInDOM( '#graphiql .CodeMirror-lint-mark-error' );
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query when the model singular and plural names are not capitalized.
	 */
	public function i_can_open_model_with_lowercased_model_names_in_graphiql( AcceptanceTester $i ) {
		$content_model = $i->haveContentModel( 'cat', 'cats' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( '.heading .options' ); // Model options button.
		$i->click( '.show-in-graphiql' );
		$i->switchToNextTab(); // GraphiQL opens in a new tab.
		$i->wait( 2 ); // Give GraphiQL time for linting.

		$i->see( 'Cat', '#graphiql' );
		// Check for query errors marked by GraphiQL linter.
		$i->dontSeeElementInDOM( '#graphiql .CodeMirror-lint-mark-error' );
	}

	/**
	 * Ensure the “Open in GraphiQL” model option opens GraphiQL with a valid pre-filled
	 * query when the model singular and plural names are identical.
	 */
	public function i_can_open_model_with_same_singular_and_plural_name_in_graphiql( AcceptanceTester $i ) {
		$content_model = $i->haveContentModel( 'deer', 'deer' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( '.heading .options' ); // Model options button.
		$i->click( '.show-in-graphiql' );
		$i->switchToNextTab(); // GraphiQL opens in a new tab.
		$i->wait( 2 ); // Give GraphiQL time for linting.

		$i->see( 'Deer', '#graphiql' );
		// Check for query errors marked by GraphiQL linter.
		$i->dontSeeElementInDOM( '#graphiql .CodeMirror-lint-mark-error' );
	}
}
