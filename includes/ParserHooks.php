<?php

namespace OOUIPlayground;

use FormatJson;
use Html;
use OOUI\MediaWikiTheme;
use OOUI\Theme;
use Parser;
use PPFrame;
use Status;

class ParserHooks {
	protected static $config = null;
	protected static $widgetRepository = null;

	/**
	 * Handler for ParserFirstCallInit hook
	 * @param  Parser $parser Parser to initialise
	 * @return true
	 */
	public static function setupParser( Parser $parser ) {
		$parser->setHook( 'ooui-demo', array( __CLASS__, 'renderDemo' ) );
		$parser->setHook( 'ooui-doc', array( __CLASS__, 'renderDoc' ) );

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
	 * Retrieves an object used to query widget information
	 * @return WidgetRepository
	 */
	protected static function getWidgetRepository() {
		if ( is_null( self::$widgetRepository ) ) {
			self::$widgetRepository = new WidgetRepository(
				self::getConfig( 'classMap' )
			);
		}

		return self::$widgetRepository;
	}

	/**
	 * Does any initialisation necessary to get OOUI to work.
	 */
	protected static function setupOOUI() {
		static $setupDone = false;

		if ( ! $setupDone ) {
			Theme::setSingleton( new MediaWikiTheme );
		}
	}

	public static function renderDoc( $input, array $args, Parser $parser, PPFrame $frame ) {
		$classStatus = self::getWidgetFromAttributes( $args );

		if ( ! $classStatus->isGood() ) {
			return Html::rawElement(
				'span',
				array( 'class' => 'error' ),
				$classStatus->getHTML()
			);
		}

		$doc = new WidgetDocumenter();
		$params = $doc->getOptions( $classStatus->getValue() );

		return Templating::renderTemplate( 'widget_doc', $params );
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
		$parser->getOutput()->addModules( array( 'oojs-ui', 'ext.ooui-playground' ) );
		$parser->getOutput()->addModuleStyles( array( 'oojs-ui', 'ext.ooui-playground' ) );

		$classStatus = self::getWidgetFromAttributes( $args );

		if ( ! $classStatus->isGood() ) {
			return Html::rawElement(
				'span',
				array( 'class' => 'error' ),
				$classStatus->getHTML()
			);
		}

		$class = $classStatus->getValue();

		// Prepare config
		unset( $args['type'] );
		$parseResult = FormatJson::parse(
			$input,
			FormatJson::FORCE_ASSOC | FormatJson::TRY_FIXING | FormatJson::STRIP_COMMENTS
		);
		if ( trim( $input ) !== '' && $parseResult->isOK() ) {
			$args = array_merge( $args, $parseResult->getValue() );
		}

		$warnings = '';
		if ( trim( $input ) !== '' && ! $parseResult->isGood() ) {
			$warnings = $parseResult->getHTML();

			if ( ! $parseResult->isOK() ) {
				return Html::rawElement(
					'span',
					array( 'class' => 'error' ),
					$warnings
				);
			}
		}

		$languages = self::getConfig( 'languages' );
		$renderer = new MultiGeSHICodeRenderer( $languages, $parser );

		$html = "<p>$warnings</p>\n\n" .
			self::getDemo( $class, $args, $renderer );

		return Html::rawElement( 'div', array( 'class' => 'ooui-playground-demo' ), $html );
	}

	/**
	 * Reads the attributes of a tag call to get info about the requested class 
	 * @param  array  $attributes Attributes of a tag call.
	 * @return OOUIPlayground\WidgetInfo
	 */
	protected static function getWidgetFromAttributes( array $attributes ) {
		$classMap = self::getConfig( 'classMap' );
		if ( ! isset( $attributes['type'] ) ) {
			return Status::newFatal( 'ooui-playground-error-no-type' );
		}

		$type = strtolower( $attributes['type'] );
		$class = self::getWidgetRepository()->getInfo( $type );

		if ( ! is_object( $class ) ) {
			return Status::newFatal( 'ooui-playground-error-bad-type', $type );
		}

		return Status::newGood( $class );
	}

	/**
	 * Renders an OOUI widget demo
	 * @param  WidgetInfo    $info     A WidgetInfo class for the widget being demonstrated.
	 * should exist in the 'classMap' config section
	 * @param  array         $args     Processed arguments to pass directly to the OOUI widget.
	 * @param  ICodeRenderer $renderer A code renderer for showing source code
	 * @return string                  HTML output.
	 */
	public static function getDemo( WidgetInfo $class, array $args, ICodeRenderer $renderer ) {
		self::setupOOUI();

		$obj = $class->instantiate( $args );
		$output = $obj->toString();

		$code = $renderer->render( $class, $args );

		return $code .
			Html::rawElement(
				'div',
				array(
					'class' => 'ooui-playground-widget'
				),
				$output );
	}
}
