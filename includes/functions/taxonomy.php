<?php

namespace Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy;

use function Surrealwebs\PrimaryTaxonomy\Functions\Plugin\get_object_name;

function get_object_public_taxonomies(
	$object,
	$output = 'names',
	$force_reload = false
) {
	$object    = get_object_name( $object );
	$cache_key = build_cache_key_for_post_type_public_taxonomies( $object, $output );

	$out_taxonomies = [];

	if ( ! $force_reload ) {
		$out_taxonomies = wp_cache_get(
			$cache_key,
			SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP,
			false,
			$found
		);

		if ( $found ) {
			return maybe_unserialize( $out_taxonomies );
		}
	}

	$all_taxonomies = get_object_taxonomies( $object, 'object' );

	/** @var \WP_Taxonomy $taxonomy */
	foreach ( $all_taxonomies as $taxonomy ) {
		if ( ! $taxonomy->public ) {
			continue;
		}

		$out_taxonomies[] = (
			'names' === $output
			? $taxonomy->name
			: $taxonomy
		);
	}

	wp_cache_set(
		$cache_key,
		maybe_serialize( $out_taxonomies ),
		SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP,
		DAY_IN_SECONDS
	);

	return $out_taxonomies;
}

function get_public_taxonomies_for_object_list( $objects, $output = 'names' ) {
	$out = [];

	foreach ( $objects as $object ) {
		$taxes = get_object_public_taxonomies( $object, $output, true );
		if ( empty( $taxes ) ) {
			continue;
		}
		$out[ get_object_name( $object ) ] = $taxes;
	}

	return $out;
}

function maybe_purge_cache( $warm_cache = false ) {
	// If nothing changed we have nothing to purge.
	if ( ! known_post_types_changed() && ! known_taxonomies_changed() ) {
		return false;
	}

	$cached_post_types = wp_cache_get(
		get_known_post_types_cache_key(),
		SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP,
		false,
		$found
	);

	// If we didn't find any thing we have nothing cached.
	if ( ! $found ) {
		return false;
	}

	$cached_post_types = maybe_unserialize( $cached_post_types );

	// Merge post types we know about with the current list of post types
	// so we don't leave anything behind when purging the cache.
	$post_types = array_merge( $cached_post_types, get_post_types() );

	foreach ( $post_types as $post_type ) {
		wp_cache_delete(
			build_cache_key_for_post_type_public_taxonomies( $post_type ),
			SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP
		);
	}

	if ( $warm_cache ) {
		warm_known_object_caches();
	}

	return true;
}

function known_post_types_changed() {
	$cached_post_types = wp_cache_get(
		get_known_post_types_cache_key(),
		SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP,
		false,
		$found
	);

	$post_types = get_post_types();

	if ( $found ) {
		$cached_post_types = maybe_unserialize( $cached_post_types );
	}

	sort( $post_types );

	if ( empty( array_diff( $post_types, (array) $cached_post_types ) ) ) {
		return false;
	}

	return true;
}

function known_taxonomies_changed() {
	$cached_taxonomies = wp_cache_get(
		get_known_taxonomies_cache_key(),
		SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP,
		false,
		$found
	);

	$taxonomies = get_taxonomies();

	if ( $found ) {
		$cached_taxonomies = maybe_unserialize( $taxonomies );
	}

	sort( $taxonomies );

	if ( empty( array_diff( $taxonomies, (array) $cached_taxonomies ) ) ) {
		return false;
	}

	return true;
}

function warm_known_post_cache() {
	warm_cache_for( get_known_post_types_cache_key(), get_post_types() );
}

function warm_known_taxonomy_cache() {
	warm_cache_for( get_known_taxonomies_cache_key(), get_taxonomies() );
}

function warm_known_object_caches() {
	warm_known_post_cache();
	warm_known_taxonomy_cache();
}

function warm_cache_for( $key, $data, $expire = 0 ) {
	wp_cache_set(
		$key,
		maybe_serialize( $data ),
		SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP,
		$expire
	);
}

function build_cache_key_for_post_type_public_taxonomies(
	$post_type,
	$format = 'object'
) {
	return sprintf(
		'sw_public_taxonomies_%s_%s',
		$post_type,
		$format
	);
}

function build_known_object_cache_key( $object ) {
	return sprintf(
		'sw_known_%s',
		$object
	);
}

function get_known_post_types_cache_key() {
	return build_known_object_cache_key( 'post_types' );
}

function get_known_taxonomies_cache_key() {
	return build_known_object_cache_key( 'taxonomies' );
}

function get_related_primary_taxonomy_term_id(
	$term_id,
	$taxonomy_name,
	$create_missing = true
) {
	$primary_term_id = get_term_meta(
		$term_id,
		SURREALWEBS_PRIMARY_TAXONOMY_RELATED_TAXMETA_KEY,
		true
	);

	if ( $create_missing && empty( $primary_term_id ) ) {
		// add this term
		$primary_term_id = copy_term_to_taxonomy(
			$term_id,
			$taxonomy_name,
			SURREALWEBS_PRIMARY_TAXONOMY_POST_TAXONOMY_NAME,
			true
		);
	}

	return $primary_term_id;
}

function copy_term_to_taxonomy(
	$original_term_id,
	$original_taxonomy,
	$new_taxonomy,
	$relate_terms = false
) {
	$term            = get_term( $original_term_id, $original_taxonomy );
	$term_data       = [
		'description' => $term->description,
		'slug'        => $term->slug,
	];

	// check to see if the "primary" taxonomy has this term
	$primary_term_id       = 0;
	$primary_existing_term = get_term_by( 'slug', $term->slug, $new_taxonomy );
	if ( ! empty( $primary_existing_term ) ) {
		$primary_term_id = $primary_existing_term->term_id;
	}

	if ( empty( $primary_term_id ) ) {
		$primary_term_data = wp_insert_term(
			$term->name,
			$new_taxonomy,
			$term_data
		);

		if (
			is_wp_error( $primary_term_data )
			|| ! isset( $primary_term_data['term_id'] )
			|| empty( $primary_term_data['term_id'] )
		) {
			return 0;
		}

		$primary_term_id = $primary_term_data['term_id'];
	}

	if ( $relate_terms ) {
		relate_term_with_primary_term(
			$original_term_id,
			$primary_term_id,
			$original_taxonomy
		);
	}

	return $primary_term_id;
}

function get_taxonomy_term_id_from_primary_term_id( $primary_term_id ) {
	$term_id = get_term_meta(
		$primary_term_id,
		SURREALWEBS_PRIMARY_TAXONOMY_ORIGINAL_TAXMETA_KEY,
		true
	);

	if ( ! empty( $term_id ) ) {
		return absint( $term_id );
	}

	return 0;
}

function relate_term_with_primary_term(
	$term_id,
	$primary_term_id,
	$taxonomy
) {
	// First, clean up the old relationships.
	remove_term_meta_for_primary_term_relationships( $taxonomy );

	// Related the term to the primary, this is setup as a two way relationship.
	add_term_meta(
		$term_id,
		SURREALWEBS_PRIMARY_TAXONOMY_RELATED_TAXMETA_KEY,
		$primary_term_id
	);

	add_term_meta(
		$primary_term_id,
		SURREALWEBS_PRIMARY_TAXONOMY_ORIGINAL_TAXMETA_KEY,
		$term_id
	);
}

function remove_term_meta_for_primary_term_relationships( $taxonomy ) {
	$terms = get_terms(
		[
			'taxonomy' => $taxonomy,
			'fields'   => 'ids',
		]
	);

	if ( empty( $terms ) ) {
		return;
	}

	foreach ( $terms as $term_id ) {
		$primary_term_id = get_term_meta(
			$term_id,
			SURREALWEBS_PRIMARY_TAXONOMY_RELATED_TAXMETA_KEY
		);

		// Real term.
		delete_term_meta(
			$term_id,
			SURREALWEBS_PRIMARY_TAXONOMY_RELATED_TAXMETA_KEY
		);

		// Primary term.
		delete_term_meta(
			$primary_term_id,
			SURREALWEBS_PRIMARY_TAXONOMY_ORIGINAL_TAXMETA_KEY
		);
	}
}
