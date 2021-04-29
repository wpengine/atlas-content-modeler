<?php
use Codeception\Util\Locator;
class PublishModelCest
{
	public function i_can_publish_a_model_entry(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		// First we create a model with fields.
		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

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
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'wpe-content-model[geese][color]'], 'Gray');
		$i->fillField(['name' => 'wpe-content-model[geese][age]'], '100');
		$i->fillField(['name' => 'wpe-content-model[geese][dateOfBirth]'], '01/01/2021');
		$i->checkOption('wpe-content-model[geese][fliesSouthForWinter]');

		// Rich text fields rendered as TinyMCE live in an iframe.
		$i->switchToIFrame('#field-description iframe');
		$i->fillField('#tinymce', 'I am a goose');
		$i->switchToIFrame(); // switch back to main window

		// Fill the second TinyMCE field.
		$i->switchToIFrame('#field-anotherRichTextField iframe');
		$i->fillField('#tinymce', 'I am another rich text field');
		$i->switchToIFrame(); // switch back to main window


		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		$i->seeInField('wpe-content-model[geese][color]', 'Gray');
		$i->seeInField('wpe-content-model[geese][age]', '100');
		$i->seeInField('wpe-content-model[geese][dateOfBirth]', '2021-01-01');
		$i->seeCheckboxIsChecked('wpe-content-model[geese][fliesSouthForWinter]');
		$i->switchToIFrame('#field-description iframe');
		$i->see('I am a goose'); // Sees the text in the TinyMCE iframe body.
		$i->switchToIFrame();

		// Show <textarea> elements hidden by TinyMCE so we can see them to check their values directly.
		$i->executeJS("
			var field = document.getElementsByName('wpe-content-model[geese][description]');
			field[0].removeAttribute('style');
			var fieldTwo = document.getElementsByName('wpe-content-model[geese][anotherRichTextField]');
			fieldTwo[0].removeAttribute('style');
		");

		$i->seeInField('wpe-content-model[geese][description]', '<p>I am a goose</p>');
		$i->seeInField('wpe-content-model[geese][anotherRichTextField]', '<p>I am another rich text field</p>');
	}
}
