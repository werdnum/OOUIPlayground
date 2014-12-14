<?php

namespace OOUIPlayground;

use MediaWikiTestCase;
use MockBuilder;

class GroupElementFilterTest extends MediaWikiTestCase {
	public function provideGroupElementFilter() {
		return array(
			array(
				'mock',
				array(
					'foo' => 'bar',
					'items' => array(
						array( 'type' => 'mock' ),
					),
				),
				function( $input, $output ) {
					return $input === $output;
				}
			),
			array(
				'groupmock',
				array(
					'foo' => 'bar',
					'items' => array(
						array( 'type' => 'mock' ),
					),
				),
				function( $input, $output ) {
					return reset( $output['items'] ) instanceof \OOUI\MockWidget;
				}
			),
		);
	}

	/**
	 * @dataProvider provideGroupElementFilter
	 */
	public function testGroupElementFilter( $type, array $input, $verifyCallback ) {
		$classMap = array(
			'mock' => 'MockWidget',
			'groupmock' => 'MockGroupWidget'
		);

		$repo = new WidgetRepository( $classMap );
		$factory = new WidgetFactory( $repo );

		$filter = new GroupElementFilter( $factory );
		$widgetInfo = $repo->getInfo( $type );

		$output = $input;
		$filter->filter( $widgetInfo, $output );

		$this->assertTrue( $verifyCallback( $input, $output ) );
	}
}
