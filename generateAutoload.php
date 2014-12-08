<?php

require_once __DIR__ . '/../../includes/utils/AutoloadGenerator.php';

function main() {
	$base = __DIR__;
	$generator = new AutoloadGenerator( $base );
	foreach ( array( 'includes' ) as $dir ) {
		$generator->readDir( $base . '/' . $dir );
	}

	$generator->generateAutoload( $base );

	echo "Done.\n\n";
}

main();
