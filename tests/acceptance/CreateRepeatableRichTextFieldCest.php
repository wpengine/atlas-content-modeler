<?php

class CreateContentModelRepeatableRichTextFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1280, 1024 );
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->click( 'Rich Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Colors' );
		$i->click( '.is-repeatable' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );
	}

	public function i_can_see_a_rich_text_repeatable_field_on_the_publisher_page( AcceptanceTester $i ) {
		// Show <textarea> elements hidden by TinyMCE so we can check they were added to the page.
		$i->executeJS(
			"
			document.getElementsByName('atlas-content-modeler[candy][colors][0]')[0].removeAttribute('style');
			"
		);

		$i->seeElement(
			'textarea',
			[ 'name' => 'atlas-content-modeler[candy][colors][0]' ]
		);
	}

	public function i_can_save_multiple_rich_text_repeatable_field_rows_on_the_publisher_page( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="add-rich-text-row"]' );

		$i->wait( 1 );

		$first_iframe_id  = $i->grabAttributeFrom( '[name="atlas-content-modeler[candy][colors][0]"]', 'id' );
		$second_iframe_id = $i->grabAttributeFrom( '[name="atlas-content-modeler[candy][colors][1]"]', 'id' );

		$i->switchToIFrame( '#' . $first_iframe_id . '_ifr' );
		$i->fillField( '#tinymce', 'First.' );
		$i->switchToIFrame(); // Switch back to main window.

		$i->switchToIFrame( '#' . $second_iframe_id . '_ifr' );
		$i->fillField( '#tinymce', 'Second.' );
		$i->switchToIFrame(); // Switch back to main window.

		// Save changes.
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		$i->see( 'Post published.' );
		$i->wait( 1 );

		// Show <textarea> elements hidden by TinyMCE so we can check their contents.
		$i->executeJS(
			"
			document.querySelector('[name=\"atlas-content-modeler[candy][colors][0]\"]').removeAttribute('style');
			document.querySelector('[name=\"atlas-content-modeler[candy][colors][1]\"]').removeAttribute('style');
			"
		);

		$i->seeInField( 'atlas-content-modeler[candy][colors][0]', '<p>First.</p>' );
		$i->seeInField( 'atlas-content-modeler[candy][colors][1]', '<p>Second.</p>' );
	}


	public function i_can_remove_rich_text_repeatable_field_rows_on_the_publisher_page( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="add-rich-text-row"]' );
		$i->wait( 1 );
		$i->seeNumberOfElements( '[data-testid="rich-text-repeater-row"]', 2 );

		$i->click( 'button[data-testid="delete-rich-text-row"]' );
		$i->seeNumberOfElements( '[data-testid="rich-text-repeater-row"]', 1 );
	}

}
