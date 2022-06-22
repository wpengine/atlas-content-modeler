<?php
use function WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint;
use function WPE\AtlasContentModeler\Blueprint\Fetch\get_local_blueprint;
use function WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir;

class TestBlueprintDownloadTestCases extends WP_UnitTestCase {

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_local_blueprint
	 */
	public function test_get_local_blueprint_returns_WP_Error_when_an_empty_path_is_provided(): void {
		$file = get_local_blueprint( '' );
		$this->assertWPError( $file );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_local_blueprint
	 */
	public function test_get_local_blueprint_returns_valid_zip_file(): void {
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		$blueprint = get_local_blueprint( __DIR__ . '/test-data/acm-rabbits.zip' );
		$this->assertNotWPError( $blueprint );

		$file_info = new finfo( FILEINFO_MIME );
		self::assertSame( 'application/zip; charset=binary', $file_info->buffer( $blueprint ) );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint
	 */
	public function test_get_remote_blueprint_returns_WP_Error_when_an_empty_url_is_provided(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( '' );
		$this->assertWPError( $response );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint
	 */
	public function test_get_remote_blueprint_returns_WP_Error_when_an_empty_response_is_encountered(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://emptyresponse' );
		$this->assertWPError( $response );
		self::assertSame( 'acm_blueprint_http_error_response_code', $response->get_error_code() );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint
	 */
	public function test_get_remote_blueprint_returns_WP_Error_when_an_empty_response_body_is_encountered(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://emptybody' );
		$this->assertWPError( $response );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint
	 */
	public function test_get_remote_blueprint_returns_WP_Error_when_a_non_200_response_is_encountered(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://status404' );
		$this->assertWPError( $response );
		self::assertSame( 'acm_blueprint_http_error_response_code', $response->get_error_code() );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint
	 */
	public function test_get_remote_blueprint_returns_blueprint_zip_when_given_valid_url_to_zip(): void {
		add_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
		$response = get_remote_blueprint( 'http://valid' );
		$this->assertSame( $response, $this->get_test_blueprint_zip() );
		remove_filter( 'pre_http_request', [ $this, 'filter_wp_remote_get_return_value' ], 10, 3 );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir
	 */
	public function test_save_blueprint_to_upload_dir_returns_WP_Error_when_an_invalid_blueprint_file_is_encountered(): void {
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		$saved = save_blueprint_to_upload_dir( 'invalid file', 'test.zip' );
		$this->assertWPError( $saved );
		self::assertSame( 'acm_blueprint_unsupported_file_type', $saved->get_error_code() );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir
	 */
	public function test_save_blueprint_to_upload_dir_saves_file_when_give_a_valid_blueprint(): void {
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		$blueprint   = $this->get_test_blueprint_zip();
		$destination = save_blueprint_to_upload_dir( $blueprint, 'acm-rabbits.zip' );
		self::assertContains( 'acm-rabbits.zip', $destination );
		self::assertTrue( is_readable( $destination ) );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir
	 */
	public function test_save_blueprint_to_upload_dir_copies_directory_when_given_a_good_local_directory(): void {
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		$blueprint_folder = __DIR__ . '/test-data/blueprint-good';
		$destination      = save_blueprint_to_upload_dir( $blueprint_folder, 'blueprint-good' );
		self::assertTrue( is_readable( $destination ) );
		self::assertTrue( is_dir( $destination ) );
	}

	/**
	 * @covers ::WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir
	 */
	public function test_save_blueprint_to_upload_dir_gives_error_when_given_a_bad_local_directory(): void {
		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		$non_existent_blueprint_folder = __DIR__ . '/test-data/this-blueprint-does-not-exist';
		$destination                   = save_blueprint_to_upload_dir( $non_existent_blueprint_folder, 'this-blueprint-does-not-exist' );
		$this->assertWPError( $destination );
		self::assertSame( 'acm_blueprint_save_error', $destination->get_error_code() );
		self::assertStringStartsWith( 'Could not read directory at', $destination->get_error_message() );
	}

	/**
	 * Returns test blueprint data.
	 *
	 * @return string
	 */
	protected function get_test_blueprint_zip(): string {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		return file_get_contents( __DIR__ . '/test-data/acm-rabbits.zip' );
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
			case 'http://valid':
				return [
					'body'     => $this->get_test_blueprint_zip(),
					'response' => [
						'code' => 200,
					],
				];
			default:
				return $preempt;
		}
	}
}
