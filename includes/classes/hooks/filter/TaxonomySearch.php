<?php
/**
 * Filter callback operator for adding taxonomy to searches.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Hooks\Filter;

use function esc_sql;
use WP_Query;

/**
 * Class TaxonomySearch filters used to modify the search query.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class TaxonomySearch {

	/**
	 * @var string $taxonomy_name The name of the taxonomy to search.
	 */
	protected $taxonomy_name;

	/**
	 * TaxonomySearch constructor.
	 *
	 * @param string $taxonomy_name Name of the taxonomy to search.
	 */
	public function __construct( $taxonomy_name ) {
		$this->taxonomy_name = $taxonomy_name;
	}

	/**
	 * Filters the "join" portion of the query to add taxonomy support.
	 *
	 * @filter posts_join
	 *
	 * @param string $join The current "join" string.
	 * @param WP_Query $query The current query being processed.
	 *
	 * @return string The updated join.
	 */
	public function posts_join( $join, $query ) {
		global $wpdb;

		if ( ! $this->is_correct_query( $query ) ) {
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

	/**
	 * Filters the "where" portion of the query to add taxonomy support.
	 *
	 * @filter posts_where
	 *
	 * @param string $where The current "where" string.
	 * @param WP_Query $query The current query being processed.
	 *
	 * @return string The updated where.
	 */
	public function posts_where( $where, $query ) {
		global $wpdb;

		if ( ! $this->is_correct_query( $query ) ) {
			return $where;
		}

		if ( empty( $this->taxonomy_name ) ) {
			return $where;
		}

		$where .= sprintf(
			" OR ( `%s`.taxonomy = '%s' AND `%s`.name LIKE '%%%s%%' ) ",
			$wpdb->term_taxonomy,
			esc_sql( $this->taxonomy_name ),
			$wpdb->terms,
			esc_sql( $query->get( 's' ) )
		);

		return $where;
	}

	/**
	 * Filters the "group by" portion of the query to add taxonomy support.
	 *
	 * @filter posts_groupby
	 *
	 * @param string $groupby The current "groupby" string.
	 * @param WP_Query $query The current query being processed.
	 *
	 * @return string The updated grouping.
	 */
	public function posts_groupby( $groupby, $query ) {
		global $wpdb;

		if ( ! $this->is_correct_query( $query ) ) {
			return $groupby;
		}

		return " `{$wpdb->posts}`.ID ";
	}

	/**
	 * Check to make sure we only modify the query when needed.
	 *
	 * @param WP_Query $query The current query being processed.
	 *
	 * @return bool True if we need to modify the query, otherwise false.
	 */
	protected function is_correct_query( $query ) {
		if ( ! $query->is_search() ) {
			return false;
		}

		if ( $query->is_admin ) {
			return false;
		}

		if ( ! $query->is_main_query() ) {
			return false;
		}

		return true;
	}
}
