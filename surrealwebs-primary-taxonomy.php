<?php
/**
 * Plugin Name: SurrealwebsPrimaryTaxonomy
 * Plugin URI:  https://github.com/surrealwebs/surrealwebs-primary-taxonomy
 * Description: Enables the ability to specify a primary taxonomy term for a post or CPT.
 * Version:     0.1.0
 * Author:      arichard <surrealwebs@gmail.com
 * Author URI:  http://adamhasawebsite.com
 * Text Domain: surrealwebs-primary-taxonomy
 * Domain Path: /languages
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

// Common constants for the plugin
define( 'SURREALWEBS_PRIMARY_TAXONOMY_VERSION', '0.1.0' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP', 'surrealwebs_ptax' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_URL', plugin_dir_url( __FILE__ ) );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_PATH', plugin_dir_path( __FILE__ ) );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_VENDOR', SURREALWEBS_PRIMARY_TAXONOMY_PATH . 'vendor/' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC', SURREALWEBS_PRIMARY_TAXONOMY_PATH . 'includes/' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC_CLASSES', SURREALWEBS_PRIMARY_TAXONOMY_INC . 'classes/' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC_FUNCTIONS', SURREALWEBS_PRIMARY_TAXONOMY_INC . 'functions/' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS', 'sw_primary_taxonomies' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_SCREEN_OPTIONS_PER_PAGE_DEFAULT', 20 );


// Our plugin autoloader
require_once SURREALWEBS_PRIMARY_TAXONOMY_INC_FUNCTIONS . 'autoload.php';

$sw_autoload_exclude_filenames = [
	'index.php'    => true,
	'autoload.php' => true,
];

\Surrealwebs\PrimaryTaxonomy\Functions\Autoload\load( $sw_autoload_exclude_filenames );

// Our Vendor autoloader
if (
	file_exists( SURREALWEBS_PRIMARY_TAXONOMY_VENDOR . 'autoload.php' )
	&& is_readable( SURREALWEBS_PRIMARY_TAXONOMY_VENDOR . 'autoload.php' )
) {
	require_once SURREALWEBS_PRIMARY_TAXONOMY_VENDOR . 'autoload.php';
}

// Plugin Management Hooks
register_activation_hook( __FILE__, 'Surrealwebs\PrimaryTaxonomy\Functions\Plugin\activate' );
register_deactivation_hook( __FILE__, 'Surrealwebs\PrimaryTaxonomy\Functions\Plugin\deactivate' );

// Kick things off
Surrealwebs\PrimaryTaxonomy\Functions\Plugin\bootstrap();
