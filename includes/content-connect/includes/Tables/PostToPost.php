<?php
/**
 * Post to Post
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\Tables;

/**
 * Undocumented class
 */
class PostToPost extends BaseTable {

	/**
	 * Undocumented function
	 */
	public function get_schema_version() {
		return '0.1.0';
	}

	/**
	 * Undocumented function
	 */
	public function get_table_name() {
		return $this->generate_table_name( 'acm_post_to_post' );
	}

	/**
	 * Defines the acm_post_to_post table schema.
	 *
	 * Indexes:
	 *  id1_id2_name - Used to ensure no duplicates are created
	 *  id2_name - Used for WP_Query "related_to_post" and get_related_object_ids() WITHOUT order by relationship
	 *  id2_name_order - Used for WP_Query "related_to_post" and get_related_object_ids() WITH order by relationship
	 *
	 * @return string
	 */
	public function get_schema() {
		$table_name = $this->get_table_name();

		$sql = "CREATE TABLE `{$table_name}` (
			`id1` bigint(20) unsigned NOT NULL,
			`id2` bigint(20) unsigned NOT NULL,
			`name` varchar(64) NOT NULL,
			`order` int(11) NOT NULL default 0,
			UNIQUE KEY id1_id2_name (`id1`,`id2`,`name`),
			KEY id2_name (`id2`,`name`),
			KEY id2_name_order (`id2`,`name`,`order`)
		);";

		return $sql;
	}

}
