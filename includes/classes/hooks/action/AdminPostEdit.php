<?php


namespace Surrealwebs\PrimaryTaxonomy\Hooks\Action;

use Surrealwebs\PrimaryTaxonomy\Admin\Settings;
use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\get_object_public_taxonomies;
use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\get_related_primary_taxonomy_term_id;
use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\get_taxonomy_term_id_from_primary_term_id;
use WP_Taxonomy;
use WP_Term;

class AdminPostEdit {
	public function register_hooks() {
		add_action(
			'admin_enqueue_scripts',
			[ $this, 'enqueue_scripts' ],
			10,
			1
		);

		add_action( 'admin_footer', [ $this, 'include_js_templates' ] );
	}

	public function enqueue_scripts( $hook_suffix ) {
		if ( ! $this->is_post_edit( $hook_suffix ) ) {
			return;
		}

		$post_taxonomies = $this->get_post_taxonomies();

		if ( empty( $post_taxonomies ) ) {
			return;
		}

		wp_register_script(
			'surrealwebs-primary-taxonomy',
			SURREALWEBS_PRIMARY_TAXONOMY_ASSETS_URL . 'scripts/surrealwebs-primary-taxonomy.js',
			[ 'jquery' ],
			SURREALWEBS_PRIMARY_TAXONOMY_VERSION,
			true
		);
		wp_enqueue_script( 'surrealwebs-primary-taxonomy' );

		wp_localize_script(
			'surrealwebs-primary-taxonomy',
			'localizedPrimaryTaxonomyData',
			[
				'taxonomies' => array_map(
					[ $this, 'prepare_taxonomy_for_js' ],
					$post_taxonomies
				),
			]
		);
	}

	/**
	 * Include the JS template files.
	 *
	 * @return void.
	 */
	public function include_js_templates() {
		if ( ! $this->is_post_edit() ) {
			return;
		}

		require_once SURREALWEBS_PRIMARY_TAXONOMY_INC_TEMPLATES . 'admin/tpl-select-primary-term.php';
	}

	/**
	 * Returns true if the current page is post edit page.
	 *
	 * @param string $hook_suffix Page hook suffix.
	 *
	 * @return boolean
	 */
	public function is_post_edit( $hook_suffix = '' ) {
		if ( '' === $hook_suffix ) {
			global $pagenow;
			$hook_suffix = $pagenow;
		}

		return 'post-new.php' === $hook_suffix || 'post.php' === $hook_suffix;
	}

	/**
	 * Get the taxonomies for the specified post. Current post used be default.
	 *
	 * @param int $post_id The post used to find taxonomies.
	 *
	 * @return array
	 */
	public function get_post_taxonomies( $post_id = 0 ) {
		if ( empty( $post_id ) ) {
			$post_id = $this->current_post_id();
		}

		if ( empty( $post_id ) ) {
			return [];
		}

		$post_type        = get_post_type( $post_id );
		$settings_handler = new Settings(
			SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS,
			[]
		);
		$taxonomy_slugs   = $settings_handler->{ $post_type };
		if ( empty( $taxonomy_slugs ) ) {
			return [];
		}

		return array_map(
			'get_taxonomy',
			array_keys( $taxonomy_slugs )
		);
	}

	/**
	 * Get the primary term for the post specified taxonomy.
	 *
	 * @param int    $post_id  The post used in the lookup.
	 * @param string $taxonomy The limiting term.
	 *
	 * @return int The term_id of the primary term or 0 if not found.
	 */
	public function get_primary_term_for_post_taxonomy( $post_id, $taxonomy ) {
		$taxonomy_terms = wp_get_post_terms(
			$post_id,
			$taxonomy,
			[
				'fields' => 'ids',
			]
		);

		$primary_term_id = 0;
		foreach ( $taxonomy_terms as $term ) {
			$term_relation = get_related_primary_taxonomy_term_id(
				$term,
				$taxonomy,
				false
			);

			if ( ! empty( $term_relation ) ) {
				$primary_term_id = $term_relation;
				break;
			}
		}

		return $primary_term_id;
	}

	/**
	 * Process the taxonomy so it can be used in our JS.
	 *
	 * @param WP_Taxonomy $taxonomy The taxonomy being parsed.
	 *
	 * @return array The parsed taxonomy data.
	 */
	public function prepare_taxonomy_for_js( $taxonomy ) {
		// this is the ID of the "primary" term.
		$primary_term = $this->get_primary_term_for_post_taxonomy(
			$this->current_post_id(),
			$taxonomy->name
		);

		if ( ! empty( $primary_term ) ) {
			// get the id of the term related to the "primary term"
			$primary_term = get_taxonomy_term_id_from_primary_term_id( $primary_term );
		}

		$prepared_taxonomy = [
			'name'    => $taxonomy->name,
			'title'   => $taxonomy->labels->singular_name,
			'primary' => $primary_term,
			'terms'   => array_map(
				[ $this, 'prepare_terms_for_js' ],
				get_terms( $taxonomy->name )
			),
		];

		return $prepared_taxonomy;
	}

	/**
	 * @param WP_Term $term The term being prepped.
	 *
	 * @return array Prepped term data.
	 */
	public function prepare_terms_for_js( $term ) {
		return [
			'id'   => $term->term_id,
			'name' => $term->name,
		];
	}

	public function current_post_id() {
		return (int) filter_input(
			INPUT_GET,
			'post',
			FILTER_SANITIZE_NUMBER_INT
		);
	}
}
