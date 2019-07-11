<?php
/**
 * Test the Admin Settings object.
 *
 * @package SurrealwebsPrimaryTaxonomy
 */

namespace SurrealwebsPrimaryTaxonomyTests;

use Surrealwebs\PrimaryTaxonomy\Admin\Settings;
use WP_Mock\Tools\TestCase;

/**
 * Class SettingsTest
 *
 * @package SurrealwebsPrimaryTaxonomy
 */
class SettingsTest extends TestCase {
	/** @var array $default_settings Default settings for the SUT instance. */
	protected $default_settings = [];

	/** @var string $test_option_name Fake option name for use with SUT. */
	protected $test_option_name = 'test_settings_options';

	/**
	 * Setup method.
	 */
	public function setUp(): void {
		\WP_Mock::setUp();
	}

	/**
	 * Teardown method.
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

	/**
	 * Test the SUT's contruction, make sure data is put where it belongs.
	 */
	public function test_construction() {
		\WP_Mock::userFunction(
			'get_option' ,
			[
				'args' => [ $this->test_option_name, [] ],
				'return' => []
			]
		);

		$sut = new Settings(
			$this->test_option_name,
			$this->default_settings
		);

		$this->assertEquals( $this->test_option_name, $sut->get_option_name() );
		$this->assertEquals( $this->default_settings, $sut->get_default_settings() );
	}

	/**
	 * Test that data is loaded correctly.
	 */
	public function test_load() {
		$expected = 'test this is set';

		\WP_Mock::userFunction(
			'get_option' ,
			[
				'args' => [ $this->test_option_name, [] ],
				'return' => $expected,
			]
		);

		$sut = new Settings(
			$this->test_option_name,
			$this->default_settings
		);

		$sut->load( $this->test_option_name, $this->default_settings );

		$this->assertEquals( $expected, $sut->get_settings() );
	}
}
