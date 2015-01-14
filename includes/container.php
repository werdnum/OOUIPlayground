<?php

namespace OOUIPlayground;

use FormatJson;
use Pimple\Container;
use SimpleLightNCandy;

$container = new Container;

$container['classMap'] = array(
	'buttongroup' => 'ButtonGroupWidget',
	'buttoninput' => 'ButtonInputWidget',
	'button' => 'ButtonWidget',
	'checkboxinput' => 'CheckboxInputWidget',
	'icon' => 'IconWidget',
	'indicator' => 'IndicatorWidget',
	'input' => 'InputWidget',
	'label' => 'LabelWidget',
	'radioinput' => 'RadioInputWidget',
	'textinput' => 'TextInputWidget',
);

$container['languages'] = array(
	'php' => array(
		'encodeVars' => function( array $vars ) {
			return var_export( $vars, true );
		},
		'template' => <<<PHP
\$obj = new OOUI\\\$class( \$args );
\$wgOut->addHTML( \$obj->toString() );
PHP
	),
	'javascript' => array(
		'encodeVars' => function( array $vars ) {
			return FormatJson::encode( $vars, true /* pretty */ );
		},
		'template' => <<<JS
var widget = new OO.ui.\$class( \$args );
\$( 'body' ).append( widget.\$element );
JS
	),
);

$container['widgetRepository'] = function( $c ) {
	return new WidgetRepository( $c['classMap'] );
};

$container['widgetFactory'] = function( $c ) {
	$factory = new WidgetFactory( $c['widgetRepository'] );

	// Some filters want a WidgetFactory
	$factory->addFilter( new GroupElementFilter( $factory ) );

	return $factory;
};

$container['templating'] = function( $c ) {
	$templating = new SimpleLightNCandy( __DIR__ . '/../templates/' );
	$templating->addHelper( 'msg',
		function( array $args, array $named ) {
			$message = null;
			$str = array_shift( $args );

			return wfMessage( $str )->params( $args )->text();
		}
	);

	return $templating;
};

return $container;
