<?php


/**
 * Inherited Methods
 *
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class AcceptanceTester extends \Codeception\Actor {

	use _generated\AcceptanceTesterActions;

	/**
	 * Visit WPEngine Content Edit Model page.
	 *
	 * @param string $id The content model id.
	 */
	public function amOnWPEngineEditContentModelPage( $id ) {
		$this->amOnWPEngineContentModelPage( "&view=edit-model&id={$id}" );
	}

	/**
	 * Visit WPEngine Create Content Model page.
	 */
	public function amOnWPEngineCreateContentModelPage() {
		$this->amOnWPEngineContentModelPage( '&view=create-model' );
	}

	/**
	 * Visit WPEngine Content Model page.
	 *
	 * @param string $params Optional query parameter string.
	 */
	public function amOnWPEngineContentModelPage( $params = '' ) {
		$path = '/wp-admin/admin.php?page=atlas-content-modeler';

		if ( $params ) {
			$path .= $params;
		}

		$this->amOnPage( $path );
	}

	/**
	 * Visit the Taxonomy page.
	 *
	 * @param string $params Optional query parameter string.
	 */
	public function amOnTaxonomyListingsPage( $params = '' ) {
		$path = '/wp-admin/admin.php?page=atlas-content-modeler&view=taxonomies';

		if ( $params ) {
			$path .= $params;
		}

		$this->amOnPage( $path );
	}

	/**
	 * Create a Content Model.
	 *
	 * @param string $singular    Singular content model name.
	 * @param string $plural      Plural content model name.
	 * @param string $description Content model description.
	 *
	 * @return array The content model.
	 */
	public function haveContentModel( string $singular, string $plural, array $args = [] ) {
		$content_model                            = $this->makeContentModel( $singular, $plural, $args );
		$content_models                           = $this->grabContentModels();
		$content_models[ $content_model['slug'] ] = $content_model;
		$this->haveOptionInDatabase( 'atlas_content_modeler_post_types', $content_models );

		return $content_model;
	}

	/**
	 * Create a Taxonomy.
	 *
	 * @param string $singular Singular taxonomy name.
	 * @param string $plural   Plural taxonomy name.
	 * @param array  $types    Slug name of the models that have this taxonomy.
	 */
	public function haveTaxonomy( $singular, $plural, array $types ) {
		$this->amOnTaxonomyListingsPage();
		$this->wait( 1 );

		$this->fillField( [ 'name' => 'singular' ], $singular );
		$this->fillField( [ 'name' => 'plural' ], $plural );

		foreach ( $types as $type ) {
			$this->click( ".checklist .checkbox input[value={$type}]" );
		}

		$this->click( '.card-content button.primary' );
	}

	/**
	 * Make a non-persistant content model.
	 *
	 * @param string $singular The singular model name.
	 * @param string $plural   The plural model name.
	 * @param array  $args     Optional content model args.
	 *
	 * @return array The content model.
	 */
	public function makeContentModel( string $singular, string $plural, array $args = [] ) {
		$content_model = array_merge(
			[
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'slug'            => strtolower( $singular ),
				'api_visibility'  => 'private',
				'model_icon'      => 'dashicons-admin-post',
				'description'     => '',
				'fields'          => [],
			],
			$args
		);

		$content_model['singular'] = $singular;
		$content_model['plural']   = $plural;

		return $content_model;
	}

	/**
	 * Retrieve all content models from the options database.
	 *
	 * @return array Associative list of content models.
	 */
	public function grabContentModels() {
		$content_models = $this->grabOptionFromDatabase( 'atlas_content_modeler_post_types' );

		return ! empty( $content_models ) ? $content_models : [];
	}
}
