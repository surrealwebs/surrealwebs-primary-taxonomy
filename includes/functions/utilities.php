<?php
/**
 * Some utility methods for our plugin.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Functions\Utilities;

/**
 * Build a nonce key for the specified taxonomy.
 *
 * @param string $taxonomy_name The taxonomy name.
 *
 * @return string The nonce key.
 */
function build_nonce_name_for_taxonomy( $taxonomy_name ) {
	return sprintf(
		'%s_%s_%s',
		SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE,
		$taxonomy_name,
		'nonce'
	);
}

/**
 * Get the primary field name for the specified taxonomy.
 *
 * This is the field name for use in forms and for saving data. This is used
 * in several places so it made sense to put the logic in a function for
 * consistency.
 *
 * @param string $taxonomy_name The taxonomy name.
 *
 * @return string The field name.
 */
function build_primary_term_field_name_for_taxonomy( $taxonomy_name ) {
	return sprintf(
		'%s_%s',
		SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE,
		$taxonomy_name
	);
}
