<?php

$_tests_dir = getenv( 'WP_TESTS_DIR' );

if ( ! $_tests_dir ) {
	$_tests_dir = '/tmp/wordpress-tests-lib';
}

require_once $_tests_dir . '/includes/functions.php';

function _manually_load_plugin() {
	require_once dirname( __FILE__ ) . '/../../../includes/content-connect/autoload.php';
	acm_content_connect_autoloader();

	$test_classes = array(
		'ContentConnectTestCase.php',
	);

	foreach ( $test_classes as $file ) {
		require __DIR__ . '/' . $file;
	}

	// Kick things off.
	\WPE\AtlasContentModeler\ContentConnect\Plugin::instance();
}
tests_add_filter( 'muplugins_loaded', '_manually_load_plugin' );

require $_tests_dir . '/includes/bootstrap.php';

define( 'PHPUNIT_RUNNER', true );
