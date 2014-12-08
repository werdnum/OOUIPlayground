<?php

class OOUIPlayground {
	protected static $config = null;

	/**
	 * Handler for ParserFirstCallInit hook
	 * @param  Parser $parser Parser to initialise
	 * @return true
	 */
	public static function setupParser( Parser $parser ) {
		$parser->setHook( 'ooui-demo', array( __CLASS__, 'renderDemo' ) );

		return true;
	}

	/**
	 * Gets a section of configuration
	 * @param  string $type The section of configuration to retrieve
	 * @return mixed|null   That section in config.php, or null if it does not exist.
	 */
	protected static function getConfig( $type ) {
		if ( self::$config === null ) {
			self::$config = require __DIR__ . '/config.php';
		}

		return isset( self::$config[$type] ) ? self::$config[$type] : null;
	}

	/**
	 * Does any initialisation necessary to get OOUI to work.
	 */
	protected static function setupOOUI() {
		static $setupDone = false;

		if ( ! $setupDone ) {
			OOUI\Theme::setSingleton( new OOUI\MediaWikiTheme );
		}
	}

	/**
	 * Parser tag extension entry point. Validates input and hands off to getDemo()
	 * @param  string  $input  Contents of the tag.
	 * @param  array   $args   Attributes on the tag
	 * @param  Parser  $parser Parser object
	 * @param  PPFrame $frame  The frame context for this call.
	 * @return string          HTML to output
	 */
	public static function renderDemo( $input, array $args, Parser $parser, PPFrame $frame ) {
		$classMap = self::getConfig( 'classMap' );
		$parser->getOutput()->addModules( array( 'oojs-ui' ) );

		if ( ! isset( $args['type'] ) ) {
			return Html::element( 'span', array( 'class' => 'error' ), 'You must specify a type.' );
		}

		$type =  strtolower( $args['type'] );
		if ( ! isset( $classMap[$type] ) ) {
			return Html::element(
				'span',
				array( 'class' => 'error' ),
				'There is no OOUI widget called ' . $type . '.'
			);
		}

		// Prepare config
		unset( $args['type'] );
		$parseResult = FormatJson::parse( $input, FormatJson::FORCE_ASSOC );
		if ( $parseResult->isOK() ) {
			$args = array_merge( $args, $parseResult->getValue() );
		}

		$warnings = '';
		if ( trim( $input ) !== '' && ! $parseResult->isGood() ) {
			$warnings = $parseResult->getHTML();
		}

		$languages = self::getConfig( 'languages' );
		$renderer = new MultiGeSHICodeRenderer( $languages, $parser );

		return Html::rawElement( 'p', null, $warnings ) .
			"\n\n" . self::getDemo( $type, $args, $renderer );
	}

	/**
	 * Renders an OOUI widget demo
	 * @param  string        $type     The name of the widget,
	 * should exist in the 'classMap' config section
	 * @param  array         $args     Processed arguments to pass directly to the OOUI widget.
	 * @param  ICodeRenderer $renderer A code renderer for showing source code
	 * @return string                  HTML output.
	 */
	public static function getDemo( $type, array $args, ICodeRenderer $renderer ) {
		self::setupOOUI();
		$classMap = self::getConfig( 'classMap' );
		$className = $classMap[$type];
		$class = 'OOUI\\'.$className;

		$obj = new $class( $args );
		$output = $obj->toString();

		$code = $renderer->render( $className, $args );

		return "<p>$output</p>$code";
	}
}
