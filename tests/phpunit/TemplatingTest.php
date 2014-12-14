<?php

namespace OOUIPlayground;

use MediaWikiTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class TemplatingTest extends MediaWikiTestCase {
	public function testRenderTemplate() {
		vfsStream::setup( 'root' );
		$basedir = vfsStream::url( 'root/' );
		file_put_contents( "{$basedir}test.template", 'Test {{template}}' );

		$templating = new Templating( $basedir );

		$output = $templating->renderTemplate( 'test', array( 'template' => 'foo' ) );

		$this->assertEquals( 'Test foo', $output );
	}

	public function testReplaceTemplate() {
		$root = vfsStream::setup( 'root' );

		$basedir = vfsStream::url( 'root/' );
		file_put_contents( "{$basedir}test2.template", 'Test {{template}}' );
		file_put_contents( "{$basedir}test2.php", '<?php return function() { echo "fail"; };' );

		$root->getChild( 'test2.php' )->lastModified( time() - 5 );

		$templating = new Templating( $basedir );

		$output = $templating->renderTemplate( 'test2', array( 'template' => 'foo' ) );

		$this->assertEquals( 'Test foo', $output );
	}
}
