<?php

/**
 * Users should see their changes reflected in the WordPress admin sidebar
 * without having to refresh the page.
 */
class SidebarUpdatesCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
	}

	/**
	 * Ensure the sidebar is updated when a model is added.
	 */
	public function the_sidebar_adds_new_post_types_upon_creation(AcceptanceTester $I)
	{
		$I->dontSeeElementInDOM('#menu-posts-goose');

		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);

		$I->seeElementInDOM('#menu-posts-goose');

		$I->haveContentModel('Moose', 'Moose');
		$I->wait(1);

		$I->seeElementInDOM('#menu-posts-goose');
		$I->seeElementInDOM('#menu-posts-moose');
	}

	/**
	 * Ensure the sidebar is updated when a model is deleted.
	 */
	public function the_sidebar_removes_post_types_upon_deletion(AcceptanceTester $I)
	{
		$I->haveContentModel('Moose', 'Moose');
		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);

		$I->seeElementInDOM('#menu-posts-goose');
		$I->seeElementInDOM('#menu-posts-moose');

		$I->amOnWPEngineContentModelPage();

		// Delete Moose.
		$I->click('.model-list button.options');
		$I->click('.dropdown-content a.delete');
		$I->click('Delete', '.atlas-content-modeler-delete-model-modal-container');
		$I->wait(1);

		$I->dontSeeElementInDOM('#menu-posts-moose');

		// Delete Goose.
		$I->click('.model-list button.options');
		$I->click('.dropdown-content a.delete');
		$I->click('Delete', '.atlas-content-modeler-delete-model-modal-container');
		$I->wait(1);

		$I->dontSeeElementInDOM('#menu-posts-goose');
	}

	/**
	 * Ensure the sidebar is updated when taxonomies are added.
	 */
	public function the_sidebar_adds_new_taxonomies_upon_creation(AcceptanceTester $I)
	{
		$I->haveContentModel('Moose', 'Moose');
		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);

		$I->haveTaxonomy('Breed', 'Breeds', ['goose']);
		$I->wait(1);

		// The new taxonomy is assigned to Goose.
		$I->seeElementInDOM('#menu-posts-goose a', ['href' => 'edit-tags.php?taxonomy=breed&post_type=goose']);
		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->see('Breeds', ['css' => '#menu-posts-goose a']);
		// The new taxonomy is not assigned to Moose.
		$I->dontSeeElementInDOM('#menu-posts-moose a', ['href' => 'edit-tags.php?taxonomy=breed&post_type=moose']);

		$I->haveTaxonomy('Region', 'Regions', ['goose', 'moose']);
		$I->wait(1);

		// The new taxonomy is added to Goose and existing taxonomies are still present.
		$I->seeElementInDOM('#menu-posts-goose a', ['href' => 'edit-tags.php?taxonomy=region&post_type=goose']);
		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->see('Breeds', ['css' => '#menu-posts-goose a']);
		$I->see('Region', ['css' => '#menu-posts-goose a']);

		// Only the new taxonomy is present on Moose.
		$I->seeElementInDOM('#menu-posts-moose a', ['href' => 'edit-tags.php?taxonomy=region&post_type=moose']);
		$I->moveMouseOver(['css' => '#menu-posts-moose']);
		$I->dontSee('Breeds', ['css' => '#menu-posts-moose a']);
		$I->see('Region', ['css' => '#menu-posts-moose a']);
	}

	/**
	 * Ensure the sidebar is updated when taxonomies are edited.
	 */
	public function the_sidebar_updates_taxonomies_upon_editing(AcceptanceTester $I)
	{
		$I->haveContentModel('Moose', 'Moose');
		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);

		$I->haveTaxonomy('Breed', 'Breeds', ['goose']);
		$I->wait(1);

		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->see('Breeds', ['css' => '#menu-posts-goose a']);
		$I->moveMouseOver(['css' => '#menu-posts-moose']);
		$I->dontSee('Breeds', ['css' => '#menu-posts-moose a']);

		// Make some edits
		$I->click('.action button.options');
		$I->click('Edit', '.action .dropdown-content');
		$I->wait(1);
		$I->fillField(['name' => 'plural'], "Br33ds");
		$I->click(".checklist .checkbox input[value=moose]");
		$I->click('.card-content button.primary');
		$I->wait(1);

		// The edited taxonomy should be updated on both models.
		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->see('Br33ds', ['css' => '#menu-posts-goose a']);
		$I->moveMouseOver(['css' => '#menu-posts-moose']);
		$I->see('Br33ds', ['css' => '#menu-posts-moose a']);

		// Make edits to test "type" removal.
		$I->click('.action button.options');
		$I->click('Edit', '.action .dropdown-content');
		$I->wait(1);
		$I->click(".checklist .checkbox input[value=goose]"); // Remove from goose
		$I->click('.card-content button.primary');
		$I->wait(1);

		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->dontSee('Br33ds', ['css' => '#menu-posts-goose a']);
		$I->moveMouseOver(['css' => '#menu-posts-moose']);
		$I->see('Br33ds', ['css' => '#menu-posts-moose a']);
	}

	/**
	 * Ensure the sidebar is updated when taxonomies are deleted.
	 */
	public function the_sidebar_removes_taxonomies_upon_deletion(AcceptanceTester $I)
	{
		$I->haveContentModel('Moose', 'Moose');
		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);

		$I->haveTaxonomy('Breed', 'Breeds', ['goose', 'moose']);
		$I->wait(1);

		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->see('Breeds', ['css' => '#menu-posts-goose a']);
		$I->moveMouseOver(['css' => '#menu-posts-moose']);
		$I->See('Breeds', ['css' => '#menu-posts-moose a']);

		// Delete the Breeds taxonomy.
		$I->click('.action button.options');
		$I->see('Delete', '.dropdown-content');
		$I->click('Delete', '.action .dropdown-content');
		$I->click('Delete', '.atlas-content-modeler-delete-field-modal-container');
		$I->wait(1);

		// The edited taxonomy should be removed from both models.
		$I->moveMouseOver(['css' => '#menu-posts-goose']);
		$I->dontSee('Breeds', ['css' => '#menu-posts-goose a']);
		$I->moveMouseOver(['css' => '#menu-posts-moose']);
		$I->dontSee('Breeds', ['css' => '#menu-posts-moose a']);
	}
}
