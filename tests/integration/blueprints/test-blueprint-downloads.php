<?php
use function WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint;
use function WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir;

class TestBlueprintDownloadTestCases extends WP_UnitTestCase {

	public function set_up() {
		parent::set_up();
		require_once ABSPATH . '/wp-includes/http.php';
		do_action( 'init' );
	}

	public function test_get_remote_blueprint_returns_WP_Error_when_an_empty_url_is_provided(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( '' );
		$this->assertWPError( $response );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	public function test_get_remote_blueprint_returns_WP_Error_when_an_empty_response_is_encountered(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://emptyresponse' );
		$this->assertWPError( $response );
		self::assertSame( 'acm_blueprint_http_error_response_code', $response->get_error_code() );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	public function test_get_remote_blueprint_returns_WP_Error_when_an_empty_response_body_is_encountered(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://emptybody' );
		$this->assertWPError( $response );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	public function test_get_remote_blueprint_returns_WP_Error_when_a_non_200_response_is_encountered(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://status404' );
		$this->assertWPError( $response );
		self::assertSame( 'acm_blueprint_http_error_response_code', $response->get_error_code() );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	public function test_save_blueprint_to_upload_dir_returns_WP_Error_when_an_invalid_blueprint_file_is_encountered(): void {
		$saved = save_blueprint_to_upload_dir( 'invalid file', 'test.zip' );
		$this->assertWPError( $saved );
		self::assertSame( 'acm_blueprint_unsupported_file_type', $saved->get_error_code() );
	}

	public function test_save_blueprint_to_upload_dir_returns_true_when_give_a_valid_blueprint(): void {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$blueprint = file_get_contents( __DIR__ . '/test-data/acm-rabbits.zip' );
		$saved     = save_blueprint_to_upload_dir( $blueprint, 'acm-rabbits.zip' );
		self::assertTrue( $saved );
		self::assertTrue( is_readable( trailingslashit( wp_upload_dir()['path'] ) . 'acm-rabbits.zip' ) );
	}

	/**
	 * Intercepts blueprint download requests and returns a mocked response
	 * when attached to the `pre_http_request` filter.
	 *
	 * @param $preempt
	 * @param $args
	 * @param $url
	 *
	 * @return array|string[]
	 */
	public function filter_wp_remote_get_return_value( $preempt, $args, $url ) {
		switch ( $url ) {
			case 'http://emptyresponse':
				return [];
			case 'http://emptybody':
				return [ 'body' => '' ];
			case 'http://status404':
				return [
					'response' => [
						'code'    => 404,
						'message' => 'Not found',
					],
					'status'   => 404,
				];
			default:
				return $preempt;
		}
	}
}
