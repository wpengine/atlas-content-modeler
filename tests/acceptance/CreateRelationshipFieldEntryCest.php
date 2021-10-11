<?php
use Codeception\Util\Locator;

class CreateRelationshipFieldEntryCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$i->haveContentModel( 'Company', 'Companies' );
		$i->wait( 1 );
		$i->click( 'Text', '.field-buttons' );
		$i->checkOption( 'input[name="isTitle"]' );
		$i->fillField( [ 'name' => 'name' ], 'Company' );
		$i->click( '.open-field button.primary' );

		$i->haveContentModel( 'Employee', 'Employees' );
		$i->wait( 1 );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Company' );
		$i->selectOption( '#reference', 'Companies' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Many Companies' );
		$i->selectOption( '#reference', 'Companies' );
		$i->click( 'input#many-to-many' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );
	}

	public function i_can_create_an_employee_and_see_many_to_one_relation_field( AcceptanceTester $i ) {
		$i->see( 'Relationship', '.field-list div.type' );
		$i->see( 'Company', '.field-list div.widest' );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=company' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[company][company]' ], 'WP Engine' );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		$i->see( 'Post published.' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );
		$i->see( 'Company', 'div.field.relationship' );
		$i->click( '#atlas-content-modeler[employee][company]' );
		$i->see( 'Select Company', 'div.ReactModal__Content.ReactModal__Content--after-open h2' );
		$i->wait( 3 );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button.action-button' );
		$i->wait( 3 );

		$i->see( 'WP Engine', 'div.relation-model-card' );
	}

	public function i_can_create_an_employee_and_see_many_to_many_relation_field( AcceptanceTester $i ) {
		$i->see( 'Relationship', '.field-list div.type' );
		$i->see( 'Company', '.field-list div.widest' );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=company' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[company][company]' ], 'WP Engine' );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=company' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[company][company]' ], 'Another Company Name' );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		$i->see( 'Post published.' );
		$i->wait( 1 );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );
		$i->see( 'Many Companies', 'div.field.relationship' );
		$i->click( '#atlas-content-modeler[employee][manyCompanies]' );
		$i->see( 'Select Companies', 'div.ReactModal__Content.ReactModal__Content--after-open h2' );
		$i->wait( 3 );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( Locator::elementAt( 'td.checkbox input', 2 ) );
		$i->click( 'button.action-button' );
		$i->wait( 3 );

		$i->see( 'WP Engine', 'div.relation-model-card' );
		$i->see( 'Another Company Name', 'div.relation-model-card' );
	}

	public function i_can_create_a_new_entry_from_an_empty_relationships_modal_table( AcceptanceTester $i ) {
		// Try to create an employee before any companies have been entered.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );
		$i->see( 'Company', 'div.field.relationship' );
		$i->click( '#atlas-content-modeler[employee][company]' );
		$i->see( 'Select Company', '.atlas-content-modeler-relationship-modal-container h2' );
		$i->wait( 2 );
		$i->see( 'No published entries' );

		// Create a company via the link in the relationships modal.
		$i->click( 'Create a new Company' );
		$i->wait( 1 );
		$i->switchToNextTab(); // Focus on the Create Company tab.
		$i->fillField( [ 'name' => 'atlas-content-modeler[company][company]' ], 'WP Engine' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		// Check the new company appears in the updated modal.
		$i->closeTab(); // Focus on the original employee tab.

		// Test that the modal is still visible.
		$i->see( 'Select Company', '.atlas-content-modeler-relationship-modal-container h2' );

		// This is as far as we can test in headless mode under CircleCI.
		// The visibilitychange event does not fire in headless mode,
		// so we can't check that the modal refreshed to show the
		// newly-added “WP Engine” company.
	}
}
