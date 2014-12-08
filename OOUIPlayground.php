<?php
/**
 * MediaWiki Extension: OOUIPlayground
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * This program is distributed WITHOUT ANY WARRANTY.
 */

/**
 *
 * @file
 * @ingroup Extensions
 * @author Andrew Garrett
 */

if ( !defined( 'MEDIAWIKI' ) ) {
	echo <<<EOT
To install this extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/OOUIPlayground/OOUIPlayground.php" );
EOT;
	exit( 1 );
}

// Extension credits that will show up on Special:Version
$wgExtensionCredits['specialpage'][] = array(
	'path' => __FILE__,
	'name' => 'OOUIPlayground',
	'url' => 'https://www.mediawiki.org/wiki/Design/Living_style_guide',
	'author' => array(
		'Andrew Garrett',
	),
	'descriptionmsg' => 'oouiplayground-desc',
);

$dir = dirname( __FILE__ );

require_once __DIR__ . "/autoload.php";
require_once __DIR__ . "/lib/autoload.php";
require_once __DIR__ . "/vendor/autoload.php";

$wgHooks['ParserFirstCallInit'][] = 'OOUIPlayground::setupParser';
