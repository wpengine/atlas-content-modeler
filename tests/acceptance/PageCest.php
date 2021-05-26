<?php

class PageCest
{
    /**
     * Ensure the WPEngine Content Modeling page is available.
     */
    public function i_can_access_the_content_model_page(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->amOnPage('/wp-admin/admin.php?page=atlas-content-modeler');
        $I->see('Content Model Creator by WP Engine');
    }
}
