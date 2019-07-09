<?php
/**
 * This object is used to handle operations with Screen Options
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Admin;

/**
 * Class ScreenOptions
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class ScreenOptions {
	/**
	 * Flag indicating an Option Type of Integer.
	 *
	 * @since 0.1.0
	 * @var int OPTION_TYPE_INT
	 */
	const OPTION_TYPE_INT = 1;

	/**
	 * Flag indicating an Option Type of String.
	 *
	 * @since 0.1.0
	 * @var int OPTION_TYPE_STRING
	 */
	const OPTION_TYPE_STRING = 2;

	/**
	 * Flag indicating an Option Type of Boolean.
	 *
	 * @since 0.1.0
	 * @var int OPTION_TYPE_BOOL
	 */
	const OPTION_TYPE_BOOL = 4;

	/**
	 * The currently configured options the class is handling.
	 *
	 * @since 0.1.0
	 * @var array $options
	 */
	protected $options;

	/**
	 * ScreenOptions constructor.
	 *
	 * @param array $options List of options to have the class handle.
	 */
	public function __construct( $options ) {
		$this->options = $options;
	}

	/**
	 * Use this to add an option to the list of option handled by the class.
	 *
	 * If the specified screen option name is already in use it will not be
	 * added to avoid duplication. If you wish to replace the option pass in
	 * the optional force_override param.
	 *
	 * @param string $name The name of the option being added.
	 * @param string $label Label for this option, should be translated.
	 * @param mixed $default The default value used for this option.
	 * @param string $option_name The name to use when storing the data.
	 * @param int $type On of the class OPTION_TYPE_... constants.
	 * @param bool $force_override Optional. Flag indicating if existing
	 *                             option should be overridden (true) or
	 *                             not (false, default).
	 *
	 * @return bool True if the option was added otherwise false.
	 */
	public function add_screen_option(
		$name,
		$label,
		$default,
		$option_name,
		$type = self::OPTION_TYPE_STRING,
		$force_override = false
	) {
		if ( ! $force_override && isset( $this->options[ $name ] ) ) {
			return false;
		}

		$this->options[ $name ] = [
			'label'   => $label,
			'default' => $default,
			'option'  => $option_name,
			'type'    => $type,
		];

		return true;
	}

	/**
	 * Set the entire list of options controlled by this class.
	 *
	 * @param array $options_list {
	 *     Optional. The list of options to store.
	 *
	 *     Keyed by the option name, this is a list of the option configs
	 *     used to filter user originating option values and add the option
	 *     to the screen options tab when the time comes.
	 *
	 *     @type string "*" {
	 *         The name of the option as the key.
	 *
	 *         @type string "label"      Label of the option.
	 *         @type string "option"     The name used to store the option.
	 *         @type mixed  "default"    Default value of the option.
	 *         @type int    "type"       The type data stored by this option.
	 *                                   Should be one of the OPTION_TYPE...
	 *     }
	 * }
	 * @return void
	 */
	public function set_screen_options( $options_list = [] ) {
		$this->options = $options_list;
	}

	/**
	 * Add the screen options to the page.
	 *
	 * @return void
	 * @uses add_screen_option()
	 *
	 */
	public function add_screen_options_to_pages() {
		foreach ( $this->options as $name => $config ) {
			add_screen_option( $name, $this->strip_unwanted_data_from_option_args( $config ) );
		}
	}

	/**
	 * Save the screen option. This is a callback used to filter user data.
	 *
	 * @param mixed $status Status passed from the "set-screen-option" filter.
	 * @param string $option The name of the option being processed.
	 * @param mixed $value The value entered by the user.
	 *
	 * @return mixed Formatted value if we can process it otherwise $status.
	 */
	public function save_screen_option( $status, $option, $value ) {
		// If we are not handling this option exit early.
		if ( ! isset( $this->options[ $option ] ) ) {
			return $status;
		}

		// Use the option's config to determine how to process it's data.
		switch ( $this->options[ $option ]['type'] ) {
			case self::OPTION_TYPE_INT:
				$output_value = absint( $value );
				break;
			case self::OPTION_TYPE_STRING:
				$output_value = wp_strip_all_tags( $value );
				break;
			case self::OPTION_TYPE_BOOL:
				$output_value = boolval( $value );
				break;
			default:
				$output_value = $status;
		}

		return $output_value;
	}

	/**
	 * Remove any unwanted keys from the option configuration.
	 *
	 * By default all values except the for "option", "default", and "label"
	 * are stripped out. You can pass the key names for any additional values
	 * you would like to keep.
	 *
	 * @param array $config       The configuration of the option to process.
	 * @param array $args_to_keep Optional. List of key names to keep.
	 *
	 * @return mixed
	 */
	protected function strip_unwanted_data_from_option_args(
		$config,
		$args_to_keep = []
	) {
		$preserve_args = [
			'label',
			'option',
			'default',
		];

		// Merge any additional args with the main list.
		if ( ! empty( $args_to_keep ) ) {
			$preserve_args = array_merge( $preserve_args, $args_to_keep );
		}

		// Get the keys of the data we need to remove.
		$remove_keys = array_diff( array_keys( $config ), $preserve_args );

		array_map(
			function ( $key ) use ( &$config ) {
				unset( $config[ $key ] );
			},
			$remove_keys
		);

		return $config;
	}
}
