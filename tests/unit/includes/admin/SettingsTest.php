<?php

namespace SurrealwebsPrimaryTaxonomyTests;

use Surrealwebs\PrimaryTaxonomy\Admin\Settings;
use WP_Mock\Tools\TestCase;

class SettingsTest extends TestCase {

	protected $default_settings = [];
	protected $test_option_name = 'test_settings_options';

	public function setUp(): void {
		\WP_Mock::setUp();
	}

	public function tearDown(): void {
		\WP_Mock::tearDown();
	}

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

	public function test_load() {

	}
}
