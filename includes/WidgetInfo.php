<?php

namespace OOUIPlayground;

use ReflectionClass;

class WidgetRepository {
	/** @var array */
	protected $classMap;

	function __construct( array $classMap ) {
		$this->classMap = $classMap;
	}

	public function getClassName( $type ) {
		$type = strtolower( $type );
		if ( ! isset( $this->classMap[$type] ) ) {
			return false;
		} else {
			return $this->classMap[$type];
		}
	}

	public function getInfo( $type ) {
		$className = $this->getClassName( $type );

		if ( $className ) {
			return new WidgetInfo( $type, $className );
		} else {
			return false;
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
	 * Get an instance of the OOUI-PHP class
	 * @param  array  $args Options for instantiation
	 * @return OOUI\Widget  The requested class
	 */
	public function instantiate( array $args = array() ) {
		$fullClass = $this->getFullClassName();
		return new $fullClass( $args );
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

	public function getType() {
		return $this->type;
	}

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
	protected function getFullClassName() {
		return 'OOUI\\' . $this->className;
	}
}
