<?php
/**
 * Tests Content Creation.
 *
 * @package WPE_Content_Model
 */

use function WPE\ContentModel\FormEditingExperience\set_slug;

/**
 * Class TestContentCreation
 */
class TestContentCreation extends WP_UnitTestCase {

	public function test_regular_post_slug(): void {
		// Use set_slug() in a regular post creation with a defined slug and ensure it doesn't use custom ID slug.
		$slug = 'custom_regular_post_slug';

		$expected = 'custom_regular_post_slug';

		$this->assertSame(
			$expected,
			$test
		);
	}

	public function test_custom_model_post_slug(): void {
		// Use set_slug() in a custom model post creation and ensure the default ID is a number or a specific number.
		$slug = '13';

		$expected = '13';

		$this->assertSame(
			$expected,
			$test
		);
	}
}
