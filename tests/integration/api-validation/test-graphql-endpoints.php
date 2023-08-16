<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use PHPUnit\Runner\Exception as PHPUnitRunnerException;

class GraphQLEndpointTests extends WP_UnitTestCase {

	private $test_models;
	public $factory;

	public function set_up(): void {
		parent::set_up();

		$this->test_models = $this->get_models();

		update_registered_content_types( $this->test_models );

		/**
		 * Reset the WPGraphQL schema before each test.
		 * Lazy loading types only loads part of the schema,
		 * so we refresh for each test.
		 */
		WPGraphQL::clear_schema();

		// Start each test with a fresh relationships registry.
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();

		// @todo why is this not running automatically?
		do_action( 'init' );

		$this->post_ids = $this->get_post_ids();
	}

	public function tear_down() {
		parent::tear_down();
		wp_set_current_user( null );
		delete_option( 'atlas_content_modeler_post_types' );
	}

	private function get_models() {
		return include __DIR__ . '/test-data/models.php';
	}

	private function get_post_ids() {
		include_once __DIR__ . '/test-data/posts.php';

		return create_test_posts( $this );
	}

	/**
	 * Ensure a private model's data is not publicly queryable in GraphQL
	 */
	public function test_post_type_with_private_api_visibility_cannot_be_read_via_graphql_when_not_authenticated(): void {
		$graphql_settings                       = get_option( 'graphql_general_settings', [] );
		$graphql_settings['debug_mode_enabled'] = 'on';
		update_option( 'graphql_general_settings', $graphql_settings );
		try {
			$results = graphql(
				[
					'query' => '
				{
					privatesFields {
						nodes {
							databaseId
						}
					}
				}
				',
				]
			);

			self::assertEmpty( $results['data']['privatesFields']['nodes'] );
			self::assertContains( 'ACM_UNAUTHORIZED_REQUEST', array_column( $results['extensions']['debug'], 'type' ) );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	/**
	 * Ensure a post on a private model is accessible to an authenticated user.
	 */
	public function test_post_type_with_private_api_visibility_can_be_read_via_graphql_when_authenticated(): void {
		wp_set_current_user( 1 );
		try {
			$results = graphql(
				[
					'query' => '
				{
					privatesFields {
						nodes {
							databaseId
						}
					}
				}
				',
				]
			);

			self::assertSame( $results['data']['privatesFields']['nodes'][0]['databaseId'], $this->post_ids['private_fields_post_id'] );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	/**
	 * Ensure GraphQL includes support data like author
	 */
	public function test_graphql_can_query_by_author_id(): void {
		try {
			$results = graphql(
				[
					'query' => '
				{
					privatesFields(where: {author: 1}) {
						nodes {
							databaseId
						}
					}
				}
				',
				]
			);

			self::assertArrayHasKey( 'privatesFields', $results['data'] );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}
}
