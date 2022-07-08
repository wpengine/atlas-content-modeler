<?php

class UpdateExistingModelEntryCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'goose', 'geese', [ 'description' => 'Geese go honk' ] );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Color' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.add-item' );
		$i->click( 'Rich Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Description' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.add-item' );
		$i->click( 'Rich Text', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Another rich text field' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.add-item' );
		$i->click( 'Number', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Age' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.add-item' );
		$i->click( 'Date', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Date of Birth' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.add-item' );
		$i->click( 'Boolean', '.field-buttons' );
		$i->fillField( [ 'name' => 'name' ], 'Flies south for winter?' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Next we create an entry for our new model.
		$i->amOnPage( '/wp-admin/edit.php?post_type=goose' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][color]' ], 'Gray' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][age]' ], '100' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][dateOfBirth]' ], '01/01/2021' );
		$i->checkOption( 'atlas-content-modeler[goose][fliesSouthForWinter]' );

		// Rich text fields rendered as TinyMCE live in an iframe.
		$i->switchToIFrame( '#field-description iframe' );
		$i->fillField( '#tinymce', 'I am a goose' );
		$i->switchToIFrame(); // Switch back to main window.

		// Fill the second TinyMCE field.
		$i->switchToIFrame( '#field-anotherRichTextField iframe' );
		$i->fillField( '#tinymce', 'I am another rich text field' );
		$i->switchToIFrame(); // Switch back to main window.

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );
	}

	public function i_can_update_an_existing_model_entry( AcceptanceTester $i ) {
		// Update the entry.
		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][color]' ], 'Green' );
		$i->switchToIFrame( '#field-description iframe' );
		$i->fillField( '#tinymce', 'I am a green goose' );
		$i->switchToIFrame(); // Switch back to main window.

		$i->click( 'Update', '#publishing-action' );
		$i->wait( 2 );

		$i->seeInField( 'atlas-content-modeler[goose][color]', 'Green' );
		$i->switchToIFrame( '#field-description iframe' );
		$i->see( 'I am a green goose' );
		$i->switchToIFrame();

		// Cause an update failure and check error message.
		$i->executeJS(
			"
            var field = document.getElementsByName('atlas-content-modeler-pubex-nonce');
            field[0].setAttribute('type', 'text');
        "
		);
		$i->fillField( [ 'name' => 'atlas-content-modeler-pubex-nonce' ], 'broken nonce' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][color]' ], 'Green' );
		$i->click( 'Update', '#publishing-action' );
		$i->wait( 2 );
		$i->see( 'Nonce verification failed when saving your content. Please try again.' );
	}

	public function i_can_clear_fields_in_an_existing_model_entry( AcceptanceTester $i ) {
			// Clear some fields.
			$i->fillField( [ 'name' => 'atlas-content-modeler[goose][color]' ], '' );
			$i->fillField( [ 'name' => 'atlas-content-modeler[goose][age]' ], '' );
			$i->fillField( [ 'name' => 'atlas-content-modeler[goose][dateOfBirth]' ], '' );

			$i->click( 'Update', '#publishing-action' );
			$i->wait( 2 );

			// Confirm fields are still cleared after updating.
			$i->seeInField( [ 'name' => 'atlas-content-modeler[goose][color]' ], '' );
			$i->seeInField( [ 'name' => 'atlas-content-modeler[goose][age]' ], '' );
			$i->seeInField( [ 'name' => 'atlas-content-modeler[goose][dateOfBirth]' ], '' );
	}
}
