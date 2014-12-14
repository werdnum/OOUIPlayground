<?php

namespace OOUIPlayground;

use Sami\Parser\Filter\TrueFilter;
use Sami\Parser\DocBlockParser;
use Sami\Parser\ParserContext;
use ReflectionClass;

class WidgetDocumenter {
	/**
	 * Gets the configuration options for this Widget.
	 * @return array of options. Each option has the following keys:
	 * * name: The code name of the option.
	 * * types: Array of accepted types.
	 * * description: The description in the code.
	 */
	public function getOptions( WidgetInfo $info ) {
		$options = $this->getOptionsFromClass( $info->getReflection() );

		foreach( $info->getMixins() as $mixin ) {
			$mixinClass = new ReflectionClass( $mixin );
			$options = array_merge(
				$this->getOptionsFromClass( $mixinClass ),
				$options
			);
		}

		return $options;
	}

	/**
	 * Reads out configuration options from the doc comment of a class.
	 * @param  ReflectionClass $class The class to examine.
	 * @return array Like the output from getOptions()
	 */
	protected function getOptionsFromClass( ReflectionClass $class ) {
		$constructor = $class->getConstructor();
		$docComment = $constructor->getDocComment();

		if ( $docComment !== false ) {
			$filter = new TrueFilter;
			$parser = new DocBlockParser;
			$context = new ParserContext( $filter, $parser, 'pretty printer' );
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
						'name' => $matches[1],
						'types' => $types,
						'description' => $param[2],
					);
				}
			}
		} else {
			$output = array();
		}

		$parentClass = $class->getParentClass();
		if (
			$parentClass
		) {
			$output = array_merge( $this->getOptionsFromClass( $parentClass ), $output );
		}

		return $output;
	}
}
