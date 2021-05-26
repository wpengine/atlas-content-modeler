<?php
/**
 * Tests Content Creation.
 *
 * @package WPE_Content_Model
 */

use function WPE\AtlasContentModeler\FormEditingExperience\set_slug;

/**
 * Class TestContentCreation
 */
class TestContentCreation extends WP_UnitTestCase {

	/**
	 * Ensure set_slug() does not manipulate slug in post creation.
	 *
	 * @return void
	 */
	public function test_regular_post_slug(): void {
		$expected = 'custom_regular_post_slug';

		$slug = 'custom_regular_post_slug';
		$post = $this->factory()->post->create_and_get([
			'post_name'   => $slug,
			'post_status' => 'publish',
			'post_type'   => 'post',
		]);
		$this->assertSame(
			$expected,
			$post->post_name
		);
	}
}
