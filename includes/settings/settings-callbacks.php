<?php
/**
 * Settings related callbacks.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\Settings;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\reserved_post_types;

add_action( 'admin_menu', __NAMESPACE__ . '\register_admin_menu_page' );
/**
 * Registers the wp-admin menu page.
 */
function register_admin_menu_page(): void {
	$icon = include __DIR__ . '/views/admin-menu-icon.php';
	add_menu_page(
		esc_html__( 'Content Modeler', 'atlas-content-modeler' ),
		esc_html__( 'Content Modeler', 'atlas-content-modeler' ),
		'manage_options',
		'atlas-content-modeler',
		__NAMESPACE__ . '\render_admin_menu_page',
		$icon
	);

	add_submenu_page(
		'atlas-content-modeler',
		esc_html__( 'Models', 'atlas-content-modeler' ),
		esc_html__( 'Models', 'atlas-content-modeler' ),
		'manage_options',
		'atlas-content-modeler',
		'__return_null'
	);

	add_submenu_page(
		'atlas-content-modeler',
		esc_html__( 'Taxonomies', 'atlas-content-modeler' ),
		esc_html__( 'Taxonomies', 'atlas-content-modeler' ),
		'manage_options',
		'atlas-content-modeler&amp;view=taxonomies',
		'__return_null'
	);

	add_submenu_page(
		'atlas-content-modeler',
		esc_html__( 'Tools', 'atlas-content-modeler' ),
		esc_html__( 'Tools', 'atlas-content-modeler' ),
		'manage_options',
		'atlas-content-modeler&amp;view=tools',
		'__return_null'
	);

	add_submenu_page(
		'atlas-content-modeler',
		esc_html__( 'Settings', 'atlas-content-modeler' ),
		esc_html__( 'Settings', 'atlas-content-modeler' ),
		'manage_options',
		'atlas-content-modeler&amp;view=settings',
		'__return_null'
	);
}

add_filter( 'parent_file', __NAMESPACE__ . '\maybe_override_submenu_file' );
/**
 * Overrides the “submenu file” that determines which admin submenu item gains
 * the `current` CSS class. Without this, WordPress incorrectly gives the
 * “Model” subpage the `current` class when the “Taxonomies” subpage is active.
 *
 * @link https://github.com/WordPress/WordPress/blob/9937fea517ac165ad01f67c54216469e48c48ca7/wp-admin/menu-header.php#L223-L227
 * @link https://wordpress.stackexchange.com/a/131873
 * @link https://developer.wordpress.org/reference/hooks/parent_file/
 * @param string $parent_file The original parent file.
 * @return string The $parent_file unaltered. Only the $submenu_file global is altered.
 */
function maybe_override_submenu_file( $parent_file ) {
	global $submenu_file;

	$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
	$view = filter_input( INPUT_GET, 'view', FILTER_SANITIZE_FULL_SPECIAL_CHARS );

	if ( $page === 'atlas-content-modeler' && $view === 'taxonomies' ) {
		$submenu_file = 'atlas-content-modeler&amp;view=taxonomies'; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
	}

	if ( $page === 'atlas-content-modeler' && $view === 'tools' ) {
		$submenu_file = 'atlas-content-modeler&amp;view=tools'; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
	}

	if ( $page === 'atlas-content-modeler' && $view === 'settings' ) {
		$submenu_file = 'atlas-content-modeler&amp;view=settings'; // phpcs:ignore -- global override needed to set current submenu page without JavaScript.
	}

	return $parent_file;
}

/**
 * Renders the wp-admin menu page.
 */
function render_admin_menu_page() {
	include_once __DIR__ . '/views/admin-menu-page.php';
}

/**
 * Decides if feedback banner scripts should be loaded.
 *
 * @return bool
 */
function should_show_feedback_banner(): bool {
	$time_dismissed = get_user_meta( get_current_user_id(), 'acm_hide_feedback_banner', true );

	// Check for time elapsed and presence of the meta data.
	return ! ( ! empty( $time_dismissed ) && ( $time_dismissed + WEEK_IN_SECONDS * 2 > time() ) );
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_settings_assets' );
/**
 * Registers and enqueues admin scripts and styles.
 *
 * @param string $hook The current admin page.
 */
function enqueue_settings_assets( $hook ) {
	$plugin     = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );
	$admin_path = wp_parse_url( esc_url( admin_url() ) )['path'];

	wp_register_script(
		'atlas-content-modeler-app',
		ATLAS_CONTENT_MODELER_URL . 'includes/settings/dist/index.js',
		[ 'wp-api', 'wp-api-fetch', 'react', 'react-dom', 'lodash', 'wp-i18n', 'wp-core-data' ],
		$plugin['Version'],
		true
	);

	wp_set_script_translations( 'atlas-content-modeler-app', 'atlas-content-modeler' );

	wp_localize_script(
		'atlas-content-modeler-app',
		'atlasContentModeler',
		array(
			'appPath'              => $admin_path . 'admin.php?page=atlas-content-modeler',
			'taxonomies'           => get_option( 'atlas_content_modeler_taxonomies', array() ),
			'reservedPostTypes'    => array_values( reserved_post_types() ),
			'initialState'         => get_registered_content_types(),
			'isWPGraphQLActive'    => is_plugin_active( 'wp-graphql/wp-graphql.php' ),
			'isGraphiQLAvailable'  => function_exists( 'get_graphql_setting' )
										&& get_graphql_setting( 'graphiql_enabled' ) !== 'off',
			'graphQLUrl'           => function_exists( 'get_graphql_setting' )
										? get_site_url() . '/' . get_graphql_setting( 'graphql_endpoint', 'graphql' )
										: get_site_url() . '/graphql',
			'usageTrackingEnabled' => acm_usage_tracking_enabled(),
		)
	);

	wp_register_style(
		'atlas-content-modeler-fonts',
		'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap',
		[],
		$plugin['Version']
	);

	wp_register_style(
		'atlas-content-modeler-app-styles',
		ATLAS_CONTENT_MODELER_URL . 'includes/settings/dist/index.css',
		[ 'atlas-content-modeler-fonts' ],
		$plugin['Version']
	);

	if ( 'toplevel_page_atlas-content-modeler' === $hook ) {
		wp_enqueue_script( 'atlas-content-modeler-app' );
		wp_enqueue_style( 'atlas-content-modeler-app-styles' );

		if ( should_show_feedback_banner() ) {
			wp_enqueue_script( 'atlas-content-modeler-feedback-banner' );
		}
	}
}

add_action( 'init', __NAMESPACE__ . '\register_plugin_settings' );
/**
 * Registers the plugin's settings.
 *
 * @return void
 */
function register_plugin_settings(): void {
	register_setting(
		'atlas_content_modeler_settings',
		'atlas_content_modeler_usage_tracking',
		[
			'type'              => 'string',
			'description'       => __( 'Opt into anonymous usage tracking to help us make Atlas Content Modeler better.', 'atlas-content-modeler' ),
			'sanitize_callback' => 'sanitize_text_field',
			'show_in_rest'      => true,
			'default'           => '0',
		]
	);
}

add_action( 'admin_init', __NAMESPACE__ . '\register_settings_fields' );
/**
 * Registers the plugin's settings sections and fields.
 *
 * @return void
 */
function register_settings_fields(): void {
	add_settings_section(
		'atlas_content_modeler_general_section',
		esc_html__( 'General', 'atlas-content-modeler' ),
		null, // rendered by React.
		'atlas_content_modeler_settings'
	);

	add_settings_field(
		'atlas_content_modeler_usage_tracking',
		esc_html__( 'Analytics', 'atlas-content-modeler' ),
		null, // rendered by React.
		'atlas_content_modeler_general_section'
	);
}
