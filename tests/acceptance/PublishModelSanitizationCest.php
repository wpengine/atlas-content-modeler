<?php
use Codeception\Util\Locator;
class PublishModelSanitizationCest
{
	public function i_can_publish_a_model_and_fields_are_sanitized(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		// Create a model.
		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

		// Add a text field.
		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Color');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Create a new goose.
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		// Fill the color field, including HTML tags that should be stripped.
		$i->fillField(['name' => 'atlas-content-modeler[goose][color]'], '<em>Gray</em>');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// HTML tags should be stripped in text fields if sanitization is working, with tag content preserved.
		$i->dontSeeInField('atlas-content-modeler[goose][color]', '<em>Gray</em>');
		$i->seeInField('atlas-content-modeler[goose][color]', 'Gray');
	}
}
