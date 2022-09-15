<?php

class ConflictingModelCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();

		/**
		 * Creates a model with a conflicting model ID ('type') by directly
		 * inserting it into the `atlas_content_modeler_post_types` WP option,
		 * bypassing our checks that prevent models with bad model IDs.
		 */
		$i->haveContentModel( 'Type', 'Types' );

		// Creates another model with an ID that WordPress is not likely to ever reserve.
		$i->haveContentModel( 'BadgerShoe', 'BadgerShoes' );

		$i->amOnWPEngineContentModelPage();
		$i->waitForElement( '.model-list' );
	}

	public function i_see_a_conflict_message_if_a_model_with_a_reserved_id_exists( AcceptanceTester $i ) {
		$i->see( 'Some models are disabled due to ID conflicts' );
		$i->see( 'Disabled due to a model ID conflict' );
	}

	public function i_see_no_conflict_message_after_deleting_the_conflicting_model( AcceptanceTester $i ) {
		$i->click( '.model-list .has-conflict button.options' );
		$i->see( 'Delete', '.dropdown-content' );
		$i->click( 'Delete', '.model-list .has-conflict .dropdown-content' );

		$i->see( 'Are you sure you want to delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );

		$i->dontSee( 'Some models are disabled due to ID conflicts' );
		$i->dontSee( 'Disabled due to a model ID conflict' );
	}

}
