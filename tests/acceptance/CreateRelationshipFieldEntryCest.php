<?php

use Codeception\Util\Locator;

class CreateRelationshipFieldEntryCest {

	public function _before( AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
	}

	/**
	 * @before create_company_employee_models
	 */
	public function i_can_create_an_employee_and_see_many_to_one_relation_field( AcceptanceTester $i ) {
		$i->see( 'Relationship', '.field-list div.type' );
		$i->see( 'Company', '.field-list div.widest' );

		$this->create_company( $i, 'WP Engine' );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );
		$i->see( 'Company', 'div.field.relationship' );
		$i->click( '#atlas-content-modeler[employee][company]' );
		$i->see( 'Select Company', 'div.ReactModal__Content.ReactModal__Content--after-open h2' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );

		$i->waitForElementVisible( 'div.relation-model-card' );
		$i->see( 'WP Engine', 'div.relation-model-card' );
	}

	/**
	 * @before create_company_employee_models
	 */
	public function i_can_create_an_employee_and_see_many_to_many_relation_field( AcceptanceTester $i ) {
		$i->see( 'Relationship', '.field-list div.type' );
		$i->see( 'Company', '.field-list div.widest' );

		$this->create_company( $i, 'WP Engine' );
		$this->create_company( $i, 'Another Company Name' );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );
		$i->see( 'Many Companies', 'div.field.relationship' );
		$i->click( '#atlas-content-modeler[employee][manyCompanies]' );
		$i->see( 'Select Companies', 'div.ReactModal__Content.ReactModal__Content--after-open h2' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( Locator::elementAt( 'td.checkbox input', 2 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );

		$i->waitForElementVisible( 'div.relation-model-card' );
		$i->see( 'WP Engine', 'div.relation-model-card' );
		$i->see( 'Another Company Name', 'div.relation-model-card' );
	}

	/**
	 * @before create_company_employee_models
	 */
	public function i_can_create_a_new_entry_from_an_empty_relationships_modal_table( AcceptanceTester $i ) {
		// Try to create an employee before any companies have been entered.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );
		$i->see( 'Company', 'div.field.relationship' );
		$i->click( '#atlas-content-modeler[employee][company]' );
		$i->see( 'Select Company', '.atlas-content-modeler-relationship-modal-container h2' );
		$i->waitForElementVisible( 'tr.empty' );
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

	/**
	 * Validates that one-to-one and one-to-many cardinality can not be broken.
	 *
	 * • If you’re editing a One-to-one from the A side, you can only select
	 *   Bs that aren’t already connected to As:
	 *   - A1 is connected to B1.
	 *   - A2 cannot also be connected to B1. B1 should be unselectable
	 *     when editing A2.
	 *
	 * • If you’re editing a One-to-many from the A side, you can only select
	 *   Bs that aren't connected to another A:
	 *   - A1 is connected to B1 and B2.
	 *   - A2 cannot also be connected to B1 or B2. B1 and B2 should be
	 *     unselectable when editing A2.
	 *
	 * @before create_company_employee_models
	 * @param AcceptanceTester $i
	 * @return void
	 */
	public function i_cannot_choose_an_entry_that_is_already_linked_to_another_entry( AcceptanceTester $i ) {
		// Add a one-to-many relationship field.
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'onetomany' );
		$i->selectOption( '#reference', 'Companies' );
		$i->click( 'input#one-to-many' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$this->create_company( $i, 'Company A' );

		// Add Employee 1.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );

		// Link Employee 1 to Company A via the one-to-one field.
		$i->click( '#atlas-content-modeler[employee][company]' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );
		$i->wait( 1 );

		// Link Employee 1 to Company A via the one-to-many field.
		$i->click( '#atlas-content-modeler[employee][onetomany]' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );
		$i->wait( 1 );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		// Start to add Employee 2.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=employee' );

		// Attempt to link Employee 2 to Company A via one-to-one.
		$i->click( '#atlas-content-modeler[employee][company]' );
		$i->see( 'Select Company', '.atlas-content-modeler-relationship-modal-container h2' );
		$i->waitForElementVisible( '.unselectable button' );
		$i->moveMouseOver( '.unselectable button' );
		$i->waitForElementVisible( '.tooltip-text' );
		$i->see( 'is already linked', '.tooltip-text' ); // Company A is already linked to Employee 1.
		$i->click( 'button[data-testid="relationship-modal-cancel-button"]' ); // Closes the modal.

		// Attempt to link Employee 2 to Company A via one-to-many.
		$i->click( '#atlas-content-modeler[employee][onetomany]' );
		$i->see( 'Select Companies', '.atlas-content-modeler-relationship-modal-container h2' );
		$i->waitForElementVisible( '.unselectable button' );
		$i->moveMouseOver( '.unselectable button' );
		$i->waitForElementVisible( '.tooltip-text' );
		$i->see( 'is already linked', '.tooltip-text' ); // Company A is already linked to Employee 1.
	}

	/**
	 * Validates that one-to-one and one-to-many cardinality can not be broken
	 * when editing a relationship on the reverse side.
	 *
	 * • If you’re editing a One-to-one from the B side, you can only select
	 *   As that aren’t already connected to Bs:
	 *   - B1 is connected to A1.
	 *   - B2 cannot also be connected to A1. A1 should be unselectable
	 *     when editing B2.
	 *
	 * • If you’re editing a Many-to-one from the B side (where it becomes a
	 *   One-to-many field), you can only select As that aren't already
	 *   connected to Bs.
	 *   - B1 is connected to A1 and A2.
	 *   - B2 cannot also be connected to A1 or A2. A1 and A2 should be
	 *     unselectable when editing B2.
	 *
	 * @param AcceptanceTester $i
	 * @return void
	 */
	public function i_cannot_choose_an_entry_on_the_reverse_side_that_is_linked_to_another_entry( AcceptanceTester $i ) {
		// Create a model to test restrictions from the reverse side of the relationship.
		$i->haveContentModel( 'Right', 'Rights' );

		// Create a model to set up relationship fields linking Left to Right.
		$content_model = $i->haveContentModel( 'Left', 'Lefts' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		// Add a one-to-one relationship field to the Left model that references Right, including a reverse reference.
		$i->waitForElement( '.field-buttons' );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Rights One To One' );
		$i->selectOption( '#reference', 'Rights' );
		$i->click( 'input#one-to-one' );
		$i->click( '#enable-reverse' );
		$i->wait( 1 );
		$i->see( 'Reverse Display Name' );
		$i->fillField( [ 'name' => 'reverseName' ], 'Lefts One To One' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Add a many-to-one relationship field to the Left model that references Right, including a reverse reference.
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Rights Many To One' );
		$i->selectOption( '#reference', 'Rights' );
		$i->click( 'input#many-to-one' );
		$i->click( '#enable-reverse' );
		$i->wait( 1 );
		$i->see( 'Reverse Display Name' );
		$i->fillField( [ 'name' => 'reverseName' ], 'Lefts One To Many' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Publish a new Rights post.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=right' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		// Create two new Left posts.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=left' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		$i->amOnPage( '/wp-admin/post-new.php?post_type=left' );
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		// Create a new Right post, linking both fields to the Lefts posts.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=right' );

		// Link the new Right to the first Left via the one-to-one field.
		$i->click( '#atlas-content-modeler[right][rightsOneToOne]' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );
		$i->wait( 1 );

		// Link the new Right to both Lefts via the many-to-one field.
		$i->click( '#atlas-content-modeler[right][rightsManyToOne]' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( Locator::elementAt( 'td.checkbox input', 2 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );
		$i->wait( 1 );

		// Save changes to the Right post.
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		// Start to create a second Rights post.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=right' );

		/**
		 * Confirm that a Left is not available for selection in the
		 * one-to-one field (it is already connected to the other Right).
		 */
		$i->click( '#atlas-content-modeler[right][rightsOneToOne]' );
		$i->waitForElementVisible( '.unselectable button' );
		$i->moveMouseOver( '.unselectable button' );
		$i->waitForElementVisible( '.tooltip-text' );
		$i->see( 'is already linked', '.tooltip-text' );
		$i->seeNumberOfElements( '.unselectable button', 1 ); // Only one Left should be unselectable.
		$i->click( 'button[data-testid="relationship-modal-cancel-button"]' ); // Closes the modal.

		/**
		 * Confirm that both Lefts are not available in the many-to-one field
		 * (they are already connected to the other Right).
		 */
		$i->click( '#atlas-content-modeler[right][rightsManyToOne]' );
		$i->waitForElementVisible( '.unselectable button' );
		$i->moveMouseOver( '.unselectable button' );
		$i->waitForElementVisible( '.tooltip-text' );
		$i->see( 'is already linked', '.tooltip-text' );
		$i->seeNumberOfElements( '.unselectable button', 2 ); // Both Lefts should be unselectable.
	}

	public function i_can_create_a_reverse_relationship_and_update_the_fields( AcceptanceTester $i ) {
		// Create a model to check the “to”/“B” side of the relationship.
		$i->haveContentModel( 'Right', 'Rights' );

		// Create a model to check the “from”/“A” side of the relationship.
		$content_model = $i->haveContentModel( 'Left', 'Lefts' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		// Add a relationship field to the Left model that references Right.
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Rights' );
		$i->selectOption( '#reference', 'Rights' );
		$i->click( 'input#many-to-many' );

		/**
		 * Enable the reverse reference and set a label that should be visible
		 * on the “Right” side.
		 */
		$i->click( '#enable-reverse' );
		$i->wait( 1 );
		$i->see( 'Reverse Display Name' );
		$i->fillField( [ 'name' => 'reverseName' ], 'ThisIsTheReverseReference' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Visit the Lefts publisher entry screen.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=left' );

		// Confirm that the forward relationship field title label is correct.
		$i->see( 'Rights', '#field-rights label' );

		// Confirm that the forward relationship field button label is correct.
		$i->see( 'Link Rights', 'button[data-testid="content-model-relationship-button"]' );

		// Publish the “lefts” post.
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );

		// Visit the Rights publisher entry screen.
		$i->amOnPage( '/wp-admin/post-new.php?post_type=right' );

		// Confirm that the reverse relationship field title label is correct.
		$i->see( 'ThisIsTheReverseReference', '#field-rights label' );

		// Confirm that the reverse relationship field button label is correct.
		$i->see( 'Link Lefts', 'button[data-testid="content-model-relationship-button"]' );

		// Link the right entry to the published “left” entry.
		$i->click( '#atlas-content-modeler[right][rights]' );
		$i->see( 'Select Lefts', 'div.ReactModal__Content.ReactModal__Content--after-open h2' );
		$i->waitForElementVisible( 'td.checkbox input' );
		$i->click( Locator::elementAt( 'td.checkbox input', 1 ) );
		$i->click( 'button[data-testid="relationship-modal-save-button"]' );

		// Confirm the linked entry is visible when closing the modal.
		$i->waitForElementVisible( 'div.relation-model-card' );
		$i->see( 'No Title', 'div.relation-model-card' );

		// Confirm the linked entry is visible after publishing the “rights” post.
		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 2 );
		$i->waitForElementVisible( 'div.relation-model-card' );
		$i->see( 'No Title', 'div.relation-model-card' );

		// Confirm that a link to edit the linked “left” appears in the related entry options list with the correct label.
		$i->waitForElementVisible( '.options' );
		$i->click( '.options' );
		$i->see( 'Edit Left', '.dropdown-content .edit' );

		// Remove the linked entry from the “rights” side and update it.
		$i->click( '.dropdown-content .delete' );
		$i->waitForElementNotVisible( '.options' );
		$i->dontSee( 'No Title', 'div.relation-model-card' ); // Linked entry was removed.
		$i->click( 'Update', '#publishing-action' );
		$i->wait( 2 );
		$i->dontSee( 'No Title', 'div.relation-model-card' ); // Linked entry still gone after saving the page.
	}

	/**
	 * Create company and employee models used in most tests above.
	 *
	 * Annotate tests that need this using the before keyword:
	 * https://codeception.com/docs/07-AdvancedUsage#beforeafter-annotations
	 *
	 * @param \AcceptanceTester $i
	 * @return void
	 */
	protected function create_company_employee_models( \AcceptanceTester $i ) {
		$content_model = $i->haveContentModel( 'Company', 'Companies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->wait( 2 );
		$i->click( 'Text', '.field-buttons' );
		$i->checkOption( 'input[name="isTitle"]' );
		$i->fillField( [ 'name' => 'name' ], 'Company' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$content_model = $i->haveContentModel( 'Employee', 'Employees' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->wait( 1 );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Company' );
		$i->selectOption( '#reference', 'Companies' );
		$i->click( 'input#one-to-one' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );
		$i->click( Locator::lastElement( '.add-item' ) );
		$i->click( 'Relationship', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Many Companies' );
		$i->selectOption( '#reference', 'Companies' );
		$i->click( 'input#many-to-many' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 2 );
	}

	/**
	 * Creates a company with the given $company_name
	 *
	 * @param AcceptanceTester $i
	 * @param string $company_name Name of the company to create.
	 * @return void
	 */
	private function create_company( AcceptanceTester $i, string $company_name ) {
		$i->amOnPage( '/wp-admin/post-new.php?post_type=company' );
		$i->fillField( [ 'name' => 'atlas-content-modeler[company][company]' ], $company_name );

		$i->click( 'Publish', '#publishing-action' );
		$i->wait( 1 );

		$i->see( 'Post published.' );
	}
}
