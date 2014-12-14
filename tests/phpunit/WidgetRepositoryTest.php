<?php

namespace OOUIPlayground;

use \MediaWikiTestCase;
use \MockBuilder;

class WidgetRepositoryTest extends MediaWikiTestCase {
	public function testGetClassName() {
		$repo = $this->getRepo();

		$className = $repo->getClassName( 'foo' );
		$this->assertEquals( $className, 'FooWidget' );
	}

	public function testGetInfo() {
		$repo = $this->getRepo();

		$info = $repo->getInfo( 'foo' );
		$this->assertEquals( $info->getClassName(), 'FooWidget' );
		$this->assertEquals( $info->getType(), 'foo' );
	}

	/**
	 * @expectedException OOUIPlayground\NoSuchWidgetException
	 */
	public function testGetUnknownWidget() {
		$repo = $this->getRepo();

		$repo->getClassName( 'non-existent-widget' );
	}

	protected function getRepo() {
		return new WidgetRepository( array( 'foo' => 'FooWidget' ) );
	}
}
