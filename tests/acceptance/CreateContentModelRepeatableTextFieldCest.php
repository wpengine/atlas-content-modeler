<?php

class CreateContentModelRepeatableTextFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1280, 1024 );
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->click( 'Text', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Colors' );
		$i->click( '.is-repeatable' );
	}

	public function i_can_see_min_max_in_settings_if_repeatable_is_enabled( \AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->see( 'Minimum Repeatable Limit' );
		$i->see( 'Maximum Repeatable Limit' );
	}

	public function i_cannot_see_min_max_in_settings_if_repeatable_is_enabled( \AcceptanceTester $i ) {
		$i->click( '.is-repeatable' );
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->dontSee( 'Minimum Repeatable Limit' );
		$i->dontSee( 'Maximum Repeatable Limit' );
	}

	public function i_can_cancel_advanced_settings_edits_without_losing_existing_dirty_changes( \AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '2' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], '4' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );

		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '1' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], '3' );
		$i->click( 'button[data-testid="model-advanced-settings-cancel-button"]' );
		$i->wait( 1 );

		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->seeInField( '#minRepeatable', '2' );
		$i->seeInField( '#maxRepeatable', '4' );
	}

	public function i_can_see_errors_in_min_and_max_repeatable_fields_for_negative_values( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '-1' );
		$i->see( 'The minimum value is' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], '-1' );
		$i->see( 'The minimum value is' );
	}

	public function non_integer_values_for_min_and_max_repeatable_fields_will_be_converted_to_empty_strings( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], 'a' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], 'a' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );

		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->seeInField( '#minRepeatable', '' );
		$i->seeInField( '#maxRepeatable', '' );

		$i->fillField( [ 'name' => 'minRepeatable' ], 'a' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], 'a' );
		$i->click( 'button[data-testid="model-advanced-settings-cancel-button"]' );
		$i->wait( 1 );

		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->seeInField( '#minRepeatable', '' );
		$i->seeInField( '#maxRepeatable', '' );
	}

	public function i_can_create_a_content_model_text_repeatable_field( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][colors][0]' ]
		);
	}

	public function i_can_create_a_content_model_text_repeatable_field_with_a_min_of_two( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '2' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][colors][0]' ]
		);
		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][colors][1]' ]
		);
		$i->dontSeeElement( '#field-colors button.remove-item' );
		$i->seeElement( 'button.add-option' );
	}

	public function a_non_required_repeatable_text_field_with_a_min_of_two_does_not_require_all_minimum_fields_to_be_present( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '2' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );
		$i->see( 'Post published' );
	}

	public function a_non_required_repeatable_text_field_with_a_minimum_will_trigger_validation_if_minimum_valid_fields_are_not_present( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '2' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'atlas-content-modeler[candy][colors][0]' ], 'red' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );
		$i->seeElement( 'input[name="atlas-content-modeler[candy][colors][1]"]:invalid' );
	}

	public function a_non_required_repeatable_text_field_with_a_minimum_will_not_trigger_validation_if_minimum_valid_fields_are_present( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '2' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'atlas-content-modeler[candy][colors][0]' ], 'red' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[candy][colors][1]' ], 'blue' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );
		$i->see( 'Post published' );
	}

	public function a_non_required_repeatable_text_field_with_the_same_minimum_and_maximum_will_trigger_validation_if_less_than_minimum_fields_are_present( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'minRepeatable' ], '2' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], '2' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'atlas-content-modeler[candy][colors][0]' ], 'red' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );
		$i->seeElement( 'input[name="atlas-content-modeler[candy][colors][1]"]:invalid' );
	}

	public function a_repeatable_text_field_with_a_maximum_of_three_will_only_allow_a_max_of_three_fields( AcceptanceTester $i ) {
		$i->click( 'button[data-testid="edit-model-update-create-settings-button"]' );
		$i->fillField( [ 'name' => 'maxRepeatable' ], '3' );
		$i->click( 'button[data-testid="model-advanced-settings-done-button"]' );
		$i->wait( 1 );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][colors][0]' ]
		);

		$i->click( 'Add Item', 'button.add-option' );
		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][colors][1]' ]
		);

		$i->click( 'Add Item', 'button.add-option' );
		$i->seeElement(
			'input',
			[ 'name' => 'atlas-content-modeler[candy][colors][2]' ]
		);

		$i->dontSeeElement( 'button.add-option' );
	}
}
