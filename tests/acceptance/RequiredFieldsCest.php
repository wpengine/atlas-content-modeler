<?php

class RequiredFieldsCest {

	public function i_can_set_required_fields_and_see_submission_errors( AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'goose', 'geese' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->seeInField( '#slug', 'name' );
		$i->click( '.open-field label.checkbox.is-required' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Create an entry for the new model.
		$i->amOnPage( '/wp-admin/edit.php?post_type=goose' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		// Do not fill the 'name' field here.
		// We want to check we're prompted to fill the required field.

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		$i->see( 'field is required' );

		// Fill the field as prompted.
		$i->fillField( [ 'name' => 'atlas-content-modeler[goose][name]' ], 'Goosey goose' );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		$i->see( 'Post published.' );
		$i->seeInField( 'atlas-content-modeler[goose][name]', 'Goosey goose' );
	}
}
