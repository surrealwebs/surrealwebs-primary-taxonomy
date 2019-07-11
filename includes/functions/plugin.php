<?php
/**
 * These functions are used to manage the plugin.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Functions\Plugin;

use function add_action;
use function add_filter;
use function get_option;
use function get_post_type_object;
use function get_post_types;
use Surrealwebs\PrimaryTaxonomy\Admin\MenuPage;
use Surrealwebs\PrimaryTaxonomy\Admin\PageDetails;
use Surrealwebs\PrimaryTaxonomy\Admin\PageRenderer;
use Surrealwebs\PrimaryTaxonomy\Admin\ScreenOptions;
use Surrealwebs\PrimaryTaxonomy\Admin\Settings;
use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\get_public_taxonomies_for_object_list;
use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\maybe_purge_cache;
use Surrealwebs\PrimaryTaxonomy\Hooks\Action\AdminPostEdit;
use Surrealwebs\PrimaryTaxonomy\Hooks\Action\PostSave;
use Surrealwebs\PrimaryTaxonomy\Hooks\Filter\TaxonomySearch;
use Surrealwebs\PrimaryTaxonomy\Taxonomies\Primary;
use WP_Error;
use WP_Taxonomy;

function activate() {

}

function deactivate() {

}

/**
 * Bootstrap the plugin, setup all initial hooks.
 *
 * @see https://github.com/10up/plugin-scaffold/
 *
 * @action init
 * @action admin_init
 * @action admin_menu
 *
 * @filter posts_join
 * @filter posts_where
 * @filter posts_groupby
 *
 * @return void
 */
function bootstrap() {
	// Lifted from the 10up plugin scaffold.
	// This makes calling namespaced functions easier.
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'init', $n( 'init' ) );
	add_action( 'init', $n( 'init_late' ), 999 );
	add_action( 'admin_menu', $n( 'admin_menu' ) );
	add_action( 'admin_init', $n( 'admin_init' ) );

	$tax_search = new TaxonomySearch(
		SURREALWEBS_PRIMARY_TAXONOMY_POST_TAXONOMY_NAME
	);
	add_filter( 'posts_join', [ $tax_search, 'posts_join' ], 10, 2 );
	add_filter( 'posts_where', [ $tax_search, 'posts_where' ], 10, 2 );
	add_filter( 'posts_groupby', [ $tax_search, 'posts_groupby' ], 10, 2 );
}

/**
 * Used to initialize the plugin, called during the init hook process.
 *
 * @return void
 */
function init() {
	$admin_post_edit = new AdminPostEdit();
	$admin_post_edit->register_hooks();
}

/**
 * Callback that runs late in the init process.
 *
 * Use this method when you need something run on init but after other init
 * hooks are called.
 *
 * @hook init
 *
 * @return void
 */
function init_late() {
	$post_types              = get_post_types();
	$primary_taxonomy_config = build_default_taxonomy_args_array(
		__( 'Primary Taxonomy', 'surrealwebs-primary-taxonomy' ),
		__( 'Primary Taxonomies', 'surrealwebs-primary-taxonomy' )
	);

	$primary_taxonomy_config['hierarchical']      = false;
	$primary_taxonomy_config['public']            = false;
	$primary_taxonomy_config['show_ui']           = false;
	$primary_taxonomy_config['show_admin_column'] = false;
	$primary_taxonomy_config['show_in_nav_menus'] = false;
	$primary_taxonomy_config['show_tagcloud']     = false;

	$primary_taxonomy = new Primary(
		$primary_taxonomy_config,
		$post_types,
		SURREALWEBS_PRIMARY_TAXONOMY_POST_TAXONOMY_NAME
	);
	$primary_taxonomy->register();


	$post_save = new PostSave();
	add_action( 'save_post', [ $post_save, 'set_primary_taxonomies' ], 10, 3 );

	maybe_purge_cache( true );
}

/**
 * Used to setup admin menus/pages.
 *
 * @return void
 */
function admin_menu() {
	$head = [];
	$body = [
		'intro'   => [
			__( 'Primary taxonomies are configured on a per post-type, per taxonomy basis. Any taxonomy can be configured to allow a primary taxonomy to be specified.', 'surrealwebs-primary-taxonomy' ),
			__( 'No changes need to be made to taxonomy configurations to work with this plugin.', 'surrealwebs-primary-taxonomy' ),
		],
		'outro'   => '',
		'content' => null,
	];
	$foot = [];

	$page_details     = new PageDetails( $head, $body, $foot );
	$page_renderer    = new PageRenderer(
		$page_details,
		get_option(
			SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS,
			[]
		)
	);
	$screen_options   = new ScreenOptions( [] );
	$settings_handler = new Settings(
		SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS,
		[]
	);

	$settings_handler->set_fields( get_admin_settings_page_fields() );

	$admin_menu_instance = new MenuPage(
		SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS,
		$page_renderer,
		$screen_options,
		$settings_handler
	);

	$admin_menu_instance->add_menu_items();
}

/**
 * Used to initialize anything needed within the admin side of WP.
 *
 * @return void
 */
function admin_init() {

}

/**
 * Used to load up scripts, preferably as a callback method.
 *
 * @return void
 */
function enqueue_scripts() {

}

/**
 * Used to load styles, preferably as a callback method.
 *
 * @return void
 */
function enqueue_styles() {

}

/**
 * Gets a name for the specified object.
 *
 * @param mixed $object That which shall be named.
 * @param string $property_name Property to used if $object is an object.
 *
 * @return string The name of the object.
 */
function get_object_name( $object, $property_name = 'post_type' ) {
	if ( is_string( $object ) ) {
		return $object;
	}

	if ( is_object( $object ) && property_exists( $object, $property_name ) ) {
		return $object->{$property_name};
	}

	return md5( serialize( $object ) );
}

/**
 * Get the field configurations for use on the admin settings page.
 *
 * @return array Field configurations.
 */
function get_admin_settings_page_fields() {
	$out = [
		'sections' => [
			[
				'id'    => 'primary_taxonomies',
				'title' => __(
					'Primary Taxonomies',
					'surrealwebs-primary-taxonomy'
				),
			],
		],
		'fields'   => [],
	];

	$public_taxonomies = get_public_taxonomies_for_object_list(
		get_post_types(),
		'object'
	);

	foreach ( $public_taxonomies as $post_type => $taxonomies ) {
		$options = [];
		/** @var WP_Taxonomy $taxonomy */
		foreach ( $taxonomies as $taxonomy ) {
			$options[ $taxonomy->name ] = [
				'id'    => $taxonomy->name,
				'label' => $taxonomy->labels->singular_name,
			];
		}

		$post_type = get_post_type_object( $post_type );
		$out['fields'][ $post_type->name ] = [
			'key'         => $post_type->name,
			'name'        => [
				'template' => '%s[%s]',
				'base'     => SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS,
				'extra'    => [
					$post_type->name
				],
			],
			'title'       => $post_type->labels->singular_name,
			'description' => '',
			'type'        => 'checkbox',
			'section'     => 'primary_taxonomies',
			'options'     => $options,
		];
	}

	return $out;
}

/**
 * Build a list of default configurations for a custom taxonomy.
 *
 * @param string $single Taxonomy singular name.
 * @param string $plural Taxonomy plural name.
 *
 * @return array The configured arguments.
 */
function build_default_taxonomy_args_array( $single, $plural ) {
	return [
		'labels'            => [
			'name'                       => $plural,
			'singular_name'              => $single,
			'menu_name'                  => $single,
			'all_items'                  => __( 'All Items', 'surrealwebs_custom_post_types' ),
			'parent_item'                => __( 'Parent Item', 'surrealwebs_custom_post_types' ),
			'parent_item_colon'          => __( 'Parent Item:', 'surrealwebs_custom_post_types' ),
			'new_item_name'              => __( 'New Item Name', 'surrealwebs_custom_post_types' ),
			'add_new_item'               => __( 'Add New Item', 'surrealwebs_custom_post_types' ),
			'edit_item'                  => __( 'Edit Item', 'surrealwebs_custom_post_types' ),
			'update_item'                => __( 'Update Item', 'surrealwebs_custom_post_types' ),
			'view_item'                  => __( 'View Item', 'surrealwebs_custom_post_types' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'surrealwebs_custom_post_types' ),
			'add_or_remove_items'        => __( 'Add or remove items', 'surrealwebs_custom_post_types' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'surrealwebs_custom_post_types' ),
			'popular_items'              => __( 'Popular Items', 'surrealwebs_custom_post_types' ),
			'search_items'               => __( 'Search Items', 'surrealwebs_custom_post_types' ),
			'not_found'                  => __( 'Not Found', 'surrealwebs_custom_post_types' ),
			'no_terms'                   => __( 'No items', 'surrealwebs_custom_post_types' ),
			'items_list'                 => sprintf( __( '%s list', 'surrealwebs_custom_post_types' ), $single ),
			'items_list_navigation'      => sprintf( __( '%s list navigation', 'surrealwebs_custom_post_types' ), $plural ),
		],
		'hierarchical'      => false,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_nav_menus' => true,
		'show_tagcloud'     => true,
	];
}
