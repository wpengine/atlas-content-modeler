<?php

class DeleteFieldFromModelCest
{

    /**
     * Ensure a user can delete a field from a model.
     */
    public function i_can_delete_a_field_from_a_model(AcceptanceTester $I): void
    {
        $I->loginAsAdmin();
        $I->haveContentModel('Candy', 'Candies');
        $I->wait(1);

        $I->click('Text', '.field-buttons');
        $I->wait(1);
        $I->fillField(['name' => 'name'], 'Name');
        $I->click('.open-field button.primary');
        $I->wait(1);

        $I->click('.field-list li .options');
        $I->see('Delete', '.dropdown-content');
        $I->click('Delete', '.field-list li .dropdown-content');

        $I->see("Are you sure you want to delete");
        $I->click('Delete', '.atlas-content-modeler-delete-field-modal-container');
        $I->see("Choose your first field for the content model");
    }
}
