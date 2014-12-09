<?php

namespace OOUIPlayground;

use LightnCandy;

abstract class Templating {
	protected static $templates = array();

	public static function renderTemplate( $templateName, array $input ) {
		$renderFunction = self::getTemplate( $templateName );

		return $renderFunction( $input );
	}

	public static function getTemplate( $templateName ) {
		if ( ! isset( self::$templates[$templateName] ) ) {
			$phpFile = self::getBasePath() . $templateName . '.php';
			$templateFile = self::getBasePath() . $templateName . '.template';

			$cacheOk = file_exists( $phpFile ) &&
				filemtime( $phpFile ) >= filemtime( $templateFile );

			if ( ! $cacheOk ) {
				$templateStr = file_get_contents( $templateFile );
				$phpStr = LightnCandy::compile( $templateStr, self::getCompileOptions() );
				file_put_contents( $phpFile, $phpStr );
			}

			$renderFunction = include $phpFile;

			self::$templates[$templateName] = $renderFunction;
		}

		return self::$templates[$templateName];
	}

	protected static function getCompileOptions() {
		return array(
			'flags' => LightnCandy::FLAG_STANDALONE | LightnCandy::FLAG_MUSTACHE,
			'basedir' => array(
				self::getBasePath(),
			),
			'fileext' => array(
				'.template',
			),
			'helpers' => array(
				'msg' => 'OOUIPlayground\Templating::msgHelper',
			),
		);
	}

	/**
	 * @param array $args one or more arguments, i18n key and parameters
	 * @param array $named unused
	 * @return string Plaintext
	 */
	public static function msgHelper( array $args, array $named ) {
		$message = null;
		$str = array_shift( $args );

		return wfMessage( $str )->params( $args )->text();
	}

	protected static function getBasePath() {
		return __DIR__ . '/../templates/';
	}
}
