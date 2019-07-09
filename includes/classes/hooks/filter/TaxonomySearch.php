<?php


namespace Surrealwebs\PrimaryTaxonomy\Hooks\Filter;


class TaxonomySearch {

	protected $option_name;

	protected $taxonomies;

	public function __construct( $option_name ) {
		$this->option_name = $option_name;

		$this->load_taxonomies();
	}

	public function load_taxonomies() {
		$this->taxonomies = [];

		$option = get_option( $this->option_name, [] );

		if ( empty( $option ) ) {
			return false;
		}

		$all_taxonomies = [];
		foreach ( maybe_unserialize( $option ) as $post => $taxonomies ) {
			if ( empty( $taxonomies ) ) {
				continue;
			}

			$all_taxonomies = array_merge( $all_taxonomies, array_keys( $taxonomies ) );
		}

		$this->taxonomies = $all_taxonomies ?: [];
	}

	public function posts_join( $join, $query ) {
		global $wpdb;

		if ( ! $this->is_correct_query() ) {
			return $join;
		}

		$join .= sprintf(
			" LEFT JOIN ( `%1\$s` 
				INNER JOIN `%2\$s` ON `%2\$s`.term_taxonomy_id = `%1\$s`.term_taxonomy_id 
				INNER JOIN `%3\$s` ON `%3\$s`.term_id = `%2\$s`.term_id 
				) ON `%4\$s`.ID = `%1\$s`.object_id ",
			$wpdb->term_relationships,
			$wpdb->term_taxonomy,
			$wpdb->terms,
			$wpdb->posts
		);

		return $join;
	}

	public function posts_where( $where, $query ) {
		global $wpdb;

		if ( ! $this->is_correct_query() || empty( $this->taxonomies ) ) {
			return $where;
		}

		$taxonomies = implode( ', ', $this->prep_taxonomies() );
		if ( empty( $taxonomies ) ) {
			return $where;
		}

		$where .= sprintf(
			" OR ( `%s`.taxonomy IN ( %s ) AND `%s`.name LIKE '%%%s%%' ) ",
			$wpdb->term_taxonomy,
			$taxonomies,
			$wpdb->terms,
			esc_sql( $query->get( 's' ) )
		);

		return $where;
	}

	public function posts_groupby( $groupby, $query ) {
		global $wpdb;

		if ( ! $this->is_correct_query() || empty( $this->taxonomies ) ) {
			return $groupby;
		}

		return " `{$wpdb->posts}`.ID ";
	}

	protected function is_correct_query() {
		if ( ! is_search () ) {
			return false;
		}

		if ( is_admin() ) {
			return false;
		}

		if ( ! is_main_query() ) {
			return false;
		}

		return true;
	}

	public function prep_taxonomies() {

		return array_map(
			function( $tax ) {
				return sprintf( "'%s'", $tax );
			},
			$this->taxonomies
		);
	}
}
