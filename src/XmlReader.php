<?php namespace Rackbeat;

use Rackbeat\Exceptions\InvalidFormatException;

class XmlReader
{
	protected const DEFAULT_OPTIONS = [
		'namespace_separator' => ':',
		'attribute_prefix'    => '',
		'always_array'        => [],
		'auto_array'          => true,
		'text_content'        => 'value',
		'format'              => 'array',
		'auto_text'           => true,
		'key_search'          => false, // todo support an array of replacements
		'key_replace'         => false,
		'keep_namespaces'     => false,
		'lowercase'           => false,
	];

	protected $options = [];

	/**
	 * @param array $options
	 */
	public function __construct( $options = [] ) {
		$this->options = array_merge( static::DEFAULT_OPTIONS, $options );
	}

	/**
	 * @param $string
	 *
	 * @return array|object
	 */
	public static function string( $string ) {
		return ( new static() )->fromString( $string );
	}

	/**
	 * @param $filename
	 *
	 * @return array|object
	 */
	public static function path( $filename ) {
		return ( new static() )->fromString( \file_get_contents( $filename ) );
	}

	/**
	 * @param \SplFileObject $fileObject
	 *
	 * @return array|object
	 */
	public static function file( \SplFileObject $fileObject ) {
		return ( new static() )->fromString( $fileObject->fread( $fileObject->getSize() ) );
	}

	/**
	 * @param array $options
	 *
	 * @return XmlReader
	 */
	public static function make( $options = [] ) {
		return new static( $options );
	}

	/**
	 * @param $options
	 *
	 * @return $this
	 */
	public function options( $options ) {
		$this->options = array_merge( static::DEFAULT_OPTIONS, $options );

		return $this;
	}

	/**
	 * @param $string
	 *
	 * @return array|object
	 * @throws InvalidFormatException
	 */
	public function fromString( $string ) {
		if ( $this->options['format'] === 'object' ) {
			// Gross hack, because json automatically converts to objects
			return json_decode( json_encode( $this->xmlToArray( new \SimpleXMLElement( $string ) ) ) );
		}

		try {
			$xml = new \SimpleXMLElement( $string );
		} catch ( \Exception $exception ) {
			throw new InvalidFormatException( $exception->getMessage(), $exception->getCode(), $exception->getPrevious() );
		}

		return $this->xmlToArray( $xml );
	}

	/**
	 * @param string $filename
	 *
	 * @return array|object
	 * @throws InvalidFormatException
	 */
	public function fromPath( $filename ) {
		return $this->fromString( \file_get_contents( $filename ) );
	}

	/**
	 * @param \SplFileObject $fileObject
	 *
	 * @return array|object
	 * @throws InvalidFormatException
	 */
	public function fromFile( \SplFileObject $fileObject ) {
		return $this->fromString( $fileObject->fread( $fileObject->getSize() ) );
	}

	/**
	 * @param $xml
	 *
	 * @return array
	 */
	private function xmlToArray( $xml ) {
		$namespaces     = $xml->getDocNamespaces();
		$namespaces[''] = null; //add base (empty) namespace
		//get attributes from all namespaces
		$attributesArray = [];
		foreach ( $namespaces as $prefix => $namespace ) {
			foreach ( $xml->attributes( $namespace ) as $attributeName => $attribute ) {
				//replace characters in attribute name
				if ( $this->options['key_search'] ) {
					$attributeName =
						str_replace( $this->options['key_search'], $this->options['key_replace'], $attributeName );
				}
				$attributeKey                     = $this->options['attribute_prefix']
				                                    . ( $this->options['keep_namespaces'] && $prefix ? $prefix . $this->options['namespace_separator'] : '' )
				                                    . $attributeName;
				$attributesArray[ $attributeKey ] = (string) $attribute;
			}
		}

		//get child nodes from all namespaces
		$tagsArray = [];
		foreach ( $namespaces as $prefix => $namespace ) {
			foreach ( $xml->children( $namespace ) as $childXml ) {
				//recurse into child nodes
				foreach ( $this->xmlToArray( $childXml, $this->options ) as $childTagName => $childProperties ) {
					$childTagName = $this->options['lowercase'] ? \mb_strtolower( $childTagName ) : $childTagName;
					//replace characters in tag name
					if ( $this->options['key_search'] ) {
						$childTagName = str_replace( $this->options['key_search'], $this->options['key_replace'], $childTagName );
					}

					//add namespace prefix, if any
					if ( $prefix && $this->options['keep_namespaces'] ) {
						$childTagName = $prefix . $this->options['namespace_separator'] . $childTagName;
					}

					if ( ! isset( $tagsArray[ $childTagName ] ) ) {
						//only entry with this key
						//test if tags of this type should always be arrays, no matter the element count
						$tagsArray[ $childTagName ] =
							\in_array( $childTagName, (array) $this->options['always_array'], true ) || ! $this->options['auto_array']
								? [ $childProperties ] : $childProperties;
					} elseif ( \is_array( $tagsArray[ $childTagName ] ) && array_keys( $tagsArray[ $childTagName ] )
					                                                       === range( 0, \count( $tagsArray[ $childTagName ] ) - 1 )
					) {
						//key already exists and is integer indexed array
						$tagsArray[ $childTagName ][] = $childProperties;
					} else {
						//key exists so convert to integer indexed array with previous value in position 0
						$tagsArray[ $childTagName ] = [ $tagsArray[ $childTagName ], $childProperties ];
					}
				}
			}
		}

		//get text content of node
		$text_contentArray = [];
		$plainText         = trim( (string) $xml );
		if ( $plainText !== '' ) {
			$text_contentArray[ $this->options['text_content'] ] = $plainText;
		}

		//stick it all together
		$propertiesArray = ! $this->options['auto_text'] || $attributesArray || $tagsArray || ( $plainText === '' )
			? array_merge( $attributesArray, $tagsArray, $text_contentArray ) : $plainText;

		//return node as array
		return [ $this->options['lowercase'] ? \mb_strtolower( $xml->getName() ) : $xml->getName() => $propertiesArray ];
	}
}