<?php
/**
 * Tests for script dependencies.
 *
 * @package AtlasContentModeler
 */

use WPE\AtlasContentModeler\FormEditingExperience;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Class TestPublisherScriptDependencies
 */
class TestPublisherScriptDependencies extends WP_UnitTestCase {

	private $models = [
		'recipe' => [
			'singular' => 'Recipe',
			'plural'   => 'Recipes',
			'slug'     => 'recipe',
		],
	];

	public function setUp() {
		wp_set_current_user( 1 );

		parent::setUp();

		update_registered_content_types( $this->models );

		$pubex = new FormEditingExperience();
		$pubex->bootstrap();

		do_action( 'init' );

		set_current_screen( 'post.php' );
		global $current_screen;
		$current_screen->post_type = 'recipe';
		do_action( 'admin_enqueue_scripts', 'post.php' );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		delete_option( 'atlas_content_modeler_post_types' );
	}

	public function test_wp_i18n_script_is_enqueued_on_the_publisher_view(): void {
		self::assertTrue( wp_script_is( 'wp-i18n', 'enqueued' ) );
	}

	public function test_wp_api_fetch_script_is_enqueued_on_the_publisher_view(): void {
		self::assertTrue( wp_script_is( 'wp-api-fetch', 'enqueued' ) );
	}

	public function test_acm_feedback_banner_script_is_enqueued_on_the_publisher_view(): void {
		self::assertTrue( wp_script_is( 'atlas-content-modeler-feedback-banner', 'enqueued' ) );
	}

	public function test_acm_pubex_script_is_enqueued(): void {
		self::assertTrue( wp_script_is( 'atlas-content-modeler-form-editing-experience', 'enqueued' ) );
	}
}