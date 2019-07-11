<?php
/**
 * Custom taxonomy, the guts of the plugin.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Taxonomies;

use function register_taxonomy;

/**
 * Class Primary works with the custom taxonomy used to track primary terms.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class Primary {
	/** @var array $taxonomy_args Taxonomy configurations. */
	protected $taxonomy_args;

	/** @var array $post_types List of supported post types. */
	protected $post_types;

	/** @var string $name The internal name used for the taxonomy. */
	protected $name;

	/** @var string $default_name The default name to use for the taxonomy. */
	protected $default_name = 'swprimary';

	/**
	 * Primary constructor.
	 *
	 * @param array $taxonomy_args Taxonomy configurations.
	 * @param array $post_types List of supported post types.
	 * @param null $name Optional. Custom name to use for the taxonomy.
	 */
	public function __construct( $taxonomy_args, $post_types, $name = null ) {
		$this->taxonomy_args = $taxonomy_args;
		$this->post_types    = $post_types;
		$this->name          = $name ?? $this->default_name;
	}

	/**
	 * Registers the taxonomy.
	 *
	 * @return void
	 */
	public function register() {
		register_taxonomy(
			$this->name,
			$this->post_types,
			$this->taxonomy_args
		);
	}
}
