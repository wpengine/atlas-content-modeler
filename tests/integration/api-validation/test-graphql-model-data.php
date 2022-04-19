<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use PHPUnit\Runner\Exception as PHPUnitRunnerException;

class GraphQLModelDataTests extends WP_UnitTestCase {

	private $test_models;

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

		// Initialize the publisher logic, which includes additional filters.
		new \WPE\AtlasContentModeler\FormEditingExperience();

		// @todo why is this not running automatically?
		do_action( 'init' );

		$this->post_ids = $this->get_post_ids();

		$this->valid_mutation_query = [
			'query' => '
				mutation CREATE_PUBLIC_FIELDS_ENTRY {
					createPublicFields(
						input: {
							clientMutationId: "CreatePublicFields"
							status: PUBLISH
							singleLineRequired: "Created with a GraphQL mutation"
							richText: "<p>Rich Text Content</p>"
							richTextRepeatable: ["<p>Rich Text 1</p>", "<p>Rich Text 2</p>"]
							numberIntergerRequired: 1.0
							numberIntegerRepeat: [ 1.0, 2.0, 3.0]
							dateRequired: "2022-01-01"
							dateRepeatable: ["2022-01-01", "2022-01-02"]
							multiSingle: ["kiwi"]
							multipleMulti: ["apple", "banana"]
							booleanRequired: true
						}
					) {
						publicFields {
							title
							singleLineRequired
							richText
							richTextRepeatable
							numberIntergerRequired
							numberIntegerRepeat
							dateRequired
							dateRepeatable
							multiSingle
							multipleMulti
							booleanRequired
						}
					}
				}
			',
		];
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
							richTextRepeatable
							numberIntergerRequired
							numberIntegerRepeat
							mediaRepeat
							dateRequired
							dateRepeatable
							multiSingle
							multipleMulti
							booleanRequired
							featuredImageDatabaseId
							featuredImageId
							manytoManyRelationship {
								nodes {
									id
								}
							}
							manytoOneRelationship {
								node {
									id
								}
							}
							manytoManyRelationshipReverse {
								nodes {
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

			self::assertArrayHasKey( 'title', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['title'], 'This is required single line text' );

			self::assertArrayHasKey( 'richText', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['richText'], 'This is a rich text field' );

			self::assertArrayHasKey( 'richTextRepeatable', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['richTextRepeatable'][0], '<p>First</p>' );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['richTextRepeatable'][1], '<p>Second</p>' );

			self::assertArrayHasKey( 'dateRepeatable', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['dateRepeatable'] );

			self::assertArrayHasKey( 'numberIntergerRequired', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['numberIntergerRequired'], 13.0 );

			self::assertArrayHasKey( 'numberIntegerRepeat', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['numberIntegerRepeat'] );

			self::assertArrayHasKey( 'mediaRepeat', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['mediaRepeat'] );

			self::assertArrayHasKey( 'dateRequired', $results['data']['publicsFields']['nodes'][0] );
			self::assertSame( $results['data']['publicsFields']['nodes'][0]['dateRequired'], '2021/02/13' );

			self::assertArrayHasKey( 'booleanRequired', $results['data']['publicsFields']['nodes'][0] );
			self::assertTrue( $results['data']['publicsFields']['nodes'][0]['booleanRequired'] );

			self::assertArrayHasKey( 'featuredImageId', $results['data']['publicsFields']['nodes'][0] );
			self::assertArrayHasKey( 'featuredImageDatabaseId', $results['data']['publicsFields']['nodes'][0] );

			self::assertArrayHasKey( 'manytoManyRelationship', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['manytoManyRelationship'] );

			self::assertArrayHasKey( 'manytoManyRelationshipReverse', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['manytoManyRelationshipReverse'] );

			self::assertArrayHasKey( 'manytoOneRelationship', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['manytoOneRelationship'] );

			self::assertArrayHasKey( 'multiSingle', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['multiSingle'] );

			self::assertArrayHasKey( 'multipleMulti', $results['data']['publicsFields']['nodes'][0] );
			self::assertIsArray( $results['data']['publicsFields']['nodes'][0]['multipleMulti'] );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_graphql_create_mutations_accept_acm_fields_as_inputs(): void {
		wp_set_current_user( 1 );
		try {
			$response = graphql( $this->valid_mutation_query );

			$mutation = $response['data']['createPublicFields']['publicFields'];

			self::assertArrayHasKey( 'title', $mutation );
			self::assertSame( $mutation['title'], 'Created with a GraphQL mutation' );

			self::assertArrayHasKey( 'singleLineRequired', $mutation );
			self::assertSame( $mutation['singleLineRequired'], 'Created with a GraphQL mutation' );

			self::assertArrayHasKey( 'richText', $mutation );
			self::assertSame( $mutation['richText'], '<p>Rich Text Content</p>' );

			self::assertArrayHasKey( 'richTextRepeatable', $mutation );
			self::assertSame( $mutation['richTextRepeatable'], [ '<p>Rich Text 1</p>', '<p>Rich Text 2</p>' ] );

			self::assertArrayHasKey( 'numberIntergerRequired', $mutation );
			self::assertSame( $mutation['numberIntergerRequired'], 1.0 );

			self::assertArrayHasKey( 'numberIntegerRepeat', $mutation );
			self::assertSame( $mutation['numberIntegerRepeat'], [ 1.0, 2.0, 3.0 ] );

			self::assertArrayHasKey( 'dateRequired', $mutation );
			self::assertSame( $mutation['dateRequired'], '2022-01-01' );

			self::assertArrayHasKey( 'dateRepeatable', $mutation );
			self::assertSame( $mutation['dateRepeatable'], [ '2022-01-01', '2022-01-02' ] );

			self::assertArrayHasKey( 'multiSingle', $mutation );
			self::assertSame( $mutation['multiSingle'], [ 'kiwi' ] );

			self::assertArrayHasKey( 'multipleMulti', $mutation );
			self::assertSame( $mutation['multipleMulti'], [ 'apple', 'banana' ] );

			self::assertArrayHasKey( 'booleanRequired', $mutation );
			self::assertTrue( $mutation['booleanRequired'] );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_graphql_create_mutations_must_provide_required_fields(): void {
		wp_set_current_user( 1 );
		try {
			$response = graphql(
				[
					// This query omits all required fields and should fail.
					'query' => '
						mutation CREATE_PUBLIC_FIELDS_ENTRY {
							createPublicFields(
								input: {
									clientMutationId: "CreatePublicFields"
									status: PUBLISH
									richText: "<p>Rich Text Content</p>"
									richTextRepeatable: ["<p>Rich Text 1</p>", "<p>Rich Text 2</p>"]
									numberIntegerRepeat: [ 1.0, 2.0, 3.0]
									dateRepeatable: ["2022-01-01", "2022-01-02"]
									multiSingle: ["kiwi"]
									multipleMulti: ["apple", "banana"]
								}
							) {
								publicFields {
									title
								}
							}
						}
					',
				]
			);

			self::assertArrayHasKey( 'errors', $response );

			$error_messages = wp_list_pluck( $response['errors'], 'message' );

			$expected_messages = [
				'Field CreatePublicFieldsInput.booleanRequired of required type Boolean! was not provided.',
				'Field CreatePublicFieldsInput.dateRequired of required type String! was not provided.',
				'Field CreatePublicFieldsInput.numberIntergerRequired of required type Float! was not provided.',
				'Field CreatePublicFieldsInput.singleLineRequired of required type String! was not provided.',

			];

			foreach ( $expected_messages as $expected_message ) {
				self::assertContains( $expected_message, $error_messages );
			}
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_graphql_create_mutations_require_authentication(): void {
		// Log out to check that mutation attempts then fail.
		wp_set_current_user( null );

		try {
			$response = graphql( $this->valid_mutation_query );

			self::assertArrayHasKey( 'errors', $response );

			$error_messages   = wp_list_pluck( $response['errors'], 'message' );
			$expected_message = 'Sorry, you are not allowed to create publicsFields';

			self::assertContains( $expected_message, $error_messages );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

}
