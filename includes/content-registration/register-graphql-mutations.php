<?php
/**
 * Adds fields to existing GraphQL post type mutations.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\RegisterGraphQLMutations;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\map_html_field_type_to_graphql_field_type;
use function WPE\AtlasContentModeler\ContentRegistration\is_repeatable_field;

add_action( 'graphql_register_types', __namespace__ . '\register_acm_fields_as_mutation_inputs' );
/**
 * Registers ACM fields as inputs for Create and Update mutations.
 *
 * WPGraphQL registers create[ModelSingularName] and update[ModelSingularName]
 * mutations for all post types, but does not register ACM fields as mutation
 * input fields by default.
 *
 * This function extends the default mutation input fields so that
 * `createRabbit` and `updateRabbit` requests can send ACM field data,
 * such as 'name' and 'favoriteFood' in this example:
 *
 * ```
 * mutation CREATE_RABBIT {
 *  createRabbit(input: {
 *    clientMutationId: "CreateKillerBunny"
 *    status: PUBLISH
 *    name: "Killer Bunny"
 *    favoriteFood: "Knights of the Round Table"
 *  }) {
 *    rabbit {
 *      id
 *      title
 *      date
 *    }
 *  }
 * }
 * ```
 */
function register_acm_fields_as_mutation_inputs(): void {
	$models = get_registered_content_types();

	foreach ( $models as $model ) {
		$model_graphql_name = ucfirst( get_post_type_object( $model['slug'] )->graphql_single_name );
		$fields             = $model['fields'] ?? [];

		foreach ( $fields as $field ) {
			$field_type           = $field['type'] ?? '';
			$graphql_type         = map_html_field_type_to_graphql_field_type( $field_type );
			$is_unsupported_field = $field_type === 'media'
									|| $field_type === 'relationship'
									|| is_null( $graphql_type );

			if ( $is_unsupported_field ) {
				continue;
			}

			if ( is_repeatable_field( $field ) ) {
				$graphql_type = [ 'list_of' => $graphql_type ];
			}

			// TODO: how to enforce required fields? (look at use of 'non_null' in WPGraphQL, test it with 'list_of' types).
			$args = [
				'type'        => $graphql_type,
				'description' => $field['description'] ?? '',

				'resolve'     => function( \WP_Post $post ) use ( $field ) {
					// TODO: combine and extract this logic with that from register_content_fields_with_graphql().
					$value = get_post_meta( $post->ID, $field['slug'], true );

					if ( $field['type'] === 'number' ) {
						return (float) $value;
					}

					if ( $field['type'] === 'multipleChoice' && empty( $value ) ) {
						return [];
					}

					if ( $field['type'] === 'richtext' ) {
						if ( is_repeatable_field( $field ) ) {
							return array_map( 'do_shortcode', $value );
						}

						return do_shortcode( $value );
					}

					return $value;
				},
			];

			register_graphql_field( "Create{$model_graphql_name}Input", $field['slug'], $args );
			register_graphql_field( "Update{$model_graphql_name}Input", $field['slug'], $args );
		}
	}
}

add_action( 'graphql_post_object_mutation_update_additional_data', __namespace__ . '\update_acm_fields_during_mutations', 10, 3 );
/**
 * Saves provided ACM field data during WPGraphQL Create or Update mutations.
 *
 * This runs once per mutation, not once per field.
 *
 * @param int          $post_id The ID of the post being mutated.
 * @param array        $input Post data provided in the request.
 * @param WP_Post_Type $post_type_object Post type object for the post being mutated.
 */
function update_acm_fields_during_mutations( int $post_id, array $input, $post_type_object ): void {
	$models = get_registered_content_types();
	$model  = $models[ $post_type_object->name ] ?? false;

	if ( ! $model ) {
		return;
	}

	$fields = $model['fields'] ?? [];

	foreach ( $fields as $field ) {
		$field_value = $input[ $field['slug'] ] ?? false;
		if ( $field_value ) {
			// TODO: sanitize and transform values before saving here.
			update_post_meta( $post_id, $field['slug'], $field_value );
		}
	}
}
