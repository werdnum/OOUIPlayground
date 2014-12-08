<?php

class WidgetDocumenter {
	/** @var ReflectionClass */
	protected $class;

	function __construct( $className ) {
		$this->class = new ReflectionClass( 'OOUI\\' . $className );
	}

	public function getOptions() {
		$options = $this->getOptionsFromClass( $this->class );

		foreach( $this->getMixins() as $mixin ) {
			$mixinClass = new ReflectionClass( $mixin );
			$options = array_merge(
				$options,
				$this->getOptionsFromClass( $mixinClass )
			);
		}

		return $options;
	}

	protected function getOptionsFromClass( ReflectionClass $class ) {
		$constructor = $class->getConstructor();
		$docComment = $constructor->getDocComment();

		if ( $docComment === false ) {
			return array();
		}

		$filter = new Sami\Parser\Filter\TrueFilter;
		$parser = new Sami\Parser\DocBlockParser;
		$context = new Sami\Parser\ParserContext( $filter, $parser, 'pretty printer' );
		$context->enterNamespace( 'OOUI' );
		$doc = $parser->parse( $docComment, $context );

		$paramInfo = $doc->getTag( 'param' );

		$output = array();

		foreach( $paramInfo as $param ) {
			$matches = array();
			if ( preg_match( '/^config\[\'([^\'\]]+)\'\]$/', $param[1], $matches ) ) {
				$types = array_map( function( $type ) {
					return $type[0] . ($type[1] ? '[]' : '');
				}, $param[0] );
				$output[$matches[1]] = array(
					'types' => $types,
					'description' => $param[2],
				);
			}
		}

		return $output;
	}

	protected function getMixins() {
		$mixinProp = $this->class->getProperty( 'mixins' );
		$mixinProp->setAccessible( true );

		$obj = $this->class->newInstance();
		$mixins = $mixinProp->getValue( $obj );

		return $mixins;
	}
}
