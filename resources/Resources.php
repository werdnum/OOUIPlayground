<?php

$wgResourceModules['ext.ooui-playground'] = array(
	'localBasePath' => __DIR__,
	'remoteExtPath' => 'OOUIPlayground/resources',
	'group' => 'ext.ooui-playground',
	'styles' => 'display.less',
	'scripts' => 'display.js',
	'dependencies' => 'oojs-ui',
);
