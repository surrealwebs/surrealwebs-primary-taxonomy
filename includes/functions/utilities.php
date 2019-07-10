<?php

namespace Surrealwebs\PrimaryTaxonomy\Functions\Utilities;

function build_nonce_name_for_taxonomy( $taxonomy_name ) {
	return sprintf(
		'%s_%s_%s',
		SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE,
		$taxonomy_name,
		'nonce'
	);
}

function build_primary_term_field_name_for_taxonomy( $taxonomy_name ) {
	return sprintf(
		'%s_%s',
		SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE,
		$taxonomy_name
	);
}
