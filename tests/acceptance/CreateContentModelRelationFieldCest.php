<?php

use Codeception\Util\Locator;

class CreateContentModelRelationFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Employee', 'Employees' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		// Create a text field to check for reverse relationship conflicts with a reference model.
		$i->click( 'Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'conflictsWithEmployeesSlug' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$content_model = $i->haveContentModel( 'Company', 'Companies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->wait( 1 );
		$i->click( 'Relation', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Company Employees' );
	}

	public function i_can_create_a_relation_field( AcceptanceTester $i ) {
		$i->selectOption( '#reference', 'Employees' );
		$i->click( '#many-to-many' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Relationship', '.field-list div.type' );
		$i->see( 'Company Employees', '.field-list div.widest' );
	}

	public function i_must_select_a_model_to_create_a_relation_field( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Please choose a related model' );
	}

	public function i_can_not_edit_reference_or_cardinality( AcceptanceTester $i ) {
		$this->i_can_create_a_relation_field( $i );
		// Offsets from the center are used here to prevent “other element would
		// receive the click” due to the “add field” button overlapping the edit
		// button in the center.
		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->wait( 1 );

		$reference_disabled_state   = $i->grabAttributeFrom( '#reference', 'disabled' );
		$cardinality_disabled_state = $i->grabAttributeFrom( '#many-to-many', 'disabled' );

		$i->assertEquals( 'true', $reference_disabled_state );
		$i->assertEquals( 'true', $cardinality_disabled_state );
	}

	public function i_can_update_an_existing_relationship_field( AcceptanceTester $i ) {
		$this->i_can_create_a_relation_field( $i );
		// Offsets from the center are used here to prevent “other element would
		// receive the click” due to the “add field” button overlapping the edit
		// button in the center.
		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'name' ], 'Updated Name' );

		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Updated Name', '.field-list div.widest' );
	}

	public function i_can_set_a_relationship_field_description_shorter_than_the_character_limit( AcceptanceTester $i ) {
		$i->selectOption( '#reference', 'Employees' );
		$i->click( '#many-to-many' );

		// The field cannot be submitted with a description exceeding the maximum length.
		$i->fillField( [ 'name' => 'description' ], str_repeat( 'a', 251 ) );
		$i->see( '251/250', '.description-count' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Exceeds max length' );

		// The description saves when corrected.
		$i->fillField( [ 'name' => 'description' ], 'This text is under the character limit.' );
		$i->see( '39/250', '.description-count' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Relationship', '.field-list div.type' );
		$i->see( 'Company Employees', '.field-list div.widest' );

		// The description and count are correct when reopening the field.
		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->seeInField( 'description', 'This text is under the character limit.' );
		$i->see( '39/250', '.description-count' );
		$i->wait( 1 );
	}

	public function i_can_see_reverse_relationship_fields_when_clicking_the_appropriate_checkbox( AcceptanceTester $i ) {
		$i->dontSee( 'Reverse Display Name' );

		$i->click( '#enable-reverse' );
		$i->wait( 1 );

		$i->see( 'Reverse Display Name' );

		$i->seeInField( 'reverseName', 'Companies' );
		$i->seeInField( 'reverseSlug', 'companies' );

		// The field cannot be submitted with a name exceeding the maximum length.
		$i->fillField( [ 'name' => 'reverseName' ], str_repeat( 'a', 51 ) );
		$i->see( '51/50', '.reverse-name-count' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Exceeds max length' );
	}

	public function i_can_see_warnings_when_a_reverse_relationship_slug_conflicts( AcceptanceTester $i ) {
		// Attempt to create a relationship field with a reverse slug that
		// conflicts with a field slug in the reference model.
		$i->selectOption( '#reference', 'Employees' );
		$i->click( '#enable-reverse' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'reverseName' ], 'conflictsWithEmployeesSlug' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'A field in the employee model has the same identifier' );

		// Resolve the reverse slug conflict and save the field.
		$i->fillField( [ 'name' => 'reverseName' ], 'resolveConflict' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Company Employees', '.field-list div.widest' ); // Field was created successfully.

		// Attempt to create a second relationship field with the same reverse
		// relationship slug as the relationship field we just saved.
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Relation', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Second Relationship' );
		$i->selectOption( '#reference', 'Employees' );
		$i->click( '#enable-reverse' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'reverseName' ], 'resolveConflict' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'A relationship field in this model has the same identifier.' );

		// Resolve the reverse slug conflict.
		$i->fillField( [ 'name' => 'reverseName' ], 'fixedConflict' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Second Relationship', Locator::lastElement( '.field-list div.widest' ) ); // Second field was created successfully.

		// Open and update one of the relationship fields.
		// To prevent reoccurence of an issue where reverse slugs could conflict
		// with themselves and stop a relationship field from updating.
		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Updated Name' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->see( 'Updated Name', '.field-list div.widest' );
	}
}
