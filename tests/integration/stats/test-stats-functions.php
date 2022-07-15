<?php
/**
 * Tests for stats functions.
 */

use function WPE\AtlasContentModeler\API\insert_model_entry;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\Stats\stats_model_counts;
use function WPE\AtlasContentModeler\Stats\stats_recent_model_entries;
use function WPE\AtlasContentModeler\Stats\stats_relationships;
use function WPE\AtlasContentModeler\Stats\stats_taxonomies;

/**
 * Class StatsFunctionTests
 */
class StatsFunctionTests extends WP_UnitTestCase {
	public function set_up(): void {
		parent::set_up();
		wp_set_current_user( 1 );

		global $wpdb;
		$option_name = $wpdb->prefix . 'acm_post_to_post_schema_version';
		delete_option( $option_name );

		update_registered_content_types( $this->mock_models() );
		update_option( 'atlas_content_modeler_taxonomies', $this->mock_taxonomies() );
		WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register();
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();
		do_action( 'init' );
	}

	public function tear_down(): void {
		delete_option( 'atlas_content_modeler_post_types' );
		delete_option( 'atlas_content_modeler_taxonomies' );
		wp_set_current_user( null );
		parent::tear_down();
	}

	public function test_stats_model_counts_returns_expected_counts(): void {
		$this->create_test_entries();
		$counts = stats_model_counts();
		self::assertSame( 'person', $counts[0]['model'] );
		self::assertSame( '2', $counts[0]['count'] );
		self::assertSame( 'company', $counts[1]['model'] );
		self::assertSame( '1', $counts[1]['count'] );
	}

	public function test_stats_model_counts_returns_empty_array_if_not_authenticated(): void {
		$this->create_test_entries();
		wp_set_current_user( null );
		self::assertSame( [], stats_model_counts() );
	}

	public function test_stats_model_counts_returns_empty_array_when_no_models_exist(): void {
		update_registered_content_types( [] );
		self::assertSame( [], stats_model_counts() );
	}

	public function test_stats_recent_model_entries_returns_expected_entries(): void {
		$this->create_test_entries();
		$entries = stats_recent_model_entries();
		self::assertCount( 3, $entries );
		self::assertSame( 'ACME, Inc.', $entries[0]->post_title );
	}

	public function test_stats_recent_model_entries_returns_empty_array_if_not_authenticated(): void {
		$this->create_test_entries();
		wp_set_current_user( null );
		self::assertSame( [], stats_recent_model_entries() );
	}

	public function test_stats_relationships_returns_expected_results(): void {
		$this->create_test_entries();
		$stats = stats_relationships();
		self::assertSame( '2', $stats['mostConnectedEntries'][0]['total_connections'] );
	}

	public function test_stats_relationships_returns_empty_array_if_not_authenticated(): void {
		$this->create_test_entries();
		wp_set_current_user( null );
		self::assertSame( [], stats_relationships() );
	}

	public function test_stats_taxonomies_returns_expected_results(): void {
		$this->create_test_entries();
		$stats = stats_taxonomies();
		self::assertSame( '0', $stats['occupation']['total_terms'] );
		self::assertSame( '3', $stats['skill']['total_terms'] );
	}

	public function test_stats_taxonomies_returns_empty_array_if_not_authenticated(): void {
		$this->create_test_entries();
		wp_set_current_user( null );
		self::assertSame( [], stats_taxonomies() );
	}

	private function mock_models(): array {
		return include __DIR__ . '/test-data/content-models.php';
	}

	private function mock_taxonomies(): array {
		return include __DIR__ . '/test-data/taxonomies.php';
	}

	private function create_test_entries(): void {
		$john_doe_id = insert_model_entry(
			'person',
			[
				'name' => 'John Doe',
			],
			[
				'post_status' => 'publish',
				'tax_input'   => [
					'skill' => [ 'coding', 'meeting', 'laughing' ],
				],
			]
		);

		$jane_doe_id = insert_model_entry(
			'person',
			[
				'name' => 'Jane Doe',
			],
			[
				'post_status' => 'publish',
			]
		);

		insert_model_entry(
			'company',
			[
				'companyName' => 'ACME, Inc.',
				'employees'   => [ $john_doe_id, $jane_doe_id ],
			],
			[
				'post_status' => 'publish',
			]
		);
	}
}
