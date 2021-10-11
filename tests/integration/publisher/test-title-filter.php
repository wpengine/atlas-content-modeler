<?php
/**
 * Tests Title Filtering.
 *
 * @package AtlasContentModeler
 */

/**
 * Class TestTitleFilter
 */
class TestTitleFilter extends WP_UnitTestCase {

	private $form_editing_experience;

	public function setUp() {
		parent::setUp();

		$this->form_editing_experience = new \WPE\AtlasContentModeler\FormEditingExperience();
	}

	/**
	 * Ensures the title filter only applies if posts have a known post type.
	 *
	 * @covers \WPE\AtlasContentModeler\FormEditingExperience()->filter_post_titles();
	 */
	public function test_title_filter_returns_unaltered_title_if_post_has_no_post_type(): void {
		$original_title  = 'Original title';
		$unknown_post_id = PHP_INT_MAX;

		$filtered_title = $this->form_editing_experience->filter_post_titles(
			$original_title,
			$unknown_post_id // To test that no filtering takes place if a post cannot be found.
		);

		$this->assertSame( $original_title, $filtered_title );
	}
}
