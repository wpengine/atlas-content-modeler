<?php
use Codeception\Util\Locator;
class PublishModelCest
{
	public function _before(\AcceptanceTester $i)
	{
		$i->maximizeWindow();

		// First we create a model with fields.
		$i->loginAsAdmin();
		$i->haveContentModel('moose', 'mooses', 'Mooses go frmh');
		$i->wait(1);
	}

	public function error_is_triggered_for_required_value(\AcceptanceTester $i)
	{
		$i->click('Number', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Integer');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Number', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Decimal');
		$i->click('input#decimal');
		$i->checkOption('required');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Next we create an entry for our new model.
		$i->amOnPage('/wp-admin/edit.php?post_type=moose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'atlas-content-modeler[moose][integer]'], '');
		$i->fillField(['name' => 'atlas-content-modeler[moose][decimal]'], '');
		$i->scrollTo('#submitdiv');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('This field is required');
		$i->wait(1);

		$i->seeInField('atlas-content-modeler[moose][integer]', '');
		$i->seeInField('atlas-content-modeler[moose][decimal]', '');
	}

	public function error_is_triggered_for_invalid_value(\AcceptanceTester $i)
	{
		$i->click('Number', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Integer');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Number', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Decimal');
		$i->click('input#decimal');
		$i->checkOption('required');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Next we create an entry for our new model.
		$i->amOnPage('/wp-admin/edit.php?post_type=moose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'atlas-content-modeler[moose][integer]'], '');
		$i->fillField(['name' => 'atlas-content-modeler[moose][decimal]'], '.0');
		$i->scrollTo('#submitdiv');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('The input is invalid');
		$i->wait(1);

		$i->seeInField('atlas-content-modeler[moose][integer]', '');
		$i->seeInField('atlas-content-modeler[moose][decimal]', '.0');
	}

	public function i_can_publish_a_model_entry(AcceptanceTester $i)
	{
		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Color');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Rich Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Description');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Rich Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Another rich text field');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Number', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Age');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Date', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Date of Birth');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Boolean', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Flies south for winter?');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Next we create an entry for our new model.
		$i->amOnPage('/wp-admin/edit.php?post_type=moose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'atlas-content-modeler[moose][color]'], 'Gray');
		$i->fillField(['name' => 'atlas-content-modeler[moose][age]'], '100');
		$i->fillField(['name' => 'atlas-content-modeler[moose][dateOfBirth]'], '01/01/2021');
		$i->checkOption('atlas-content-modeler[moose][fliesSouthForWinter]');

		// Rich text fields rendered as TinyMCE live in an iframe.
		$i->switchToIFrame('#field-description iframe');
		$i->fillField('#tinymce', 'I am a moose');
		$i->switchToIFrame(); // switch back to main window

		// Fill the second TinyMCE field.
		$i->switchToIFrame('#field-anotherRichTextField iframe');
		$i->fillField('#tinymce', 'I am another rich text field');
		$i->switchToIFrame(); // switch back to main window
		$i->scrollTo('#submitdiv');


		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');
		$i->wait(1);
		$i->see('Edit moose'); // Page title should change from “Add moose” when published.

		$i->seeInField('atlas-content-modeler[moose][color]', 'Gray');
		$i->seeInField('atlas-content-modeler[moose][age]', '100');
		$i->seeInField('atlas-content-modeler[moose][dateOfBirth]', '2021-01-01');
		$i->seeCheckboxIsChecked('atlas-content-modeler[moose][fliesSouthForWinter]');
		$i->switchToIFrame('#field-description iframe');
		$i->see('I am a moose'); // Sees the text in the TinyMCE iframe body.
		$i->switchToIFrame();

		// Show <textarea> elements hidden by TinyMCE so we can see them to check their values directly.
		$i->executeJS("
			var field = document.getElementsByName('atlas-content-modeler[moose][description]');
			field[0].removeAttribute('style');
			var fieldTwo = document.getElementsByName('atlas-content-modeler[moose][anotherRichTextField]');
			fieldTwo[0].removeAttribute('style');
		");

		$i->seeInField('atlas-content-modeler[moose][description]', '<p>I am a moose</p>');
		$i->seeInField('atlas-content-modeler[moose][anotherRichTextField]', '<p>I am another rich text field</p>');
	}
}
