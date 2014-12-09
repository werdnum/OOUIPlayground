<?php

interface ICodeRenderer {
	function render( $class, array $args );
}

abstract class ConfiguredCodeRenderer implements ICodeRenderer {
	/** @var array */
	protected $config;

	function __construct( array $config ) {
		$this->config = $config;
	}

	protected function getCode( $class, array $args ) {
		$replacements = array(
			'$class' => $class,
			'$args' => call_user_func( $this->config['encodeVars'], $args ),
		);

		$code = strtr( $this->config['template'], $replacements );

		return $code;
	}
}

class GeSHICodeRenderer extends ConfiguredCodeRenderer {
	/** @var Parser */
	protected $parser;
	/** @var string */
	protected $languageName;

	function __construct( $languageName, array $config, Parser $parser ) {
		parent::__construct( $config );
		$this->parser = $parser;
		$this->languageName = $languageName;
	}

	public function render( $class, array $args ) {
		$code = $this->getCode( $class, $args );

		$output = $this->parser->extensionSubstitution(
			array(
				'name' => 'source',
				'attributes' => array( 'lang' => $this->languageName ),
				'inner' => $code,
				'close' => '</source>',
			),
			$this->parser->getPreprocessor()->newFrame()
		);

		$output = Html::rawElement(
			'div',
			array(
				'class' => 'ooui-playground-code ooui-playground-code-'.$this->languageName,
				'data-language' => $this->languageName,
			),
			$output
		);

		return $output;
	}
}

class PreCodeRenderer extends ConfiguredCodeRenderer {
	protected $parser;

	public function __construct( array $config, Parser $parser ) {
		parent::__construct( $config );
		$this->parser = $parser;
	}

	public function render( $class, array $args ) {
		$html = Html::element( 'pre', null, $this->getCode( $class, $args ) );
		return $this->parser->insertStripItem( $html );
	}
}

abstract class MultiCodeRenderer implements ICodeRenderer {
	/** @var array */
	protected $renderers;

	public function render( $class, array $args ) {
		$output = '';

		foreach( $this->renderers as $renderer ) {
			$output .= $renderer->render( $class, $args ) . "\n";
		}

		$output = Html::rawElement( 'div', array( 'class' => 'ooui-playground-code-group' ), $output );

		return $output;
	}
}

class MultiPreCodeRenderer extends MultiCodeRenderer {
	function __construct( array $config, Parser $parser ) {
		$renderers = array();

		foreach( $config as $name => $info ) {
			$renderers[$name] = new PreCodeRenderer( $info, $parser );
		}

		$this->renderers = $renderers;
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
