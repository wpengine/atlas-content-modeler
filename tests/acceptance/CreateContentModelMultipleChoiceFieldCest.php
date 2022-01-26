<?php

class CreateContentModelMultipleChoiceFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
	}

	/**
	 * Ensure a user can add a multile choice field from a model and remove it.
	 */
	public function i_can_add_a_multiple_choice_field_to_a_content_model( AcceptanceTester $i ): void {
		$i->click( 'Multiple Choice (Beta)', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$i->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$i->click( 'Add another choice', '.add-option' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[1].name' ], 'cat' );
		$i->fillField( [ 'name' => 'choices[1].slug' ], 'catSlug' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Multiple Choice' );
		$i->see( 'Favorite Animal' );
		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->wait( 1 );
		$i->seeInField( 'choices[0].name', 'dog' );
		$i->see( 'Editing “Favorite Animal” Field', '.field-list' );
		$i->click( '.remove-option', '.choices[0].remove-container' );
		$i->wait( 1 );
		$i->seeInField( 'choices[0].name', 'cat' );
	}

	/**
	 * Ensure a user cannot create two options with the same key value api identifier.
	 */
	public function i_cannot_add_a_duplicate_identifier_name_for_two_choices( AcceptanceTester $i ): void {
		$i->click( 'Multiple Choice (Beta)', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$i->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$i->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$i->click( 'Add another choice', '.add-option' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[1].name' ], 'dog 2' );
		$i->fillField( [ 'name' => 'choices[1].slug' ], 'dogSlug' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Cannot have duplicate identifier.' );
		// Ensure the errors are clearing when changing the problem field.
		$i->fillField( [ 'name' => 'choices[1].slug' ], 'dogSlug2' );
		$i->dontSee( 'Cannot have duplicate identifier.' );
	}

	/**
	 * Ensure a user cannot save a choice with no name or slug.
	 */
	public function i_cannot_save_a_blank_choice( AcceptanceTester $i ): void {
		$i->click( 'Multiple Choice (Beta)', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$i->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$i->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$i->click( 'Add another choice', '.add-option' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Must set a name.' );
		// Ensure the errors are clearing when changing the problem field.
		$i->fillField( [ 'name' => 'choices[1].name' ], 'cat' );
		$i->dontSee( 'Must set a name.' );
	}

	/**
	 * Ensure a user cannot create two choices with the same name.
	 */
	public function i_cannot_add_a_duplicate_name_for_two_choices( AcceptanceTester $i ): void {
		$i->click( 'Multiple Choice (Beta)', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Favorite Animal' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[0].name' ], 'dog' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[0].slug' ], 'dogSlug' );
		$i->wait( 1 );
		$i->click( 'Add another choice', '.add-option' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[1].name' ], 'dog' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'choices[1].slug' ], 'dogSlug2' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Cannot have duplicate choice names.' );
		// Ensure the errors are clearing when changing the problem field.
		$i->fillField( [ 'name' => 'choices[1].name' ], 'dog2' );
		$i->dontSee( 'Cannot have duplicate choice names.' );
	}
}
