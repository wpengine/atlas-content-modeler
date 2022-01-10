<?php
/**
 * Base Table
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\Tables;

/**
 * Undocumented class
 */
abstract class BaseTable {

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	public $columns = array();

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	public $keys = array();

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	public $primary_key_name = null;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	public $unique_key_name = null;

	/**
	 * Undocumented variable
	 *
	 * @var boolean
	 */
	public $did_schema = false;

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	public $bulk_updater = null;

	/**
	 * Undocumented variable
	 *
	 * @var integer
	 */
	public $inserted = 0;

	/**
	 * Undocumented variable
	 *
	 * @var integer
	 */
	public $updated = 0;

	/**
	 * Undocumented variable
	 *
	 * @var integer
	 */
	public $deleted = 0;

	/**
	 * Undocumented function
	 */
	public function setup() {
		add_action( 'init', [ $this, 'upgrade' ] );
	}

	/**
	 * Undocumented function
	 *
	 * @return string Version string for table x.x.x
	 */
	abstract public function get_schema_version();

	/**
	 * Undocumented function
	 *
	 * @return string SQL statement to create the table
	 */
	abstract public function get_schema();

	/**
	 * Undocumented function
	 *
	 * @return string table name of the table we're creating
	 */
	abstract public function get_table_name();

	/**
	 * Undocumented function
	 *
	 * @param string $table_name Table Name.
	 *
	 * @return string
	 */
	public function generate_table_name( $table_name ) {
		$db     = $this->get_db();
		$prefix = $db->prefix;

		return $prefix . $table_name;
	}

	/**
	 * Undocumented function
	 *
	 * @return string|bool
	 */
	public function get_installed_schema_version() {
		return get_option( $this->get_schema_option_name() );
	}

	/**
	 * Undocumented function
	 *
	 * @return string
	 */
	public function get_schema_option_name() {
		return $this->get_table_name() . '_schema_version';
	}

	/**
	 * Undocumented function
	 *
	 * @return boolean
	 */
	public function should_upgrade() {
		return version_compare(
			$this->get_schema_version(),
			$this->get_installed_schema_version(),
			'>'
		);
	}

	/**
	 * Undocumented function
	 *
	 * @param boolean $fresh Fresh.
	 * @return boolean
	 */
	public function upgrade( $fresh = false ) {
		if ( $this->should_upgrade() || $fresh ) {
			$sql = $this->get_schema();

			require_once ABSPATH . '/wp-admin/includes/upgrade.php';
			dbDelta( $sql );

			update_option(
				$this->get_schema_option_name(),
				$this->get_schema_version(),
				'no'
			);

			return true;
		} else {
			return false;
		}
	}

	/**
	 * Undocumented function
	 *
	 * @return WPDB
	 */
	public function get_db() {
		global $wpdb;

		return $wpdb;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $data Data.
	 * @param array $format Format.
	 */
	public function replace( $data, $format = array() ) {
		$db = $this->get_db();

		$db->replace( $this->get_table_name(), $data, $format );
	}


	/**
	 * Bulk replaces records in the database
	 *
	 *       INSERT into `table` (id,fruit)
	 *          VALUES (1,'apple'), (2,'orange'), (3,'peach')
	 *          ON DUPLICATE KEY UPDATE fruit = VALUES(fruit);
	 *
	 * $columns = array(
	 *      'col1' => '%s',
	 *      'col2' => '%d',
	 * );
	 *
	 * $rows = array(
	 *      array(
	 *          'col1' => 'string',
	 *          'col2' => 1
	 *      ),
	 *      array(
	 *          'col1' => 'another string',
	 *          'col2' => 2
	 *      ),
	 * );
	 *
	 * @param array $columns Columns.
	 * @param array $rows Rows.
	 *
	 * @return mixed
	 */
	public function replace_bulk( $columns, $rows ) {
		$db             = $this->get_db();
		$table_name     = esc_sql( $this->get_table_name() );
		$column_names   = $this->get_column_names_query( $columns, $rows );
		$column_updates = $this->get_column_updates_query( $columns );
		$values         = $this->get_values_query( $columns, $rows );

		$query = <<<SQL
			INSERT INTO `{$table_name}` {$column_names}
				VALUES {$values}
				ON DUPLICATE KEY UPDATE {$column_updates};
SQL;

		return $db->query( $query );
	}

	/**
	 * Undocumented function
	 *
	 * @param string $where Where.
	 * @param string $where_format Where Format.
	 */
	public function delete( $where, $where_format = null ) {
		$db = $this->get_db();

		$db->delete( $this->get_table_name(), $where, $where_format );
	}

	/**
	 * Undocumented function
	 *
	 * @param array $columns Columns.
	 * @param array $rows Rows.
	 */
	public function get_column_names_query( &$columns, $rows ) {
		$row = $rows[0];

		foreach ( $columns as $column => $format ) {
			if ( ! array_key_exists( $column, (array) $row ) ) {
				unset( $columns[ $column ] );
			}
		}

		$column_names = array_keys( $columns );
		$column_names = array_map(
			function( $value ) {
				return "`{$value}`";
			},
			$column_names
		);
		return '( ' . implode( ',', array_map( 'esc_sql', $column_names ) ) . ' )';
	}

	/**
	 * Undocumented function
	 *
	 * @param array $columns Columns.
	 */
	public function get_column_updates_query( &$columns ) {
		$updates = '';

		foreach ( $columns as $column_name => $column_format ) {
			$column_name = esc_sql( $column_name );
			$updates    .= "`{$column_name}` = VALUES(`$column_name`)";
			$updates    .= ',';
		}

		$updates = rtrim( $updates, ',' );

		return $updates;
	}

	/**
	 * Undocumented function
	 *
	 * @param array $columns Columns.
	 * @param array $rows Rows.
	 *
	 * @return string
	 */
	public function get_values_query( $columns, $rows ) {
		$types = array_values( $columns );

		$values = array();

		foreach ( $rows as $data ) {
			/*
			 * $types is an array of values such as %d and %s, used in vsprintf to make sure data is correct format.
			 * Values are escaped for SQL via the array_map( 'esc_sql', $data ) in the same line
			 */
			$values[] = "\n\t('" . vsprintf( implode( "', '", $types ), array_map( 'esc_sql', $data ) ) . "')";
		}

		return implode( ',', $values );
	}
}
