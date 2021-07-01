<?php

use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register;

/**
 * Class TestRestWPTaxonomy
 *
 * Checks ACM taxonomies are available via WP core endpoints under `/wp/v2/`.
 */
class TestRestWPTaxonomy extends WP_UnitTestCase {
	/**
	 * @var WP_REST_Server Server instance to send requests from.
	 */
	private $server;

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
		'show-in-rest' =>
			array(
				'slug' => 'show-in-rest',
				'api_visibility' => 'public',
				'show_in_rest' => true,
			),
		'hide-in-rest' =>
			array (
				'slug' => 'hide-in-rest',
				'api_visibility' => 'public',
				'show_in_rest' => false,
			),
		'private-visibility' =>
			array(
				'slug' => 'private-visibility',
				'api_visibility' => 'private',
				'show_in_rest' => true,
			),
	);

	public function setUp() {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		register();
		do_action( 'rest_api_init' );

		// Adds terms to each taxonomy to test visibility of term REST endpoints.
		foreach ( $this->sample_taxonomies as $taxonomy => $_unused ) {
			$term = wp_insert_term( $taxonomy . '-term', $taxonomy );
			$this->term_ids[ $taxonomy ] = $term['term_id'];
		}
	}

	public function test_terms_are_visible_if_show_in_rest_is_true() {
		$request  = new WP_REST_Request( 'GET', "/wp/v2/show-in-rest/{$this->term_ids['show-in-rest']}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'taxonomy', $data );
		$this->assertEquals( 'show-in-rest', $data['taxonomy'] );
	}

	public function test_terms_are_not_visible_if_show_in_rest_is_false() {
		$request  = new WP_REST_Request( 'GET', "/wp/v2/hide-in-rest/{$this->term_ids['hide-in-rest']}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 404, $response->get_status() );
		$this->assertArrayHasKey( 'code', $data );
		$this->assertEquals( 'rest_no_route', $data['code'] );
	}

	public function test_terms_are_not_visible_by_default_if_api_visibility_is_private() {
		wp_set_current_user( null );
		$request  = new WP_REST_Request( 'GET', "/wp/v2/private-visibility/{$this->term_ids['private-visibility']}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 404, $response->get_status() );
		$this->assertArrayHasKey( 'code', $data );
		$this->assertEquals( 'rest_no_route', $data['code'] );
	}

	public function test_terms_are_visible_to_capable_user_if_api_visibility_is_private() {
		wp_set_current_user( 1 );

		// Call register and invoke rest_api_init again because the user has changed.
		// show_in_rest is based on user capabilities for private taxonomies.
		register();
		do_action( 'rest_api_init' );

		$request  = new WP_REST_Request( 'GET', "/wp/v2/private-visibility/{$this->term_ids['private-visibility']}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'taxonomy', $data );
		$this->assertEquals( 'private-visibility', $data['taxonomy'] );
	}

	public function test_taxonomy_is_not_visible_by_default_if_api_visibility_is_private() {
		wp_set_current_user( null );
		register();
		do_action( 'rest_api_init' );

		$request  = new WP_REST_Request( 'GET', "/wp/v2/taxonomies" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayNotHasKey( 'private-visibility', $data );
	}

	public function test_taxonomy_is_visible_to_capable_user_if_api_visibility_is_private() {
		wp_set_current_user( 1 );
		register();
		do_action( 'rest_api_init' );

		$request  = new WP_REST_Request( 'GET', "/wp/v2/taxonomies" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'private-visibility', $data );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server = null;
		delete_option( $this->taxonomy_option );
		foreach ( $this->term_ids as $taxonomy => $id ) {
			wp_delete_term( $id, $taxonomy );
		}
		$this->term_ids = [];
	}
}
