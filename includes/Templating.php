<?php

namespace OOUIPlayground;

use LightnCandy;
use MWException;

class Templating {
	protected $templates = array();
	protected $basePath = '';

	public function __construct( $basePath ) {
		$this->basePath = $basePath;
	}

	public function renderTemplate( $templateName, array $input ) {
		$renderFunction = $this->getTemplate( $templateName );

		return $renderFunction( $input );
	}

	public function getTemplate( $templateName ) {
		if ( ! isset( $this->templates[$templateName] ) ) {
			$phpFile = $this->basePath . $templateName . '.php';
			$templateFile = $this->basePath . $templateName . '.template';

			$cacheOk = file_exists( $phpFile ) &&
				filemtime( $phpFile ) >= filemtime( $templateFile );

			if ( ! $cacheOk ) {
				if ( ! file_exists( $templateFile ) ) {
					throw new TemplatingException( "Unable to load $templateName from $templateFile" );
				}
				$templateStr = file_get_contents( $templateFile );
				$phpStr = LightnCandy::compile( $templateStr, $this->getCompileOptions() );
				file_put_contents( $phpFile, $phpStr );
			}

			$renderFunction = include $phpFile;

			$this->templates[$templateName] = $renderFunction;
		}

		return $this->templates[$templateName];
	}

	protected function getCompileOptions() {
		return array(
			'flags' => LightnCandy::FLAG_STANDALONE | LightnCandy::FLAG_MUSTACHE,
			'basedir' => array(
				$this->basePath,
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
	public function msgHelper( array $args, array $named ) {
		$message = null;
		$str = array_shift( $args );

		return wfMessage( $str )->params( $args )->text();
	}
}

class TemplatingException extends MWException {

}
