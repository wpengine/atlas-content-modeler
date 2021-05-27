<?php

class PageCest
{
    /**
     * Ensure the WPEngine Content Modeling page is available.
     */
    public function i_can_access_the_content_model_page(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->amOnPage('/wp-admin/admin.php?page=wpe-content-model');
        $I->see('Atlas Content Modeler by WP Engine');
    }
}
