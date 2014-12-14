<?php

namespace OOUIPlayground;

use Exception;
use FormatJson;
use Html;
use MWException;
use OOUI\MediaWikiTheme;
use OOUI\Theme;
use Parser;
use PPFrame;
use Status;

class ParserHooks {
	protected static $container = null;

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
	 * Gets an object from the container.
	 * @param  string $obj The object to get
	 * @return mixed|null   That section in config.php, or null if it does not exist.
	 */
	protected static function getContainer( $obj ) {
		if ( self::$container === null ) {
			self::$container = require __DIR__ . '/container.php';
		}

		return self::$container[$obj];
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
			return self::renderError( $classStatus );
		}

		$doc = new WidgetDocumenter();
		$params = $doc->getOptions( $classStatus->getValue() );

		return $this->getContainer( 'templating' )->renderTemplate( 'widget_doc', $params );
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
		$parser->getOutput()->addModules( array( 'oojs-ui', 'ext.ooui-playground' ) );
		$parser->getOutput()->addModuleStyles( array( 'oojs-ui', 'ext.ooui-playground' ) );

		$classStatus = self::getWidgetFromAttributes( $args );

		if ( ! $classStatus->isGood() ) {
			return self::renderError( $classStatus );
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
			$warnings = self::renderError( $parseResult );

			if ( ! $parseResult->isOK() ) {
				return $warnings;
			}
		}

		$languages = self::getContainer( 'languages' );
		$renderer = new MultiGeSHICodeRenderer( $languages, $parser );

		$html = "<p>$warnings</p>\n\n" .
			self::getDemo( $class, $args, $renderer );

		return Html::rawElement( 'div', array( 'class' => 'ooui-playground-demo' ), $html );
	}

	/**
	 * Reads the attributes of a tag call to get info about the requested class 
	 * @param  array  $attributes Attributes of a tag call.
	 * @todo Merge with WidgetRepository::create
	 * @return WidgetInfo
	 */
	protected static function getWidgetFromAttributes( array $attributes ) {
		if ( ! isset( $attributes['type'] ) ) {
			return Status::newFatal( 'ooui-playground-error-no-type' );
		}

		$type = strtolower( $attributes['type'] );

		try {
			$class = self::getContainer( 'widgetRepository' )->getInfo( $type );
		} catch ( NoSuchWidgetException $excep ) {
			return Status::newFatal( 'ooui-playground-error-bad-type', $type );
		}

		return Status::newGood( $class );
	}

	/**
	 * Renders an OOUI widget demo
	 * @param  WidgetInfo    $info     A WidgetInfo class for the widget being demonstrated.
	 * should exist in the 'classMap' config section
	 * @param  array         $args     Processed arguments to pass directly to the OOUI widget.
	 * @param  ICodeRenderer $codeRenderer A code renderer for showing source code
	 * @return string                  HTML output.
	 */
	public static function getDemo( WidgetInfo $class, array $args, ICodeRenderer $codeRenderer ) {
		self::setupOOUI();

		$factory = self::getContainer( 'widgetFactory' );

		try {
			$obj = $factory->getWidget( $class, $args );
		} catch ( MWException $excep ) {
			return self::renderError( $excep );
		}

		$output = $obj->toString();

		$code = $codeRenderer->render( $class, $args );

		return $code .
			Html::rawElement(
				'div',
				array(
					'class' => 'ooui-playground-widget'
				),
				$output );
	}

	/**
	 * Renders an error in an appropriate way.
	 * @param  string|MWException|Status $str Error to display, or text (not HTML)
	 * @return HTML
	 */
	protected static function renderError( $str ) {
		if ( !is_object( $str ) ) {
			$str = htmlspecialchars( $str );
		} elseif ( $str instanceof Exception ) {
			$str = htmlspecialchars( $str->getMessage() );
		} elseif ( $str instanceof Status ) {
			$str = $str->getHTML();
		}

		return Html::rawElement(
			'span',
			array( 'class' => 'error' ),
			$str
		);
	}
}
