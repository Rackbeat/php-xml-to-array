<?php

use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
	/** @test */
	public function can_read_simple_object() {
		$this->assertArrayHasKey( 'note', \Rackbeat\XmlReader::path( 'examples/object.xml' ) );
		$this->assertArrayHasKey( 'to', \Rackbeat\XmlReader::make()->fromPath( 'examples/object.xml' )['note'] );
		$this->assertArrayHasKey( 'from', \Rackbeat\XmlReader::file( new SplFileObject( 'examples/object.xml', 'r' ) )['note'] );
		$this->assertObjectHasAttribute( 'heading', \Rackbeat\XmlReader::make( [ 'format' => 'object' ] )->fromPath( 'examples/object.xml' )->note );
		$this->assertArrayHasKey( 'body', \Rackbeat\XmlReader::path( 'examples/object.xml' )['note'] );

		$this->assertEquals( 'Don\'t forget me this weekend!', \Rackbeat\XmlReader::path( 'examples/object.xml' )['note']['body'] );
	}

	/** @test */
	public function can_read_array_of_items() {
		$this->assertArrayHasKey( 'breakfast_menu', \Rackbeat\XmlReader::path( 'examples/array.xml' ) );
		$this->assertArrayHasKey( 'food', \Rackbeat\XmlReader::make()->fromPath( 'examples/array.xml' )['breakfast_menu'] );
		$this->assertCount( 5, \Rackbeat\XmlReader::file( new SplFileObject( 'examples/array.xml', 'r' ) )['breakfast_menu']['food'] );
		$this->assertObjectHasAttribute( 'food', \Rackbeat\XmlReader::make( [ 'format' => 'object' ] )->fromPath( 'examples/array.xml' )->breakfast_menu );
		$this->assertArrayHasKey( 'name', \Rackbeat\XmlReader::path( 'examples/array.xml' )['breakfast_menu']['food'][0] );
		$this->assertArrayHasKey( 'price', \Rackbeat\XmlReader::path( 'examples/array.xml' )['breakfast_menu']['food'][1] );
		$this->assertArrayHasKey( 'description', \Rackbeat\XmlReader::path( 'examples/array.xml' )['breakfast_menu']['food'][1] );
		$this->assertArrayHasKey( 'calories', \Rackbeat\XmlReader::path( 'examples/array.xml' )['breakfast_menu']['food'][3] );

		$this->assertEquals( 'Belgian Waffles', \Rackbeat\XmlReader::path( 'examples/array.xml' )['breakfast_menu']['food'][0]['name'] );
	}


	/** @test */
	public function will_fail_to_read_invalid_file() {
		$this->assertArrayHasKey( 'breakfast_menu', \Rackbeat\XmlReader::path( 'examples/error.xml' ) );
	}

}
