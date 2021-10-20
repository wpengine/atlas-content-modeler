<?php
/**
 * Relationship
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\Relationships;

/**
 * Undocumented class
 */
abstract class Relationship {

	/**
	 * Relationship Name. Used to enable multiple relationships between the same combinations of objects.
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Unique ID string for the relationship
	 *
	 * Used for IDs in the DOM and other places we need a unique ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Should the from UI for this relationship be enabled
	 *
	 * @var bool
	 */
	public $enable_from_ui;

	/**
	 * Should the to UI for this relationship be enabled
	 *
	 * @var bool
	 */
	public $enable_to_ui;

	/**
	 * Various labels used for from UI
	 *
	 * @var Array
	 */
	public $from_labels;

	/**
	 * Various labels used for to UI
	 *
	 * @var Array
	 */
	public $to_labels;

	/**
	 * Is the "from" UI for this sortable
	 *
	 * @var bool
	 */
	public $from_sortable;

	/**
	 * Is the "to" UI for this sortable
	 *
	 * @var bool
	 */
	public $to_sortable;

	/**
	 * Is this a two way relationship?
	 *
	 * @var bool
	 */
	public $is_bidirectional;

	/**
	 * The relationship cardinality, such as "many-to-many".
	 *
	 * @var string
	 */
	public $cardinality;

	/**
	 * Undocumented function
	 *
	 * @param string $name Name.
	 * @param array  $args Args.
	 */
	public function __construct( $name, $args = array() ) {
		$this->name = $name;

		$defaults = array(
			'is_bidirectional' => true,
			'cardinality'      => 'many-to-many',
			'from'             => array(
				'enable_ui' => true,
				'sortable'  => false,
				'labels'    => array(
					'name' => $name,
				),
			),
			'to'               => array(
				'enable_ui' => false,
				'sortable'  => false,
				'labels'    => array(
					'name' => $name,
				),
			),
		);

		$args = array_replace_recursive( $defaults, $args );

		$this->is_bidirectional = $args['is_bidirectional'];
		$this->cardinality      = $args['cardinality'];

		$this->enable_from_ui = $args['from']['enable_ui'];
		$this->from_sortable  = $args['from']['sortable'];
		$this->from_labels    = $args['from']['labels'];

		$this->enable_to_ui = $args['to']['enable_ui'];
		$this->to_sortable  = $args['to']['sortable'];
		$this->to_labels    = $args['to']['labels'];
	}

	/**
	 * Undocumented function
	 */
	abstract public function setup();

}
