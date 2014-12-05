<?php

require_once __DIR__ . '/../../includes/utils/AutoloadGenerator.php';

function main() {
	$base = __DIR__;
	$generator = new AutoloadGenerator( $base );
	foreach ( array( 'lib/oojs-ui/php' ) as $dir ) {
		$generator->readDir( $base . '/' . $dir );
	}

	$generator->generateAutoload( basename( __FILE__ ) );

	echo "Done.\n\n";
}

main();
