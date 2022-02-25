<?php
/**
 * Plugin
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect;

use WPE\AtlasContentModeler\ContentConnect\API\Search;
use WPE\AtlasContentModeler\ContentConnect\QueryIntegration\WPQueryIntegration;
use WPE\AtlasContentModeler\ContentConnect\Relationships\DeletedItems;
use WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost;

/**
 * Undocumented class
 */
class Plugin {

	/**
	 * Undocumented variable
	 *
	 * @var mixed
	 */
	private static $instance;

	/**
	 * URL to the Plugin
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Current plugin version
	 *
	 * @var string
	 */
	public $version;

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	public $tables = array();

	/**
	 * Undocumented variable
	 *
	 * @var Registry
	 */
	public $registry;

	/**
	 * Undocumented variable
	 *
	 * @var WPQueryIntegration
	 */
	public $wp_query_integration;

	/**
	 * Undocumented variable
	 *
	 * @var Search
	 */
	public $search;

	/**
	 * Undocumented variable
	 *
	 * @var DeletedItems
	 */
	public $deleted_items;

	/**
	 * Undocumented function
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Undocumented function
	 */
	public function __construct() {
		$this->url     = plugin_dir_url( dirname( __FILE__ ) );
		$this->version = '1.0.0';
	}

	/**
	 * Undocumented function
	 *
	 * @return Registry
	 */
	public function get_registry() {
		return $this->registry;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $table Table.
	 * @return mixed
	 */
	public function get_table( $table ) {
		if ( isset( $this->tables[ $table ] ) ) {
			return $this->tables[ $table ];
		}

		return false;
	}

	/**
	 * Undocumented function
	 */
	public function setup() {
		$this->register_tables();

		$this->registry = new Registry();
		$this->registry->setup();

		$this->wp_query_integration = new WPQueryIntegration();
		$this->wp_query_integration->setup();

		$this->search = new Search();
		$this->search->setup();

		$this->deleted_items = new DeletedItems();
		$this->deleted_items->setup();

		add_action( 'init', array( $this, 'wp_init' ), 100 );
	}

	/**
	 * Undocumented function
	 */
	public function wp_init() {
		do_action( 'acm_content_connect_init', $this->registry ); // phpcs:ignore
	}

	/**
	 * Undocumented function
	 */
	public function register_tables() {
		$this->tables['p2p'] = new PostToPost();
		$this->tables['p2p']->setup();
	}
}
