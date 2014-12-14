<?php

namespace OOUIPlayground;

use \MediaWikiTestCase;

/**
 * @group OOUIPlayground
 */
class WidgetFactoryTest extends MediaWikiTestCase {
	protected function getRepoAndFactory() {
		$repo = new WidgetRepository( array( 'test' => 'MockWidget' ) );
		$factory = new WidgetFactory( $repo );

		return compact( 'repo', 'factory' );
	}


	public function testCreateWidgetWithConfiguration() {
		extract( $this->getRepoAndFactory() );

		$widgetInfo = $repo->getInfo( 'test' );
		$config = array( 'foo' => 'bar' );
		$obj = $factory->getWidget( $widgetInfo, $config );

		$this->assertEquals( $obj->config, $config );
	}

	public function testCreateWidgetFromArray() {
		extract( $this->getRepoAndFactory() );

		$config = array( 'foo' => 'bar', 'type' => 'test' );

		$obj = $factory->create( $config );

		unset( $config['type'] );

		$this->assertEquals( get_class( $obj ), 'OOUI\\MockWidget' );
		$this->assertEquals( $obj->config, $config );
	}

	public function testFilters() {
		extract( $this->getRepoAndFactory() );
		$factory->addFilter( new MockFilter );

		$config = array( 'foo' => 'bar', 'type' => 'test' );

		$obj = $factory->create( $config );

		unset( $config['type'] );
		$config['filtered'] = true;

		$this->assertEquals( $obj->config, $config );
	}
}
