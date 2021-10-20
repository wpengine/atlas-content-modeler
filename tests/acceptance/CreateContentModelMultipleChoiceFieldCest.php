<?php

class CreateContentModelMultipleChoiceFieldCest {

	/**
	 * Ensure a user can add a multile choice field from a model and remove it.
	 */
	public function i_can_add_a_multiple_choice_field_to_a_content_model( AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->haveContentModel( 'Candy', 'Candies' );
		$I->wait( 1 );
		$I->click( 'Multiple Choice', '.field-buttons' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$I->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$I->click( 'Add another choice', '.add-option' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[1].name' ], 'cat' );
		$I->fillField( [ 'name' => 'choices[1].slug' ], 'catSlug' );
		$I->click( '.open-field button.primary' );
		$I->wait( 1 );
		$I->see( 'Multiple Choice' );
		$I->see( 'Favorite Animal' );
		$I->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$I->wait( 1 );
		$I->seeInField( 'choices[0].name', 'dog' );
		$I->see( 'Editing “Favorite Animal” Field', '.field-list' );
		$I->click( '.remove-option', '.choices[0].container' );
		$I->wait( 1 );
		$I->seeInField( 'choices[0].name', 'cat' );
	}

	/**
	 * Ensure a user cannot create two options with the same key value api identifier.
	 */
	public function i_cannot_add_a_duplicate_identifier_name_for_two_choices( AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->haveContentModel( 'Candy', 'Candies' );
		$I->wait( 1 );
		$I->click( 'Multiple Choice', '.field-buttons' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$I->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$I->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$I->click( 'Add another choice', '.add-option' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[1].name' ], 'dog 2' );
		$I->fillField( [ 'name' => 'choices[1].slug' ], 'dogSlug' );
		$I->click( '.open-field button.primary' );
		$I->wait( 1 );
		$I->see( 'Cannot have duplicate identifier.' );
		// Ensure the errors are clearing when changing the problem field.
		$I->fillField( [ 'name' => 'choices[1].slug' ], 'dogSlug2' );
		$I->dontSee( 'Cannot have duplicate identifier.' );
	}

	/**
	 * Ensure a user cannot save a choice with no name or slug.
	 */
	public function i_cannot_save_a_blank_choice( AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->haveContentModel( 'Candy', 'Candies' );
		$I->wait( 1 );
		$I->click( 'Multiple Choice', '.field-buttons' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$I->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$I->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$I->click( 'Add another choice', '.add-option' );
		$I->wait( 1 );
		$I->click( '.open-field button.primary' );
		$I->wait( 1 );
		$I->see( 'Must set a name.' );
		// Ensure the errors are clearing when changing the problem field.
		$I->fillField( [ 'name' => 'choices[1].name' ], 'cat' );
		$I->dontSee( 'Must set a name.' );
	}

	/**
	 * Ensure a user cannot create two choices with the same name.
	 */
	public function i_cannot_add_a_duplicate_name_for_two_choices( AcceptanceTester $I ): void {
		$I->loginAsAdmin();
		$I->haveContentModel( 'Candy', 'Candies' );
		$I->wait( 1 );
		$I->click( 'Multiple Choice', '.field-buttons' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$I->wait( 1 );
		$I->click( 'Add another choice', '.add-option' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[1].name' ], 'dog' );
		$I->wait( 1 );
		$I->fillField( [ 'name' => 'choices[1].slug' ], 'dogSlug2' );
		$I->wait( 1 );
		$I->click( '.open-field button.primary' );
		$I->wait( 1 );
		$I->see( 'Cannot have duplicate choice names.' );
		// Ensure the errors are clearing when changing the problem field.
		$I->fillField( [ 'name' => 'choices[1].name' ], 'dog2' );
		$I->dontSee( 'Cannot have duplicate choice names.' );
	}
}
