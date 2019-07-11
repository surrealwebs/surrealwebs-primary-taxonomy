<?php
/**
 * Settings manager used to manage data stored in the WordPress options table.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Admin;

use function add_settings_field;
use function add_settings_section;
use function esc_html_e;
use function get_option;
use function register_setting;
use function sanitize_text_field;
use function Surrealwebs\PrimaryTaxonomy\Functions\Taxonomy\get_object_public_taxonomies;
use function update_option;
use WP_Error;

/**
 * Class Settings. Manages settings in the WordPress options table.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class Settings {

	/** @var array $settings Loaded settings. */
	protected $settings;

	/** @var string $option_name The name of the option in the database. */
	protected $option_name;

	/** @var array $default_settings Default values for the option. */
	protected $default_settings;

	/** @var PageRenderer $page_renderer Object used to build a page. */
	protected $page_renderer;

	/** @var array $fields Set of field groups and configurations. */
	protected $fields;

	/**
	 * Settings constructor. Loads settings for the specified option.
	 *
	 * @param string $option_name      The name of the option to load.
	 * @param array  $default_settings Default settings.
	 */
	public function __construct( $option_name, $default_settings = [] ) {
		$this->option_name      = $option_name;
		$this->default_settings = $default_settings;
		$this->load();
	}

	/**
	 * Forces the data to be loaded from WP options table.
	 *
	 * You can specify which option to load if you wish to reuse this object
	 * if you do not specify the option or the default settings the values
	 * currently configured in the object instance will be used instead.
	 *
	 * @param string $option_name      Optional. The name of the option to load.
	 * @param array  $default_settings Optional. Default settings.
	 *
	 * @return void
	 */
	public function load( $option_name = '', $default_settings = [] ) {
		$option_name      = $option_name ?: $this->option_name;
		$default_settings = $default_settings ?: $this->default_settings;
		$this->settings   = get_option( $option_name, $default_settings );
	}

	/**
	 * Registers the settings fields for the admin page.
	 *
	 * @return bool|WP_Error True on success or WP_Error if there was problem.
	 */
	public function register_settings_page_fields() {
		if ( empty( $this->fields ) ) {
			return new WP_Error(
				'UNDEFINED_FIELD_LIST',
				'No fields to register'
			);
		}

		register_setting(
			$this->option_name . '-group',
			$this->option_name, [
				$this,
				'sanitize_settings',
			]
		);

		if ( isset( $this->fields['sections'] ) ) {
			array_map( [ $this, 'register_section', ], $this->fields['sections'] );
		}

		if ( isset( $this->fields['fields'] ) ) {
			array_map( [ $this, 'register_field' ], $this->fields['fields'] );
		}

		return true;
	}

	/**
	 * Register the specified settings section.
	 *
	 * @param array $section Configuration used to create the section.
	 *
	 * @return void
	 */
	public function register_section( $section ) {
		$callback = $this->get_settings_section_description_callback( $section['id'] );

		add_settings_section(
			$section['id'],
			$section['title'],
			[ $this, $callback ],
			SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS . '_options'
		);
	}

	/**
	 * Register a settings field so it appears on the settings page.
	 *
	 * @param array $field Desired field configuration.
	 *
	 * @return void
	 */
	public function register_field( $field ) {
		add_settings_field(
			$field['key'],
			$field['title'],
			[
				$this->page_renderer,
				$this->page_renderer->get_callback_name_from_type( $field['type'] ),
			],
			SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS . '_options',
			$field['section'],
			$field
		);
	}

	/**
	 * Get the name of the callback method used to render a section description.
	 *
	 * @param string $section_id The ID/slug of the section.
	 *
	 * @return string The callback method name.
	 */
	public function get_settings_section_description_callback( $section_id ) {
		$section_callbacks = [
			'primary_taxonomies' => 'get_translated_primary_taxonomies_description',
			'default'            => 'get_empty_description',
		];

		if ( isset( $section_callbacks[ $section_id ] ) ) {
			return $section_callbacks[ $section_id ];
		}

		return $section_callbacks['default'];
	}

	/**
	 * Display the translated Primary Taxonomies section description.
	 *
	 * @return void
	 */
	public function get_translated_primary_taxonomies_description() {
		esc_html_e(
			'Primary Taxonomy configurations by post type.',
			'surrealwebs-primary-taxonomy'
		);
	}

	/**
	 * Callback method used when a section has no description, does nothing.
	 *
	 * @return bool False in all instances.
	 */
	public function get_empty_description() {
		return false;
	}

	/**
	 * The the option name
	 *
	 * @param string $option_name The option name.
	 *
	 * @return void
	 */
	public function set_option_name( $option_name ) {
		$this->option_name = $option_name;
	}

	/**
	 * Get the option name.
	 *
	 * @return string The option name.
	 */
	public function get_option_name() {
		return $this->option_name;
	}

	/**
	 * The the object used to render the settings page.
	 *
	 * @param PageRenderer $page_renderer The renderer for the settings page.
	 *
	 * @return void
	 */
	public function set_page_renderer( $page_renderer ) {
		$this->page_renderer = $page_renderer;
	}

	/**
	 * Get the settings page renderer.
	 *
	 * @return PageRenderer The renderer for the settings page.
	 */
	public function get_page_renderer() {
		return $this->page_renderer;
	}

	/**
	 * Set the settings page fields.
	 *
	 * @param array $fields List of settings page fields.
	 */
	public function set_fields( $fields ) {
		$this->fields = $fields;
	}

	/**
	 * Get the configured fields for the settings page.
	 *
	 * @return array List of fields.
	 */
	public function get_fields() {
		return $this->fields;
	}

	/**
	 * Sanitization callback for settings/option page.
	 *
	 * @param array $input Submitted settings values.
	 *
	 * @return array Sanitized setting values.
	 */
	public function sanitize_settings( $input ) {
		$options = [];

		// loop through settings fields to control what we're saving
		foreach ( $this->fields['fields'] as $key => $field ) {
			$single = ( 'radio' === $field['type'] );
			if ( isset( $input[ $key ] ) && ! empty( $input[ $key ] ) ) {
				$options[ $key ] = $this->sanitize_taxonomies(
					$key,
					$input[ $key ],
					$single
				);
			} else {
				$options[ $key ] = $single ? '' : [];
			}
		}

		return $options;
	}

	/**
	 * Sanitize taxonomies, validates against known taxonomies for post type.
	 *
	 * @param string $post_type     The post type of taxonomy being processed.
	 * @param array  $taxonomy_list List of taxonomies to process.
	 * @param bool   $single        Used to indicate only a single taxonomy is expected.
	 *
	 * @return array List of validated and sanitized taxonomies.
	 */
	public function sanitize_taxonomies( $post_type, $taxonomy_list, $single = false ) {
		$real_taxonomies = get_object_public_taxonomies( $post_type, 'names' );
		$sanitized       = [];

		/*
		 * Check if a real taxonomy is in the list, if so add it to the returned
		 * taxonomy list, this will keep any invalid taxonomies out of our list
		 * even on accident.
		 */
		foreach ( $real_taxonomies as $taxonomy ) {
			if ( $single && $taxonomy === $taxonomy_list ) {
				$sanitized = $taxonomy;
				break;
			}

			if ( isset( $taxonomy_list[ $taxonomy ] ) ) {
				$clean_taxonomy               = sanitize_text_field( $taxonomy );
				$sanitized[ $clean_taxonomy ] = $clean_taxonomy;
			}
		}

		return $sanitized;
	}

	/**
	 * Magic getter to retrieve values.
	 *
	 * @param string $key The name of the value to retrieve.
	 *
	 * @return mixed The item's value if found.
	 */
	public function __get( $key ) {
		if ( isset( $this->settings[ $key ] ) ) {
			return $this->settings[ $key ];
		}
	}

	/**
	 * Magic setter to store value.
	 *
	 * @param string $key   Name of the value to store.
	 * @param mixed  $value The value being stored.
	 *
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->settings[ $key ] = $value;
	}

	/**
	 * Magic method used to test if a value has been set.
	 *
	 * @param string $key The value being tested.
	 *
	 * @return bool True if set otherwise false.
	 */
	public function __isset( $key ) {
		return isset( $this->settings[ $key ] );
	}

	/**
	 * Magic method used to unset a value.
	 *
	 * @param string $key The name of the value to unset.
	 *
	 * @return void
	 */
	public function __unset( $key ) {
		unset( $this->settings[ $key ] );
	}

	/**
	 * Get the settings stored in the object.
	 *
	 * @return array The stored settings.
	 */
	public function get_settings() {
		return $this->settings;
	}

	/**
	 * Get the default settings stored in the object.
	 *
	 * @return array The stored default settings.
	 */
	public function get_default_settings() {
		return $this->default_settings;
	}

	/**
	 * Saves the settings to the options table.
	 *
	 * @return void
	 */
	public function save() {
		update_option( $this->option_name, $this->settings );
	}
}
