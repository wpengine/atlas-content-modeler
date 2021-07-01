<?php

class CreateTaxonomyCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->haveContentModel('goose', 'geese');
		$I->wait(1);
	}

	public function i_can_navigate_to_the_taxonomies_page(AcceptanceTester $I)
	{
		$I->amOnWPEngineContentModelPage();
		$I->wait(1);
		$I->see('View Taxonomies', 'button.taxonomies');
		$I->click('button.taxonomies');
		$I->wait(1);
		$I->see('Taxonomies', 'section.heading h2');
	}

	public function i_can_see_the_no_taxonomies_message_if_none_exist(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->see('You currently have no taxonomies', '.taxonomy-list' );
	}

	public function i_can_create_a_taxonomy(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->fillField(['name' => 'singular'], 'Breed');
		$I->fillField(['name' => 'plural'], 'Breeds');
		$I->click('.checklist .checkbox'); // The “goose” model.
		$I->click('.card-content button.primary');
		$I->wait(1);
		$I->see('taxonomy was created', '#success');
		$I->see('Breeds', '.taxonomy-list');
		$I->see('goose', '.taxonomy-list');

		// Form fields should reset when a submission was successful.
		$I->seeInField("#singular", "");
		$I->seeInField("#plural", "");
		$I->seeInField("#slug", "");

		// Character counts should reset.
		$I->see('0/50', '.field .count');
	}

	public function i_can_not_create_a_taxonomy_without_ticking_a_model(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->fillField(['name' => 'singular'], 'Breed');
		$I->fillField(['name' => 'plural'], 'Breeds');
		$I->click('.card-content button.primary');
		$I->wait(1);
		$I->see('Please choose at least one model');
	}

	public function i_can_not_create_a_taxonomy_without_filling_the_singular_name(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->fillField(['name' => 'plural'], 'Breeds');
		$I->click('.checklist .checkbox'); // The “goose” model.
		$I->click('.card-content button.primary');
		$I->wait(1);
		$I->see('This field is required');
	}

	public function i_can_not_create_a_taxonomy_without_filling_the_plural_name(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->fillField(['name' => 'singular'], 'Breed');
		$I->click('.checklist .checkbox'); // The “goose” model.
		$I->click('.card-content button.primary');
		$I->wait(1);
		$I->see('This field is required');
	}

	public function i_can_not_create_a_taxonomy_if_the_slug_already_exists(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->fillField(['name' => 'singular'], 'Breed');
		$I->fillField(['name' => 'plural'], 'Breeds');
		$I->click('.checklist .checkbox'); // The “goose” model.
		$I->click('.card-content button.primary');
		$I->wait(1);

		// Create another taxonomy with the same info.
		$I->fillField(['name' => 'singular'], 'Breed');
		$I->fillField(['name' => 'plural'], 'Breeds');
		$I->click('.checklist .checkbox');
		$I->click('.card-content button.primary');
		$I->wait(1);

		$I->see('A taxonomy with this API Identifier already exists');
	}

	public function i_can_see_a_generated_slug_when_creating_a_second_taxonomy_after_editing_the_slug_in_the_first(AcceptanceTester $I)
	{
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->fillField(['name' => 'singular'], 'First');
		$I->fillField(['name' => 'plural'], 'Firsts');
		// Edit the slug manually to break the “link” with the singular field.
		$I->fillField(['name' => 'slug'], 'myFirst');
		$I->click('.checklist .checkbox'); // The “goose” model.
		$I->click('.card-content button.primary');
		$I->wait(1);
		$I->see('taxonomy was created', '#success');
		$I->see('Firsts', '.taxonomy-list');

		// A successful submission should relink the Singular and API ID fields
		// so they are linked for the next entry. Confirm the fields were
		// relinked: filling "singular" should auto-generate a slug.
		$I->fillField(['name' => 'singular'], 'Second');
		$I->seeInField('#slug', 'second');
	}
}
