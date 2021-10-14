<?php
use Codeception\Util\Locator;

class TrashEntryCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();

		$i->haveContentModel( 'goose', 'geese' );
		$i->haveContentModel( 'mouse', 'mice' );
		$i->wait( 1 );

		// Create a relationship field in mouse linking to geese.
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Goose Friend' );
		$i->selectOption( '#reference', 'geese' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );
	}

	public function i_see_a_warning_when_trashing_an_entry_linked_via_a_relationship_field( \AcceptanceTester $i ) {
		// Create a geese entry.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=goose' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		// Create a mouse and link it to the goose.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=mouse' );
		$i->click( '#atlas-content-modeler[mouse][gooseFriend]' );
		$i->wait( 2 );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button.action-button' );
		$i->wait( 2 );
		$i->click( 'Publish', '#publishing-action' );

		// Visit the goose we created.
		$i->amOnPage( '/wp-admin/edit.php?post_type=goose' );
		$i->click( '#the-list a.row-title' );
		$i->wait( 1 );

		// Attempt to trash the goose.
		$i->click( 'Move to Trash', '#delete-action' );
		$i->wait( 2 );

		/**
		 * Should see “Trash this entry?” modal prompt because trashing goose
		 * will sever the connection to mouse when the trash is emptied.
		 */
		$i->see( 'Trash this' );

		// Clicking Cancel in the modal should not trash the entry.
		$i->click( 'Cancel' ); // Modal button.
		$i->wait( 2 );
		$i->see( 'Edit goose' );

		// Clicking Move to Trash should trash the entry.
		$i->click( 'Move to Trash', '#delete-action' );
		$i->wait( 2 );
		$i->click( 'Move to Trash' ); // Modal button.
		$i->wait( 2 );
		$i->see( 'moved to the Trash' );
	}

	public function i_see_no_warnings_when_trashing_an_entry_not_linked_via_a_relationship_field( \AcceptanceTester $i ) {
		// Create a mouse without a link.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=mouse' );
		$i->wait( 2 );
		$i->click( 'Publish', '#publishing-action' );

		// Visit the mouse we created.
		$i->amOnPage( '/wp-admin/edit.php?post_type=mouse' );
		$i->click( '#the-list a.row-title' );
		$i->wait( 1 );

		// Attempt to trash the mouse.
		$i->click( 'Move to Trash', '#delete-action' );
		$i->wait( 2 );

		// Mouse should be trashed without a modal warning because it is not used as a reference anywhere.
		$i->see( 'moved to the Trash' );
	}
}
