<?php
/**
 * Tests for settings-related functions.
 */

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_type;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Class SettingsFunctionsTestCases
 *
 * @package AtlasContentModeler
 */
class SettingsFunctionsTestCases extends WP_UnitTestCase {

	public function tear_down(): void {
		parent::tear_down();
		delete_option( 'atlas_content_modeler_post_types' );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\get_register_content_types()
	 */
	public function test_get_registered_content_types_returns_empty_array_when_no_content_types_exist(): void {
		self::assertSame( get_registered_content_types(), [] );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\get_register_content_types()
	 */
	public function test_get_registered_content_types_returns_expected_data(): void {
		update_registered_content_types( $this->expected_post_types() );
		self::assertSame( get_registered_content_types(), $this->expected_post_types() );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\get_register_content_types()
	 * @todo Consider removing prior to v1.0
	 */
	public function test_get_registered_content_types_formats_model_slugs(): void {
		$deprecated_models = [
			'bookReviews' => [
				'slug'        => 'bookReviews',
				'singular'    => 'Book Review',
				'plural'      => 'Book Reviews',
				'description' => 'This model should have a reformatted slug.',
			],
			'unchanged'   => [
				'slug'        => 'unchanged',
				'singular'    => 'Unchanged Model',
				'plural'      => 'Unchanged Models',
				'description' => 'This model should be unaffected.',
			],
		];

		$updated_models = [
			'bookreviews' => [
				'slug'        => 'bookreviews',
				'singular'    => 'Book Review',
				'plural'      => 'Book Reviews',
				'description' => 'This model should have a reformatted slug.',
			],
			'unchanged'   => $deprecated_models['unchanged'],
		];

		update_registered_content_types( $deprecated_models );

		self::assertSame( $updated_models, get_registered_content_types() );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\update_registered_content_type()
	 */
	public function test_update_registered_content_type_returns_false_when_specified_content_type_does_not_exist(): void {
		self::assertFalse( update_registered_content_type( 'nope', [] ) );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\update_registered_content_type()
	 */
	public function test_update_registered_content_type_properly_updates_existing_content_type(): void {
		$org = $this->expected_post_types();
		update_registered_content_types( $org );

		$new                           = $org;
		$new['cat']['show_in_graphql'] = true;

		self::assertTrue( update_registered_content_type( 'cat', $new['cat'] ) );
		self::assertSame( get_registered_content_types(), $new );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types()
	 */
	public function test_update_registered_content_types_saves_to_database(): void {
		self::assertTrue( update_registered_content_types( $this->expected_post_types() ) );
		self::assertSame( get_registered_content_types(), $this->expected_post_types() );
	}
	private function expected_post_types(): array {
		return include __DIR__ . '/example-data/expected-post-types.php';
	}
}
