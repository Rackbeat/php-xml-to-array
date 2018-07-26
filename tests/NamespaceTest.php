<?php

use PHPUnit\Framework\TestCase;

class NamespaceTest extends TestCase
{
	/** @test */
	public function strips_namespaces_by_default() {
		$reader = new \Rackbeat\XmlReader( [ 'attribute_prefix' => 'h' ] );

		$this->assertArrayHasKey( 'root', $reader->fromPath( 'examples/namespaces.xml' ) );
		$this->assertArrayHasKey( 'table', $reader->fromPath( 'examples/namespaces.xml' )['root'] );
		$this->assertArrayHasKey( 'tr', $reader->fromPath( 'examples/namespaces.xml' )['root']['table'] );
		$this->assertArrayHasKey( 'td', $reader->fromPath( 'examples/namespaces.xml' )['root']['table']['tr'] );
	}

	/** @test */
	public function can_keep_namespaces() {
		$reader = new \Rackbeat\XmlReader( [ 'keep_namespaces' => true ] );

		$this->assertArrayHasKey( 'root', $reader->fromPath( 'examples/namespaces.xml' ) );
		$this->assertArrayHasKey( 'h:table', $reader->fromPath( 'examples/namespaces.xml' )['root'] );
		$this->assertArrayHasKey( 'h:tr', $reader->fromPath( 'examples/namespaces.xml' )['root']['h:table'] );
		$this->assertArrayHasKey( 'h:td', $reader->fromPath( 'examples/namespaces.xml' )['root']['h:table']['h:tr'] );
	}
}
