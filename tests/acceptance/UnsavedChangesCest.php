<?php
use Codeception\Util\Locator;
class UnsavedChangesCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1024, 1024 );
		$i->maximizeWindow();

		$i->loginAsAdmin();
		$i->haveContentModel( 'goose', 'geese' );
		$i->wait( 1 );
	}

	public function i_see_an_unsaved_changes_prompt_when_opening_another_field( \AcceptanceTester $i ) {
		// Create the first field.
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->click( 'button.primary' );
		$i->wait( 1 );

		// Start to create a second field.
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Hobbies' );

		// Click the first field's edit button before completing the second field.
		// Offsets are used here to prevent “other element would receive the click”
		// due to the “add field” button overlapping the edit button in the center.
		$i->clickWithLeftButton( 'button.edit', 10, 10 );

		// Confirm that the Unsaved Changes modal appears.
		$i->see( 'Unsaved Changes' );

		// Click “Continue Editing” and confirm our changes were preserved.
		$i->click( '.ReactModal__Content button.primary' );
		$i->seeInField( '#name', 'Hobbies' );

		// Now try adding a new field without saving changes to the current field.
		$i->click( '.add-item button' );
		$i->see( 'Unsaved Changes' );

		// Click “Discard Changes” and confirm our incomplete Hobbies field is gone.
		$i->click( '.ReactModal__Content button.tertiary' );
		$i->dontSee( 'Hobbies' );

		// Start to edit the first field again.
		$i->clickWithLeftButton( 'button.edit', 10, 10 );

		// Make a change to a field.
		$i->fillField( [ 'name' => 'name' ], 'Name Edited' );

		// Attempt to navigate away via the breadcrumb.
		$i->click( 'Content Models', '.heading' );

		// Check the modal appears, but discard changes.
		$i->see( 'Unsaved Changes' );
		$i->click( '.ReactModal__Content button.tertiary' );
		$i->dontSee( 'Name Edited' );
		$i->see( 'Name' );

		// Start to edit the first field again.
		$i->clickWithLeftButton( 'button.edit', 10, 10 );

		// Attempt to navigate away via the breadcrumb.
		$i->click( 'Content Models', '.heading' );

		// Confirm the modal does *not* appear, because there are no unsaved changes.
		$i->dontSee( 'Unsaved Changes' );
		$i->see( 'New Model' ); // Model index page.
	}
}
