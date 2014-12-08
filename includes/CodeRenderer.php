<?php

interface ICodeRenderer {
	function render( $class, array $args );
}

class GeSHICodeRenderer implements ICodeRenderer {
	/** @var Parser */
	protected $parser;
	/** @var array */
	protected $config;
	/** @var string */
	protected $languageName;

	function __construct( $languageName, array $config, Parser $parser ) {
		$this->parser = $parser;
		$this->config = $config;
		$this->languageName = $languageName;
	}

	public function render( $class, array $args ) {
		$replacements = array(
			'$class' => $class,
			'$args' => call_user_func( $this->config['encodeVars'], $args ),
		);

		$code = strtr( $this->config['template'], $replacements );

		$output = $this->parser->extensionSubstitution(
			array(
				'name' => 'source',
				'attributes' => array( 'lang' => $this->languageName ),
				'inner' => $code,
				'close' => '</source>',
			),
			$this->parser->getPreprocessor()->newFrame()
		);

		return Html::rawElement(
			'div',
			array( 'class' => 'ooui-playground-code oo-ui-playground-code-'.$this->languageName ),
			$output
		);
	}
}

abstract class MultiCodeRenderer implements ICodeRenderer {
	/** @var array */
	protected $renderers;

	public function render( $class, array $args ) {
		$output = '';

		foreach( $this->renderers as $renderer ) {
			$output .= $renderer->render( $class, $args );
		}

		return $output;
	}
}

class MultiGeSHICodeRenderer extends MultiCodeRenderer {

	function __construct( array $config, Parser $parser ) {
		$renderers = array();

		foreach( $config as $name => $info ) {
			$renderers[$name] = new GeSHICodeRenderer( $name, $info, $parser );
		}

		$this->renderers = $renderers;
	}
}

class NullCodeRenderer implements ICodeRenderer {
	function render( $class, array $args ) {
		return '';
	}
}
