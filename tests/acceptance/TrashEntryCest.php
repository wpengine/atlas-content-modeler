<?php
use Codeception\Util\Locator;

class TrashEntryCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();

		$I->haveContentModel('goose', 'geese');
		$I->haveContentModel('mouse', 'mice');
		$I->wait(1);

		// Create a relationship field in mouse linking to geese.
		$I->click('Relationship', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Goose Friend');
		$I->selectOption('#reference', 'geese');
		$I->click('.open-field button.primary');
		$I->wait(1);

		// Create a geese entry.
		$I->amOnPage('/wp-admin/post-new.php?post_type=goose');
		$I->click('Publish', '#publishing-action');
		$I->wait(2);

		// Create a mouse and link it to the goose.
		$I->amOnPage('/wp-admin/post-new.php?post_type=mouse');
		$I->click('#atlas-content-modeler[mouse][gooseFriend]');
		$I->wait(2);
		$I->click(Locator::elementAt('td.checkbox input', 1));
		$I->click('button.action-button');
		$I->wait(2);
		$I->click('Publish', '#publishing-action');
	}

	public function i_see_a_warning_when_trashing_an_entry_linked_via_a_relationship_field(\AcceptanceTester $I)
	{
		// Visit the goose we created.
		$I->amOnPage('/wp-admin/edit.php?post_type=goose');
		$I->click('#the-list a.row-title');
		$I->wait(1);

		// Attempt to trash the goose.
		$I->click('Move to Trash', '#delete-action');
		$I->wait(2);

		/**
		 * Should see “Trash this entry?” modal prompt because trashing goose
		 * will sever the connection to mouse when the trash is emptied.
		 */
		$I->see('Trash this');

		// Clicking Cancel in the modal should not trash the entry.
		$I->click('Cancel'); // Modal button.
		$I->wait(2);
		$I->see('Edit goose');

		// Clicking Move to Trash should trash the entry.
		$I->click('Move to Trash', '#delete-action');
		$I->wait(2);
		$I->click('Move to Trash'); // Modal button.
		$I->wait(2);
		$I->see('moved to the Trash');
	}

	public function i_see_no_warnings_when_trashing_an_entry_not_linked_via_a_relationship_field(\AcceptanceTester $I)
	{
		// Visit the mouse we created.
		$I->amOnPage('/wp-admin/edit.php?post_type=mouse');
		$I->click('#the-list a.row-title');
		$I->wait(1);

		// Attempt to trash the mouse.
		$I->click('Move to Trash', '#delete-action');
		$I->wait(2);

		// Mouse should be trashed without a modal warning because it is not used as a reference anywhere.
		$I->see('moved to the Trash');
	}
}
