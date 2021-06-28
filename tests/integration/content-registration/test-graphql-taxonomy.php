<?php

use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register;

class TestGraphQLTaxonomyEndpoint extends WP_UnitTestCase {
	/**
	 * @var string The option name for ACM taxonomies.
	 */
	private $taxonomy_option = 'atlas_content_modeler_taxonomies';

	/**
	 * @var array IDs of terms to use in REST requests.
	 */
	private $term_ids = [];

	/**
	 * @var array Taxonomies to test against.
	 */
	private $sample_taxonomies = array(
		'show-in-graphqls' =>
			array(
				'slug' => 'show-in-graphqls',
				'api_visibility' => 'public',
				'show_in_graphql' => true,
			),
		'hide-in-graphqls' =>
			array (
				'slug' => 'hide-in-graphqls',
				'api_visibility' => 'public',
				'show_in_graphql' => false,
			),
		'private-visibilities' =>
			array(
				'slug' => 'private-visibilities',
				'api_visibility' => 'private',
				'show_in_graphql' => true,
			),
	);

	public function setUp() {
		parent::setUp();
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		register();

		// Adds terms to each taxonomy to test visibility of term REST endpoints.
		foreach ( $this->sample_taxonomies as $taxonomy => $_unused ) {
			$term = wp_insert_term( $taxonomy . '-term', $taxonomy );
			$this->term_ids[ $taxonomy ] = $term['term_id'];
		}
		do_action( 'graphql_register_types_late' );
	}

	public function test_taxonomies_are_visible_if_show_in_graphql_is_true() {
		$graphql = graphql([
			'query' => '{
				taxonomy(idType: NAME, id: "show-in-graphqls") {
					name
				}
			}'
		]);

		$this->assertEquals( 'show-in-graphqls', $graphql['data']['taxonomy']['name'] );
	}

	public function test_taxonomies_are_not_visible_if_show_in_graphql_is_false() {
		$graphql = graphql([
			'query' => '{
				taxonomy(idType: NAME, id: "hide-in-graphqls") {
					name
				}
			}'
		]);

		$this->assertEmpty( $graphql['data']['taxonomy'] );
	}

	public function test_taxonomy_is_not_visible_by_default_if_api_visibility_is_private() {
		wp_set_current_user( null );

		$graphql = graphql([
			'query' => '{
				taxonomy(idType: NAME, id: "private-visibilities") {
					name
				}
			}'
		]);

		$this->assertEmpty( $graphql['data']['taxonomy'] );
	}

	public function test_taxonomy_is_visible_to_capable_user_if_api_visibility_is_private() {
		wp_set_current_user( 1 );

		$graphql = graphql([
			'query' => '{
				taxonomy(idType: NAME, id: "private-visibilities") {
					name
				}
			}'
		]);

		$this->assertEquals( 'private-visibilities', $graphql['data']['taxonomy']['name'] );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		delete_option( $this->taxonomy_option );
	}
}
