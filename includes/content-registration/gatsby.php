<?php
/**
 * Register content for Gatsby.
 *
 * So that the WPGatsby plugin invalidates the Gatsby data cache when
 * ACM entries are created, deleted or modified.
 *
 * @link https://wordpress.org/plugins/wp-gatsby/
 * @link https://github.com/gatsbyjs/gatsby/blob/master/packages/gatsby-source-wordpress/docs/getting-started.md#quick-start
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\ContentRegistration\Gatsby;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

add_filter( 'gatsby_action_monitor_tracked_post_types', __NAMESPACE__ . '\monitor_acm_post_types' );
/**
 * Extends post types WPGatsby monitors for changes to include all ACM models
 * where api_visibility is 'public'.
 *
 * Without this, Gatsby developers have to stop and start their server to see
 * new, updated, or deleted ACM posts reflected in GraphQL responses.
 *
 * With this, they can click “Refresh Data” on the Gatsby GraphiQL page at
 * http://localhost:8000/___graphql during development to update data, or do
 * `curl -X POST http://localhost:8000/__refresh`.
 *
 * The `ENABLE_GATSBY_REFRESH_ENDPOINT=true` environment variable is required
 * in `.env.development` before “Refresh Data” or the `/__refresh` endpoint are
 * available. See:
 * https://www.gatsbyjs.com/docs/how-to/local-development/environment-variables/
 *
 * @param array $original_post_types Current list of monitored post types.
 * @return array New list of monitored post types with ACM types added.
 */
function monitor_acm_post_types( array $original_post_types ): array {
	$acm_post_types = array_keys(
		array_filter(
			get_registered_content_types(),
			function( $acm_post_type ) {
				return ( $acm_post_type['api_visibility'] ?? 'private' ) === 'public';
			}
		)
	);

	$acm_post_types = array_combine( $acm_post_types, $acm_post_types ); // So that keys match values, as in the array returned from get_post_types().

	return array_merge( $original_post_types, $acm_post_types );
}
