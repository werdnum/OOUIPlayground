<?php

namespace OOUIPlayground;

use MWException;
use ReflectionClass;

class WidgetRepository {
	/** @var array */
	protected $classMap;

	function __construct( array $classMap ) {
		$this->classMap = $classMap;
	}

	/**
	 * Gets the class name of a widget from its type.
	 * @param  string $type The name of the widget type
	 * @throws NoSuchWidgetException
	 * @return string       Class name
	 * (cannot be used with new because it's in the wrong namespace)
	 */
	public function getClassName( $type ) {
		$type = strtolower( $type );

		if ( ! isset( $this->classMap[$type] ) ) {
			throw new NoSuchWidgetException( $type );
		} else {
			return $this->classMap[$type];
		}
	}

	/**
	 * Gets the WidgetInfo for a particular widget.
	 * @param  string $type Name of the widget, matching the classMap in config.php
	 * @throws NoSuchWidgetException
	 * @return WidgetInfo
	 */
	public function getInfo( $type ) {
		$className = $this->getClassName( $type );

		if ( $className ) {
			return new WidgetInfo( $type, $className );
		}
	}
}

class WidgetInfo {
	/** @var string */
	protected $type;
	/** @var string */
	protected $className;
	/** @var array */
	protected $mixins = null;

	/**
	 * Creates a new WidgetInfo
	 * @param string $type      The type, as used in markup
	 * @param string $className Internal identifier used as class name,
	 * from classMap in config.php
	 */
	function __construct( $type, $className ) {
		$this->type = $type;
		$this->className = $className;
	}

	/**
	 * Gets a list of mixins for this class
	 * @return array[string] Class names
	 */
	public function getMixins() {
		if ( is_null( $this->mixins ) ) {
			$obj = $this->instantiate();
			$class = $this->getReflection();
			$mixinProp = $class->getProperty( 'mixins' );
			$mixinProp->setAccessible( true );
			$mixinObjs = $mixinProp->getValue( $obj );
			$this->mixins = array_map( 'get_class', $mixinObjs );
		}

		return $this->mixins;
	}

	/**
	 * Determines if the given type is a superclass or mixin of this widget.
	 * @param  string  $type Class name, with or without namespace
	 * @return boolean
	 */
	public function isA( $type ) {
		if ( substr( $type, 0, 5 ) !== 'OOUI\\' ) {
			$type = "OOUI\\$type";
		}

		return $this->getReflection()->isSubclassOf( $type ) ||
			in_array( $type, $this->getMixins() );
	}

	/**
	 * Returns the simplified type for use in markup.
	 * @return string
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Gets the class name (without any namespacing)
	 * @return string
	 */
	public function getClassName() {
		return $this->className;
	}

	/**
	 * @return ReflectionClass
	 */
	public function getReflection() {
		return new ReflectionClass( $this->getFullClassName() );
	}

	/**
	 * @return string Class name that can be used with new or ReflectionClass
	 */
	public function getFullClassName() {
		return 'OOUI\\' . $this->className;
	}

	/**
	 * Get an instance of the OOUI-PHP class
	 * Only for internal use, outside this class
	 * you should use a correctly configured WidgetFactory.
	 * @param  array  $args Options for instantiation
	 * @return OOUI\Widget  The requested class
	 */
	protected function instantiate( array $args = array() ) {
		$fullClass = $this->getFullClassName();
		return new $fullClass( $args );
	}
}

class NoSuchWidgetException extends MWException {
	/** @var string */
	public $type;

	function __construct( $type ) {
		parent::__construct( "There is no widget called {$type}." );
	}
}
