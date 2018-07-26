<?php

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
	/** @test */
	public function can_force_attribute_to_be_an_array() {
		$reader = new \Rackbeat\XmlReader( [ 'always_array' => [ 'to', 'from' ] ] );
		$this->assertTrue( \is_array( $reader->fromPath( __DIR__ . '/examples/object.xml' )['note']['to'] ) );
		$this->assertFalse( \is_array( $reader->fromPath( __DIR__ . '/examples/object.xml' )['note']['body'] ) );
	}

	/** @test */
	public function can_lowercase_attributes() {
		// Default
		$reader = new \Rackbeat\XmlReader( [ 'lowercase' => false ] );

		$this->assertArrayHasKey( 'NOTE', $reader->fromPath( __DIR__ . '/examples/uppercase.xml' ) );
		$this->assertArrayHasKey( 'TO', $reader->fromPath( __DIR__ . '/examples/uppercase.xml' )['NOTE'] );

		// Lowercase
		$reader->options( [ 'lowercase' => true ] );

		$this->assertArrayHasKey( 'note', $reader->fromPath( __DIR__ . '/examples/uppercase.xml' ) );
		$this->assertArrayHasKey( 'to', $reader->fromPath( __DIR__ . '/examples/uppercase.xml' )['note'] );
	}

	/** @test */
	public function can_search_replace_attributes() {
		// Default
		$reader = new \Rackbeat\XmlReader( [ 'key_search' => 'to', 'key_replace' => 'to_user' ] );

		$this->assertArrayHasKey( 'note', $reader->fromPath( 'examples/object.xml' ) );
		$this->assertArrayNotHasKey( 'to', $reader->fromPath( 'examples/object.xml' )['note'] ); // replaced
		$this->assertArrayHasKey( 'to_user', $reader->fromPath( 'examples/object.xml' )['note'] ); // new
	}
}
