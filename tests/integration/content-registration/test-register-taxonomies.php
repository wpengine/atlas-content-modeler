<?php
/**
 * Tests for taxonomy registration.
 */

use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register;

/**
 * Class RegisterTaxonomiesTestCases
 *
 * @package AtlasContentModeler
 */
class RegisterTaxonomiesTestCases extends WP_UnitTestCase {
	/**
	 * @var string Name of the ACM taxonomy option.
	 */
	private $taxonomy_option = 'atlas_content_modeler_taxonomies';

	/**
	 * @var array Example taxonomy data that would be stored in the
	 *            atlas_content_modeler_taxonomies option.
	 */
	private $sample_taxonomies = array(
		// A complete taxonomy.
		'ingredient' =>
		  array (
			'singular' => 'Ingredient',
			'plural' => 'Ingredients',
			'slug' => 'ingredient',
			'hierarchical' => false,
			'types' =>
			array (
			  0 => 'recipe',
			),
			'api_visibility' => 'public',
			'show_in_rest' => true,
			'show_in_graphql' => true,
		  ),
		// A taxonomy with missing values. Should gain default values when registered.
		'missing' =>
			array (
				'slug' => 'missing',
			),
		// Public and private examples to test the `public` property is set.
		'public' =>
			array (
				'slug' => 'public',
				'api_visibility' => 'public',
			),
		'private' =>
			array (
				'slug' => 'private',
				'api_visibility' => 'private',
			),
	);

	public function tearDown(): void {
		parent::tearDown();
		delete_option( $this->taxonomy_option );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_taxonomies()
	 */
	public function test_get_taxonomies_returns_empty_array_when_no_taxonomies_exist(): void {
		self::assertSame( get_acm_taxonomies(), [] );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_taxonomies()
	 */
	public function test_get_taxonomies_returns_stored_values(): void {
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		self::assertSame( get_acm_taxonomies(), $this->sample_taxonomies );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register()
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_props()
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\set_defaults()
	 */
	public function test_registering_partial_taxonomy_sets_defaults(): void {
		wp_set_current_user(1);
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		register();

		$missing = get_taxonomy( 'missing' );

		self::assertSame( 'Tags', $missing->label );
		self::assertSame( true, $missing->show_in_menu );
		self::assertSame( true, $missing->show_ui );
		self::assertSame( 'manage_categories', $missing->cap->manage_terms );
		self::assertSame( true, $missing->show_in_graphql );
		self::assertSame( true, $missing->show_in_rest );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register()
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_props()
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\set_defaults()
	 */
	public function test_registering_full_taxonomy_registers_custom_values(): void {
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		register();

		$ingredients = get_taxonomy( 'ingredient' );

		self::assertSame('Ingredients', $ingredients->label );
		self::assertSame('Ingredient', $ingredients->labels->singular_name );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register()
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_props()
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\Taxonomies\set_defaults()
	 */
	public function test_taxonomy_privacy_is_set_correctly(): void {
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		register();

		$public  = get_taxonomy( 'public' );
		$private = get_taxonomy( 'private' );
		$missing = get_taxonomy( 'missing' );

		self::assertSame( true, $public->public );
		self::assertSame( false, $private->public );
		self::assertSame( false, $missing->public );
	}
}
