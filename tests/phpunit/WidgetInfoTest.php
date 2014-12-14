<?php

namespace OOUIPlayground;

use \MediaWikiTestCase;
use \MockBuilder;

class WidgetInfoTest extends MediaWikiTestCase {
	public function provideGetMixins() {
		return array(
			array(
				'MockWidget',
				array(),
			),
			array(
				'MockGroupWidget',
				array(
					'OOUI\\GroupElement',
					'OOUI\\LabelElement',
				),
			),
		);
	}

	/**
	 * @dataProvider provideGetMixins
	 */
	public function testGetMixins( $class, array $expectedMixins ) {
		$widgetInfo = new WidgetInfo( 'test', $class );

		$this->assertEquals( $widgetInfo->getMixins(), $expectedMixins );
	}

	public function provideIsA() {
		return array(
			array(
				'MockWidget',
				'Widget',
				true,
			),
			array(
				'MockWidget',
				'LabelElement',
				false,
			),
			array(
				'MockGroupWidget',
				'MockWidget',
				true,
			),
			array(
				'MockWidget',
				'MockGroupWidget',
				false,
			),
			array(
				'MockGroupWidget',
				'GroupElement',
				true,
			),
		);
	}

	/**
	 * @dataProvider provideIsA
	 */
	public function testIsA( $class, $isA, $value ) {
		$widgetInfo = new WidgetInfo( 'test', $class );

		$this->assertEquals( $value, $widgetInfo->isA( $isA ) );
	}
}
