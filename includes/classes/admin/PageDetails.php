<?php


namespace Surrealwebs\PrimaryTaxonomy\Admin;


class PageDetails {

	protected $head;

	protected $foot;

	protected $body;

	public function __construct( $head, $body, $foot ) {
		$this->head = $head;
		$this->body = $body;
		$this->foot = $foot;
	}

	/**
	 * @return mixed
	 */
	public function get_head() {
		return $this->head;
	}

	/**
	 * @param mixed $head
	 */
	public function set_head( $head ) {
		$this->head = $head;
	}

	/**
	 * @return mixed
	 */
	public function get_foot() {
		return $this->foot;
	}

	/**
	 * @param mixed $foot
	 */
	public function set_foot( $foot ) {
		$this->foot = $foot;
	}

	/**
	 * @return mixed
	 */
	public function get_body() {
		return $this->body;
	}

	/**
	 * @param mixed $body
	 */
	public function set_body( $body ) {
		$this->body = $body;
	}


}
