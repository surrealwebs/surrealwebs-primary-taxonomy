<?php
/**
 * Page Renderer class
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Admin;

/**
 * Class PageRenderer used to render settings pages.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class PageRenderer {
	/** @var array $setting_values List of current setting values. */
	protected $setting_values;

	/** @var PageDetails $page_details List of page details. */
	protected $page_details;

	/**
	 * PageRenderer constructor.
	 *
	 * @param PageDetails $page_details   List of page details.
	 * @param array       $setting_values List of current settings values.
	 */
	public function __construct( $page_details, $setting_values ) {
		$this->page_details   = $page_details;
		$this->setting_values = $setting_values;
	}

	/**
	 * Build the settings page.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @param Settings $settings Settings object used to build the page.
	 *
	 * @return void
	 */
	public function main_settings_page( $settings ) {
		$this->header();

		$body = $this->page_details->get_body();
		if ( ! empty( $body['intro'] ) ) {
			$this->add_content_block( $body['intro'] );
		}
		?>

		<form method="post" action="options.php">
			<?php
			settings_fields( $settings->get_option_name() . '-group' );
			do_settings_sections( SURREALWEBS_PRIMARY_TAXONOMY_ADMIN_SETTINGS . '_options' );
			submit_button();
			?>
		</form>

		<?php
		$this->footer();
	}

	/**
	 * Adds the specified content to the page.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @param array $content_array List of content to display.
	 *
	 * @return void
	 */
	public function add_content_block( $content_array ) {
		if ( empty( $content_array ) ) {
			return;
		}
		printf(
			'<div><p>%s</p></div>',
			wp_kses_post(
				implode(
					'</p><p>',
					(array) $content_array
				)
			)
		);
	}

	/**
	 * Output the page header.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @return void
	 */
	protected function header() {
		?>
		<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php
	}

	/**
	 * Output the page footer.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @return void
	 */
	protected function footer() {
		?>
		</div>
		<?php
	}

	/**
	 * Get the name of the method to use to render the a field.
	 *
	 * @param string $type The field type.
	 *
	 * @return string The name of the callback method.
	 */
	public function get_callback_name_from_type( $type ) {
		$valid_types = [
			'checkbox',
			'radio',
			'text',
		];

		if ( ! in_array( $type, $valid_types, true ) ) {
			$type = 'text';
		}

		return sprintf( 'render_%s', $type );
	}

	/**
	 * Render a checkbox based on the field settings.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @param array $field Field configuration settings.
	 *
	 * @return void
	 */
	public function render_checkbox( $field ) {
		$template = '<label for="%s"><input type="checkbox" name="%s" value="%s" id="%s" %s />%s</label><br/>';

		$current_value = $this->get_current_value_for_field( $field, [] );

		foreach ( $field['options'] as $option ) {
			$option_tag_id = sprintf( '%s_%s', $field['key'], $option['id'] );
			printf(
				$template,
				esc_attr( $option_tag_id ),
				esc_attr( $this->create_option_name_for_field_option_name( $field, $option['id'] ) ),
				esc_attr( $option['id'] ),
				esc_attr( $option_tag_id ),
				checked( true, in_array( $option['id'], (array) $current_value, true ), false ),
				esc_html( $option['label'] )
			);
		}
	}

	/**
	 * Render a radio button based on the field settings.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @param array $field Field configuration settings.
	 *
	 * @return void
	 */
	public function render_radio( $field ) {
		$template = '<label for="%s"><input type="radio" name="%s" value="%s" id="%s" %s /> %s</label><br/>';

		$current_value = $this->get_current_value_for_field( $field, '' );
		foreach ( $field['options'] as $option ) {
			$option_tag_id = sprintf( '%s_%s', $field['key'], $option['id'] );
			printf(
				$template,
				esc_attr( $option_tag_id ),
				esc_attr( $this->create_option_name_for_field_option_name( $field, '' ) ),
				esc_attr( $option['id'] ),
				esc_attr( $option_tag_id ),
				checked( $current_value, $option['id'], false ),
				esc_html( $option['label'] )
			);
		}
	}

	/**
	 * Render a radio button based on the field settings.
	 *
	 * NOTE: This method will output content directly.
	 *
	 * @param array $field Field configuration settings.
	 *
	 * @return void
	 */
	public function render_text( $field ) {
		echo esc_html( __( 'Not Used', 'surrealwebs-primary-taxonomy' ) );
	}

	/**
	 * Get the field name of the form element based on field settings.
	 *
	 * @param array $field The field configuration used to build the name.
	 *
	 * @return string The field name.
	 */
	public function extract_field_name_from_field( $field ) {
		if ( empty( $field['name'] ) ) {
			return '';
		}

		if ( isset( $field['name']['base'] ) ) {
			return $field['name']['base'];
		}

		return '';
	}

	/**
	 * Create a the field name to used for option/checkbox/radio elements.
	 *
	 * @param array  $field       Field settings.
	 * @param string $option_name The name of the option.
	 *
	 * @return string The field name to use for the option.
	 */
	public function create_option_name_for_field_option_name( $field, $option_name ) {
		if ( empty( $field['name'] ) ) {
			return '';
		}

		$base = $this->extract_field_name_from_field( $field );
		if ( empty( $base ) ) {
			return $option_name;
		}

		$template = isset( $field['name']['template'] ) ? $field['name']['template'] : '%s';
		$vars     = [];

		if ( ! empty( $field['name']['extra'] ) ) {
			$vars = $field['name']['extra'];
		}

		array_unshift( $vars, $base );

		if ( ! empty( $option_name ) ) {
			$template .= '[%s]';
			$vars[]   = $option_name;
		}

		return vsprintf( $template, $vars );
	}

	/**
	 * Get the current value of the specified field to populate the element.
	 *
	 * @param array $field   Field settings
	 * @param mixed $default Optional. The default value to use.
	 *
	 * @return mixed The current value of the field, or default if not set.
	 */
	public function get_current_value_for_field( $field, $default = null ) {
		if ( ! isset( $field['key'] ) ) {
			return $default;
		}

		return $this->setting_values[ $field['key'] ] ?: $default;
	}

	/**
	 * Get the setting values.
	 *
	 * @return array The setting values.
	 */
	public function get_setting_values() {
		return $this->setting_values;
	}

	/**
	 * Set the setting values.
	 *
	 * @param array $setting_values The setting values.
	 *
	 * @return void
	 */
	public function set_setting_values( $setting_values ) {
		$this->setting_values = $setting_values;
	}

	/**
	 * Get the page details object.
	 *
	 * @return PageDetails The page details object.
	 */
	public function get_page_details() {
		return $this->page_details;
	}

	/**
	 * Set the page details object.
	 *
	 * @param PageDetails $page_details The page details to set.
	 *
	 * @return void
	 */
	public function set_page_details( $page_details ) {
		$this->page_details = $page_details;
	}
}
