<?php

use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register;

class TestGraphQLTaxonomyEndpoint extends WP_UnitTestCase {
	private $taxonomy_option = 'atlas_content_modeler_taxonomies';

	private $sample_taxonomies = array(
		'show-in-graphqls'     =>
			array(
				'slug'            => 'show-in-graphqls',
				'api_visibility'  => 'public',
				'show_in_graphql' => true,
			),
		'hide-in-graphqls'     =>
			array(
				'slug'            => 'hide-in-graphqls',
				'api_visibility'  => 'public',
				'show_in_graphql' => false,
			),
		'private-visibilities' =>
			array(
				'slug'            => 'private-visibilities',
				'api_visibility'  => 'private',
				'show_in_graphql' => true,
			),
	);

	public function set_up() {
		parent::set_up();
		update_option( $this->taxonomy_option, $this->sample_taxonomies );
		register();
	}

	public function test_taxonomies_are_visible_if_show_in_graphql_is_true() {
		$graphql = graphql(
			[
				'query' => '{
				taxonomy(idType: NAME, id: "show-in-graphqls") {
					name
				}
			}',
			]
		);

		$this->assertEquals( 'show-in-graphqls', $graphql['data']['taxonomy']['name'] );
	}

	public function test_taxonomies_are_not_visible_if_show_in_graphql_is_false() {
		$graphql = graphql(
			[
				'query' => '{
				taxonomy(idType: NAME, id: "hide-in-graphqls") {
					name
				}
			}',
			]
		);

		$this->assertEmpty( $graphql['data']['taxonomy'] );
	}

	public function test_taxonomy_is_not_visible_by_default_if_api_visibility_is_private() {
		wp_set_current_user( null );

		$graphql = graphql(
			[
				'query' => '{
				taxonomy(idType: NAME, id: "private-visibilities") {
					name
				}
			}',
			]
		);

		$this->assertEmpty( $graphql['data']['taxonomy'] );
	}

	public function test_taxonomy_is_visible_to_capable_user_if_api_visibility_is_private() {
		wp_set_current_user( 1 );

		$graphql = graphql(
			[
				'query' => '{
				taxonomy(idType: NAME, id: "private-visibilities") {
					name
				}
			}',
			]
		);

		$this->assertEquals( 'private-visibilities', $graphql['data']['taxonomy']['name'] );
	}

	public function tear_down() {
		parent::tear_down();
		wp_set_current_user( null );
		delete_option( $this->taxonomy_option );
	}
}
