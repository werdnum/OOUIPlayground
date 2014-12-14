<?php

namespace OOUIPlayground;

use \MediaWikiTestCase;
use \MockBuilder;

class WidgetDocumenterTest extends MediaWikiTestCase {
	protected $standardParams = array(
		'classes' => array(
			'name' => 'classes',
			'types' => array ( 'string[]' ),
			'description' => 'CSS class names to add',
		),
		'content' => array(
			'name' => 'content',
			'types' => array ( 'array' ),
			'description' => 'Content to append, strings or Element objects. Strings will be HTML-escaped for output, use a HtmlSnippet instance to prevent that.',
		),
		'disabled' => array(
			'name' => 'disabled',
			'types' => array ( 'boolean' ),
			'description' => 'Disable (default: false)',
		),
	);

	public function provideWidgetDocumenter() {
		return array(
			array(
				'MockWidget',
				array(
					'testparam' => array(
						'name' => 'testparam',
						'types' => array( 'string[]', 'bool' ),
						'description' => 'A test parameter',
					),
				),
			),
			array(
				'MockGroupWidget',
				array(
					'testparam' => array(
						'name' => 'testparam',
						'types' => array( 'string[]', 'bool' ),
						'description' => 'A test parameter',
					),
					'label' => array(
						'name' => 'label',
						'types' => array( 'string' ),
						'description' => 'Label text',
					),
				),
			),
		);
	}

	/**
	 * @dataProvider provideWidgetDocumenter
	 */
	public function testWidgetDocumenter( $className, $expected ) {
		$doc = new WidgetDocumenter;
		$widget = new WidgetInfo( 'test', $className );

		$data = $doc->getOptions( $widget );

		$expected += $this->standardParams;

		$this->assertEquals( $expected, $data );
	}
}
