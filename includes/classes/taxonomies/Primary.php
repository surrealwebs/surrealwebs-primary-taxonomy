<?php


namespace Surrealwebs\PrimaryTaxonomy\Taxonomies;


class Primary {
	protected $taxonomy_args;

	protected $post_types;

	protected $name;

	protected $default_name = 'swprimary';

	public function __construct( $taxonomy_args, $post_types, $name = null ) {
		$this->taxonomy_args = $taxonomy_args;
		$this->post_types    = $post_types;
		$this->name          = $name ?? $this->default_name;
	}

	public function register() {
		register_taxonomy( $this->name, $this->post_types, $this->taxonomy_args );
	}

}
