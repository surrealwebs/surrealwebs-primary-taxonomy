<?php
/**
 * Page details class.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Admin;

/**
 * Class PageDetails is used to hold page components.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class PageDetails {
	/** @var array $head List of header content. */
	protected $head;

	/** @var array $foot List of footer content. */
	protected $foot;

	/** @var array $body List of body content. */
	protected $body;

	/**
	 * PageDetails constructor.
	 *
	 * @param array $head Header content.
	 * @param array $body Body content.
	 * @param array $foot Footer content.
	 */
	public function __construct( $head, $body, $foot ) {
		$this->head = $head;
		$this->body = $body;
		$this->foot = $foot;
	}

	/**
	 * Get the header content.
	 *
	 * @return array Header content.
	 */
	public function get_head() {
		return $this->head;
	}

	/**
	 * Set the header content.
	 *
	 * @param array $head List of header content.
	 *
	 * @return void
	 */
	public function set_head( $head ) {
		$this->head = $head;
	}

	/**
	 * Get footer content.
	 *
	 * @return array List of footer content.
	 */
	public function get_foot() {
		return $this->foot;
	}

	/**
	 * Set the footer content.
	 *
	 * @param array $foot List of footer content.
	 *
	 * @return void
	 */
	public function set_foot( $foot ) {
		$this->foot = $foot;
	}

	/**
	 * Get the body content.
	 *
	 * @return array List of body content.
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * Set the body content
	 *
	 * @param array $body List of body content.
	 *
	 * @return void
	 */
	public function set_body( $body ) {
		$this->body = $body;
	}
}
