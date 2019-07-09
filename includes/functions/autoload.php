<?php
/**
 * Autoloader and related functionality.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Functions\Autoload;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Load the files in the plugin's includes directory.
 *
 * This is a very simple "autoloader" it will recursively load files from the
 * includes directory and will exclude anything specified in the optional
 * excluded_filenames parameter.
 *
 * At it's core, this autoloader is just a way to avoid large blocks of
 * require statements. There is nothing smart about how it operates, nothing
 * is lazy loaded, etc.
 *
 * The excluded files array should be formatted like this:
 *
 * $excluded_files = [
 *      'filename1.php'                => true,
 *      '/path/to/file/to/exclude.php' => true,
 * ];
 *
 * @param array $exclude_filenames List of files to excluded keyed by file name.
 *
 * @return bool
 */
function load( $exclude_filenames = [] ) {
	if ( ! defined( 'SURREALWEBS_PRIMARY_TAXONOMY_INC' ) ) {
		return false;
	}

	$loaded = false;
	$iti    = new RecursiveDirectoryIterator( SURREALWEBS_PRIMARY_TAXONOMY_INC );
	foreach ( new RecursiveIteratorIterator( $iti ) as $file ) {
		// Skip directories right off.
		if ( is_dir( $file ) ) {
			continue;
		}

		// Check to see if the file should be excluded.
		if ( \Surrealwebs\PrimaryTaxonomy\Functions\Autoload\is_excluded_file( $file, $exclude_filenames ) ) {
			continue;
		}

		// Make sure we only load PHP files
		if ( strpos( $file, '.php' ) !== false ) {
			$loaded = true;
			require_once $file;
		}
	}

	return $loaded;
}

/**
 * Check if a file should be excluded. File is assumed excluded by default.
 *
 * The excluded files array should be formatted like this:
 *
 * $excluded_files = [
 *      'filename.php' => true,
 * ];
 *
 * @param string $file           File to check.
 * @param array  $excluded_files List of files to exclude.
 *
 * @return bool True if the file should be excluded, otherwise false.
 */
function is_excluded_file( $file, $excluded_files ) {
	$base_filename = basename( $file );

	if ( ! isset( $excluded_files[ $base_filename ] ) ) {
		return false;
	}

	if ( ! $excluded_files[ $base_filename ] ) {
		return false;
	}

	return true;
}
