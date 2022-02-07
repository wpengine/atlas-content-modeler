<?php

class DeleteFieldFromModelCest {

	/**
	 * Ensure a user can delete a field from a model.
	 */
	public function i_can_delete_a_field_from_a_model( AcceptanceTester $i ): void {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Name' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->click( '.field-list li .options' );
		$i->see( 'Delete', '.dropdown-content' );
		$i->click( 'Delete', '.field-list li .dropdown-content' );

		$i->see( 'Are you sure you want to delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-field-modal-container' );
		$i->see( 'Choose your first field for the content model' );
	}

}
