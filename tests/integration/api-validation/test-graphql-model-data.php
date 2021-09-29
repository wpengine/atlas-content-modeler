<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use PHPUnit\Runner\Exception as PHPUnitRunnerException;

class GraphQLModelDataTests extends WP_UnitTestCase {

	private $test_models;

	public function setUp(): void {
		parent::setUp();

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

	public function tearDown() {
		parent::tearDown();
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

	public function test_graphql_query_result_has_custom_fields_data(): void {
		try {
			$results = graphql(
				[
					'query' => '
				{
					publicsFields {
						nodes {
							databaseId
							title
							richText
                            numberIntergerRequired
                            dateRequired
                            booleanRequired
							manytoManyRelationship {
								nodes {
									id
								}
							}
							onetoManyRelationshipReverse {
								nodes {
									id
								}
							}
							manytoOneRelationship {
								node {
									id
								}
							}
						}
					}
				}
				',
				]
			);

			self::assertArrayHasKey( 'databaseId', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['databaseId'], 23 );

			self::assertArrayHasKey( 'title', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['title'], 'Test dog with fields' );

			self::assertArrayHasKey( 'richText', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['richText'], 'This is a rich text field' );

			self::assertArrayHasKey( 'numberIntergerRequired', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['numberIntergerRequired'], 13.0 );

			self::assertArrayHasKey( 'dateRequired', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['dateRequired'], '2021/02/13' );

			self::assertArrayHasKey( 'booleanRequired', $results['data']['publicsFields']['nodes'][0] );
			self::assertTrue( $results['data']['publicsFields']['nodes'][0]['booleanRequired'] );

			self::assertArrayHasKey( 'manytoManyRelationship', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['manytoManyRelationship'] );

			self::assertArrayHasKey( 'onetoManyRelationshipReverse', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['onetoManyRelationshipReverse'] );

			self::assertArrayHasKey( 'manytoOneRelationship', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['manytoOneRelationship'] );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}
}
