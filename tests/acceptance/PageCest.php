<?php

class PageCest {

	/**
	 * Ensure the WPEngine Content Modeling page is available.
	 */
	public function i_can_access_the_content_model_page( AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$i->amOnPage( '/wp-admin/admin.php?page=atlas-content-modeler' );
		$i->see( 'Atlas Content Modeler by WP Engine' );
	}
}
