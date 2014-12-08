<?php

require_once __DIR__ . '/../../includes/utils/AutoloadGenerator.php';

function main() {
	$base = __DIR__ . '/lib';
	$generator = new AutoloadGenerator( $base );
	foreach ( array( 'oojs-ui/php' ) as $dir ) {
		$generator->readDir( $base . '/' . $dir );
	}

	$generator->generateAutoload( $base );

	echo "Done.\n\n";
}

main();
