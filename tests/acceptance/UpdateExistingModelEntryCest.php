<?php

class UpdateExistingModelEntryCest
{
    public function i_can_update_an_existing_model_entry(AcceptanceTester $i)
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

        $i->click('.add-item');
        $i->click('Rich Text', '.field-buttons');
        $i->fillField(['name' => 'name'], 'Description');
        $i->click('.open-field button.primary');
        $i->wait(1);

        $i->click('.add-item');
        $i->click('Rich Text', '.field-buttons');
        $i->fillField(['name' => 'name'], 'Another rich text field');
        $i->click('.open-field button.primary');
        $i->wait(1);

        $i->click('.add-item');
        $i->click('Number', '.field-buttons');
        $i->fillField(['name' => 'name'], 'Age');
        $i->click('.open-field button.primary');
        $i->wait(1);

        $i->click('.add-item');
        $i->click('Date', '.field-buttons');
        $i->fillField(['name' => 'name'], 'Date of Birth');
        $i->click('.open-field button.primary');
        $i->wait(1);

        $i->click('.add-item');
        $i->click('Boolean', '.field-buttons');
        $i->fillField(['name' => 'name'], 'Flies south for winter?');
        $i->click('.open-field button.primary');
        $i->wait(1);

        // Next we create an entry for our new model.
        $i->amOnPage('/wp-admin/edit.php?post_type=goose');
        $i->click('Add New', '.wrap');
        $i->wait(1);

        $i->fillField(['name' => 'atlas-content-modeler[goose][color]'], 'Gray');
        $i->fillField(['name' => 'atlas-content-modeler[goose][age]'], '100');
        $i->fillField(['name' => 'atlas-content-modeler[goose][dateOfBirth]'], '01/01/2021');
        $i->checkOption('atlas-content-modeler[goose][fliesSouthForWinter]');

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

        // Update the entry.
        $i->fillField(['name' => 'atlas-content-modeler[goose][color]'], 'Green');
        $i->switchToIFrame('#field-description iframe');
        $i->fillField('#tinymce', 'I am a green goose');
        $i->switchToIFrame(); // switch back to main window

        $i->click('Update', '#publishing-action');
        $i->wait(2);

        $i->seeInField('atlas-content-modeler[goose][color]', 'Green');
        $i->switchToIFrame('#field-description iframe');
        $i->see('I am a green goose');
        $i->switchToIFrame();

        // Cause an update failure and check error message.
        $i->executeJS("
            var field = document.getElementsByName('atlas-content-modeler-pubex-nonce');
            field[0].setAttribute('type', 'text');
        ");
        $i->fillField(['name' => 'atlas-content-modeler-pubex-nonce'], 'broken nonce');
        $i->fillField(['name' => 'atlas-content-modeler[goose][color]'], 'Green');
        $i->click('Update', '#publishing-action');
        $i->wait(2);
        $i->see('Nonce verification failed when saving your content. Please try again.');
    }
}
