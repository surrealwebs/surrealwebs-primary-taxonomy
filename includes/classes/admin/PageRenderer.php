<?php

namespace Surrealwebs\PrimaryTaxonomy\Admin;

class PageRenderer {

	protected $setting_values;

	protected $page_details;

	public function __construct( $page_details, $setting_values ) {
		$this->page_details   = $page_details;
		$this->setting_values = $setting_values;
	}

	public function main_settings_page( $settings ) {
		$this->header();

		if ( ! empty( $this->page_details->body['intro'] ) ) {
			$this->add_content_block( $this->page_details->body['intro'] );
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

	protected function header() {
		?>
		<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<?php
	}

	protected function footer() {
		?>
		</div>
		<?php
	}

	public function get_callback_name_from_type( $type ) {
		$valid_types = [
			'checkbox',
			'radio',
			'select',
			'text',
			'textarea',
		];

		if ( ! in_array( $type, $valid_types, true ) ) {
			$type = 'text';
		}

		return sprintf( 'render_%s', $type );
	}

	public function render_checkbox( $field ) {
		$template = '<label for="%s"><input type="checkbox" name="%s" value="%s" id="%s" %s />%s</label><br/>';

		$current_value = $this->get_current_value_for_field( $field, [] );

		foreach ( $field['options'] as $option ) {
			$option_tag_id = sprintf( '%s_%s', $field['key'], $option['id'] );
			echo sprintf(
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


	public function render_radio( $field ) {
		$template = '<label for="%s"><input type="radio" name="%s" value="%s" id="%s" %s /> %s</label><br/>';

		$current_value = $this->get_current_value_for_field( $field, '' );
		foreach ( $field['options'] as $option ) {
			$option_tag_id = sprintf( '%s_%s', $field['key'], $option['id'] );
			echo sprintf(
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
	public function render_select() {}
	public function render_text() {}
	public function render_textarea() {}

	public function extract_field_name_from_field( $field, $extra_data = [] ) {
		if ( empty( $field['name'] ) ) {
			return '';
		}

		if ( isset( $field['name']['base'] ) ) {
			return $field['name']['base'];
		}

		return '';
	}

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
			$vars[]    = $option_name;
		}

		return vsprintf( $template, $vars );

	}

	public function get_current_value_for_field( $field, $default = null ) {
		if ( ! isset( $field['key'] ) ) {
			return $default;
		}

		return $this->setting_values[ $field['key'] ] ?: $default;
	}

	public function get_setting_values() {
		return $this->setting_values;
	}

	public function set_setting_values( $setting_values ) {
		$this->setting_values = $setting_values;
	}

	public function get_page_details() {
		return $this->page_details;
	}

	public function set_page_details( $page_details ) {
		$this->page_details = $page_details;
	}
}
