<?php


namespace Surrealwebs\PrimaryTaxonomy\Hooks\Action;


use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\get_related_primary_taxonomy_term_id;
use function Surrealwebs\PrimaryTaxonomy\Functions\Utilities\build_nonce_name_for_taxonomy;
use function Surrealwebs\PrimaryTaxonomy\Functions\Utilities\build_primary_term_field_name_for_taxonomy;
use WP_Post;

class PostSave {
	/**
	 * Set the primary category for the post if any are selected.
	 *
	 * @action save_post
	 *
	 * @param int     $post_id The post ID
	 * @param WP_Post $post    The saved post.
	 * @param bool    $update  True if updated existing post, otherwise false.
	 *
	 */
	public function set_primary_taxonomies( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$taxonomies = $this->get_supported_taxonomies_for_post_type(
			$post->post_type
		);

		if ( empty( $taxonomies ) ) {
			return;
		}

		$current_terms = wp_get_post_terms(
			$post_id,
			$taxonomies,
			[
				'fields' => 'ids',
			]
		);

		// If there are no current terms for this post there is no primary.
		if ( empty( $current_terms ) ) {
			return;
		}

		remove_action( 'save_post', [ $this, 'set_primary_taxonomies' ], 10 );
		$this->save_primary_terms( $post_id, $taxonomies, $current_terms );
		add_action( 'save_post', [ $this, 'set_primary_taxonomies' ], 10, 3 );
	}

	public function save_primary_terms( $post_id, $taxonomy_names, $current_terms ) {

		$primary_terms = [];
		foreach ( $taxonomy_names as $taxonomy_name ) {
			$term_id = $this->get_selected_primary_term(
				$taxonomy_name,
				$current_terms
			);

			$primary_terms[] = get_related_primary_taxonomy_term_id(
				$term_id,
				$taxonomy_name
			);
		}

		// Set the post's primary terms. If $primary_terms is empty then the
		// post will have no primary term.
		wp_set_object_terms(
			$post_id,
			$primary_terms,
			SURREALWEBS_PRIMARY_TAXONOMY_POST_TAXONOMY_NAME,
			false
		);

		return true;
	}

	/**
	 * Get the term the user selected as primary for the taxonomy.
	 *
	 * @param string $taxonomy_name The taxonomy being processed.
	 * @param array  $current_terms The post's current terms in the taxonomy.
	 *
	 * @return int The term_id of the selected primary term.
	 */
	public function get_selected_primary_term(
		$taxonomy_name,
		$current_terms
	) {

		// Are you even supposed to be here today?
		check_admin_referer(
			'sw_primary_tax_save',
			build_nonce_name_for_taxonomy( $taxonomy_name )
		);

		$primary_term_field_name = build_primary_term_field_name_for_taxonomy(
			$taxonomy_name
		);

		if ( ! isset( $_POST[ $primary_term_field_name ] ) ) {
			return 0;
		}

		$term = absint( $_POST[ $primary_term_field_name ] );

		if ( ! in_array( $term, $current_terms ) ) {
			return 0;
		}

		return $term;
	}

	/**
	 * Get the taxonomies configured to allow a primary term.
	 *
	 * @param string $post_type The post type to look up.
	 *
	 * @return array The taxonomies that support primary terms for post_type.
	 */
	public function get_supported_taxonomies_for_post_type( $post_type ) {
		// Get the list of taxonomies that allow primaries
		$primary_taxonomies = get_option(
			SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS
		);

		if ( empty( $primary_taxonomies ) ) {
			return [];
		}

		// Is this post in our list, if not, bail out.
		if ( ! isset( $primary_taxonomies[ $post_type ] ) ) {
			return [];
		}

		// If the post type doesn't have any primaries bail out.
		if ( empty( $primary_taxonomies[ $post_type ] ) ) {
			return [];
		}

		/*
		 * Check if the list is a single item, happens if using radios
		 * instead of checkboxes when declaring which taxonomies can
		 * support a "primary" term.
		 */
		if ( ! is_array( $primary_taxonomies[ $post_type ] ) ) {
			return $primary_taxonomies[ $post_type ];
		}

		return array_keys( $primary_taxonomies[ $post_type ] );
	}
}
