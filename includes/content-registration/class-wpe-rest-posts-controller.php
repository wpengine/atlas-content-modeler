<?php
/**
 * REST API: Custom REST Posts Controller class
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);
namespace WPE\AtlasContentModeler\ContentRegistration;

/**
 * Class REST_Posts_Controller
 */
final class REST_Posts_Controller extends \WP_REST_Posts_Controller {
	/**
	 * Checks if a post can be read.
	 *
	 * This is different than the class it extends because it
	 * does not automatically make published posts available
	 * to unauthenticated requests. It checks the API Visibility
	 * setting of the post type and if it's private it requires
	 * authenticating as a user with authorization to read_post.
	 *
	 * @param \WP_Post $post Post object.
	 *
	 * @return bool
	 */
	public function check_read_permission( $post ): bool {
		$models    = get_registered_content_types();
		$post_type = get_post_type_object( $post->post_type );
		if ( array_key_exists( $this->post_type, $models ) && isset( $models[ $this->post_type ]['api_visibility'] ) && 'private' === $models[ $this->post_type ]['api_visibility'] ) {
			return current_user_can( $post_type->cap->read_post, $post->ID );
		}
		return parent::check_read_permission( $post );
	}
}
