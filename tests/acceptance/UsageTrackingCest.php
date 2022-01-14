<?php
class UsageTrackingCest {
	public function _before( AcceptanceTester $i ) {
		$i->loginAsAdmin();
	}

	public function i_can_see_the_usage_tracking_setting( AcceptanceTester $i ) {
		$i->amOnPluginSettingsPage();
		$i->see( 'Opt into anonymous usage tracking to help us make Atlas Content Modeler better' );
	}

	public function i_can_toggle_the_setting_and_see_the_value_updated_in_the_database( AcceptanceTester $i ) {
		$i->amOnPluginSettingsPage();

		// Enable the setting.
		$i->click( '#atlas-content-modeler-settings[usageTrackingEnabled]' );
		$i->wait( 1 ); // wait for API call that saves setting to db.
		$i->seeOptionInDatabase( 'atlas_content_modeler_usage_tracking', 1 );

		// Disable the setting.
		$i->click( '#atlas-content-modeler-settings[usageTrackingDisabled]' );
		$i->wait( 1 ); // wait for API call that saves setting to db.
		$i->seeOptionInDatabase( 'atlas_content_modeler_usage_tracking', 0 );
	}
}
