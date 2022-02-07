<?php

class AdvancedSettingsCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'goose', 'geese' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
	}

	public function i_can_set_min_max_character_counts_for_text_fields( \AcceptanceTester $i ) {
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minChars' ], '1' );
		$i->fillField( [ 'name' => 'maxChars' ], '10' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );

		// Open Advanced Settings again and check the options persisted.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->seeInField( 'minChars', '1' );
		$i->seeInField( 'maxChars', '10' );
	}

	public function i_can_see_errors_if_max_is_lower_than_min( \AcceptanceTester $i ) {
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'maxChars' ], '10' );
		$i->fillField( [ 'name' => 'minChars' ], '11' );
		$i->see( 'Max must be more than min' );
	}

	public function i_can_see_errors_if_min_is_negative( \AcceptanceTester $i ) {
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minChars' ], '-1' );
		$i->see( 'The minimum value is' );
	}

	public function i_can_see_errors_if_publisher_text_entry_does_not_exceed_min_chars( AcceptanceTester $i ) {
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minChars' ], '10' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' ); // Save Advanced Settings.
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' ); // Save the field.

		// Create an entry in the publisher app.
		$i->amOnPage( '/wp-admin/edit.php?post_type=goose' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		// Fill the field with a value that does not meet the 10 character minimum we set in minChars.
		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][name]' ], 'goose' );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		$i->see( 'Minimum length is 10' );
	}

	public function i_can_cancel_advanced_settings_edits_without_losing_other_field_changes( \AcceptanceTester $i ) {
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minChars' ], '1' );

		// Cancel the Advanced Settings changes.
		$i->click( 'button[data-testid="model-advanced-settings-cancel-button"]' );
		$i->wait( 1 );

		// Check the name field still contains the original text.
		$i->seeInField( '#slug', 'name' );

		// Check the minChars field in Advanced Settings is now cleared.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->seeInField( '#minChars', '' );
	}

	public function i_can_cancel_advanced_settings_edits_without_losing_existing_dirty_changes( \AcceptanceTester $i ) {
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minChars' ], '111' );

		// Save the Advanced Settings change but do not save the field yet.
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );

		// Open Advanced Settings again and update the same field.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minChars' ], '999' );

		// This time, cancel the Advanced Settings changes.
		$i->click( 'button[data-testid="model-advanced-settings-cancel-button"]' );
		$i->wait( 1 );

		// Open Advanced Settings a final time.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );

		// Expect to see the original saved value (previous state when
		// “Done” was clicked), not an empty field (initial form state).
		$i->seeInField( '#minChars', '111' );
	}

	public function i_can_set_allowed_file_types_for_media_fields( \AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'name' ], 'Photo' );

		// Open and fill Advanced Settings.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'allowedTypes' ], 'jpg,jpeg,pdf' );

		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' ); // Save Advanced Settings.
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' ); // Save the field.
		$i->wait( 1 );

		// Offsets are used here to prevent “other element would receive the click”
		// due to the “add field” button overlapping the edit button in the center.
		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );

		// Open Advanced Settings again.
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );

		// Expect to see saved values.
		$i->seeInField( '#allowedTypes', 'jpg,jpeg,pdf' );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=goose' );
		$i->wait( 1 );
		$i->see( 'Accepts file types: JPG, JPEG, PDF' );
	}
}
