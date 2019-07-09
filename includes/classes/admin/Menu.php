<?php
/**
 * This file holds the Menu class used to control admin menu items.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace Surrealwebs\PrimaryTaxonomy\Admin;

use function add_options_page;
use function add_action;
use function add_filter;

/**
 * Class Menu
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class Menu {
	/** @var PageRenderer $page_renderer Object used to render admin pages. */
	protected $page_renderer;

	/** @var ScreenOptions $screen_option_handler Handles working with screen options. */
	protected $screen_option_handler;

	/** @var Settings $settings_handler */
	protected $settings_handler;

	/** @var string $option_group_name The option_name used to store our data. */
	protected $option_group_name;

	/**
	 * Menu constructor.
	 *
	 * @param string        $option_group_name     Name to use for the settings.
	 * @param PageRenderer  $page_renderer         Object instance used to render pages.
	 * @param ScreenOptions $screen_option_handler Object use to handle screen options.
	 * @param Settings      $settings_handler      Manages settings for admin pages.
	 */
	public function __construct(
		$option_group_name,
		$page_renderer,
		$screen_option_handler,
		$settings_handler
	) {
		$this->option_group_name     = $option_group_name;
		$this->page_renderer         = $page_renderer;
		$this->screen_option_handler = $screen_option_handler;
		$this->settings_handler      = $settings_handler;
	}

	/**
	 * This method will add menu items to the WP Admin Settings menu.
	 *
	 * @uses add_options_page
	 *
	 * @action load-{hook_suffix} The hook_suffix comes from add_options_page.
	 * @filter set-screen-option
	 *
	 * @return void
	 */
	public function add_menu_items() {

		$hook = add_options_page(
			__( 'SW Primary Taxonomies', 'surrealwebs-primary-taxonomy' ),
			__( 'SW Primary Taxonomies', 'surrealwebs-primary-taxonomy' ),
			'manage_options',
			$this->option_group_name . '_options',
			[ $this, 'do_settings_page' ]
		);

		$this->screen_option_handler->add_screen_option(
			'per_page',
			__( 'Items per page', 'surrealwebs-primary-taxonomy' ),
			SURREALWEBS_PRIMARY_TAXONOMY_SCREEN_OPTIONS_PER_PAGE_DEFAULT,
			$this->option_group_name . '_per_page',
			ScreenOptions::OPTION_TYPE_INT
		);

		add_action(
			'load-' . $hook,
			[ $this->screen_option_handler, 'add_screen_options_to_pages' ]
		);

		add_filter(
			'set-screen-option',
			[ $this, 'set_list_page_screen_options' ],
			10,
			3
		);

		$this->settings_handler->set_page_renderer( $this->page_renderer );
		add_action(
			'admin_init',
			[
				$this->settings_handler,
				'register_settings_page_fields'
			]
		);
	}

	/**
	 * Callback for the set-screen-option filter, used to filter option data.
	 *
	 * The method will check the current page to ensure the correct option
	 * is being handled.
	 *
	 * @filter set-screen-option
	 *
	 * @param mixed  $status Status value passed in by the filter.
	 * @param string $option The name of the option being processed.
	 * @param mixed  $value  The value to be filtered, entered by the user.
	 *
	 * @return mixed The filtered value or $status if it's not our option.
	 */
	public function set_screen_options( $status, $option, $value ) {
		$this_page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';
		switch ( $this_page ) {
			case $this->option_group_name . '_options':
				return $this->screen_option_handler->save_screen_option( $status, $option, $value );
				break;
		}

		return $status;
	}

	public function do_settings_page() {
		$this->settings_handler->load( $this->option_group_name, [] );
		$this->page_renderer->main_settings_page( $this->settings_handler );
	}
}
