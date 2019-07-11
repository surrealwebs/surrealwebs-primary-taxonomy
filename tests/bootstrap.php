<?php

defined( 'DS' ) or define( 'DS', DIRECTORY_SEPARATOR );

define( 'SURREALWEBS_PRIMARY_TAXONOMY_VERSION', '0.1.0' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_CACHE_GROUP', 'surrealwebs_ptax' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_URL', '/wp-content/plugins/surrealwebs-primary-taxonomy' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_PATH', realpath( __DIR__ . '/../' ) . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_TEST_DIR', __DIR__ );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_ASSETS_URL', SURREALWEBS_PRIMARY_TAXONOMY_URL . 'assets/' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_ASSETS_DIR', SURREALWEBS_PRIMARY_TAXONOMY_PATH . 'assets' . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_VENDOR', SURREALWEBS_PRIMARY_TAXONOMY_PATH . 'vendor' . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC', SURREALWEBS_PRIMARY_TAXONOMY_PATH . 'includes' . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC_CLASSES', SURREALWEBS_PRIMARY_TAXONOMY_INC . 'classes' . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC_FUNCTIONS', SURREALWEBS_PRIMARY_TAXONOMY_INC . 'functions' . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_INC_TEMPLATES', SURREALWEBS_PRIMARY_TAXONOMY_INC . 'templates' . DS );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS', 'sw_primary_taxonomies' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_SCREEN_OPTIONS_PER_PAGE_DEFAULT', 20 );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_FIELD_NAME_BASE', 'sw_primary_term' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_POST_TAXONOMY_NAME', 'swprimary' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_RELATED_TAXMETA_KEY', 'primary_taxonomy_term_id' );
define( 'SURREALWEBS_PRIMARY_TAXONOMY_ORIGINAL_TAXMETA_KEY', 'original_term_id' );

require_once SURREALWEBS_PRIMARY_TAXONOMY_VENDOR . 'antecedent' . DS . 'patchwork' . DS . 'Patchwork.php';

ini_set( 'display_errors', false );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', realpath( __DIR__ . '/test.log' ) );

if ( ! file_exists( SURREALWEBS_PRIMARY_TAXONOMY_VENDOR . 'autoload.php' ) ) {
	throw new PHPUnit_Framework_Exception(
		'ERROR' . PHP_EOL . PHP_EOL .
		'You must use Composer to install the test suite\'s dependencies!' . PHP_EOL
	);
}

require_once SURREALWEBS_PRIMARY_TAXONOMY_VENDOR . 'autoload.php';
require_once SURREALWEBS_PRIMARY_TAXONOMY_INC_FUNCTIONS . 'autoload.php';

$sw_autoload_exclude_filenames = [
	'index.php'    => true,
	'autoload.php' => true,
];

$sw_autoload_exclude_directories = [
	SURREALWEBS_PRIMARY_TAXONOMY_INC_TEMPLATES,
];

\Surrealwebs\PrimaryTaxonomy\Functions\Autoload\load(
	$sw_autoload_exclude_filenames,
	$sw_autoload_exclude_directories
);

WP_Mock::setUsePatchwork( true );
WP_Mock::bootstrap();
// WP_Mock::tearDown();
