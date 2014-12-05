<?php

class OOUIPlayground {
	protected static $classMap = null;

	static function setupParser( Parser $parser ) {
		$parser->setHook( 'ooui-demo', array( __CLASS__, 'renderDemo' ) );

		return true;
	}

	static function getClassMap() {
		if ( self::$classMap === null ) {
			self::$classMap = require __DIR__ . '/classMap.php';
		}

		return self::$classMap;
	}

	static function setupOOUI() {
		static $setupDone = false;

		if ( ! $setupDone ) {
			OOUI\Theme::setSingleton( new OOUI\MediaWikiTheme );
		}
	}

	static function renderDemo( $input, array $args, Parser $parser, PPFrame $frame ) {
		self::setupOOUI();
		$classMap = self::getClassMap();
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

		$class = $classMap[$type];

		// Prepare config
		unset( $args['type'] );
		if ( trim( $input ) !== '' ) {
			$args['content'] = $input;
		}

		$obj = new $class( $args );

		return $obj->toString();
	}
}
