<?php

namespace OOUIPlayground;

use \MediaWikiTestCase;
use FormatJson;
use Parser;
use ParserOptions;
use Title;

class CodeRendererTest extends MediaWikiTestCase {

	public function provideCodeRenderer() {
		$mockRenderer = $this->getMock( 'OOUIPlayground\\ICodeRenderer' );

		$mockRenderer->method( 'render' )
			->will( $this->returnValue( 'Test' ) );

		return array(
			array(
				new PreCodeRenderer( $this->getRendererConfig(), $this->getParser() ),
				new WidgetInfo( 'test', 'MockWidget' ),
				array( 'key' => 'val' ),
				"<pre>var MockWidget = {\"key\":\"val\"};</pre>",
			),
			array(
				new GeSHICodeRenderer( 'javascript', $this->getRendererConfig(), $this->getParser() ),
				new WidgetInfo( 'test', 'MockWidget' ),
				array( 'key' => 'val' ),
				// This bit was basically copy-pasted from the failing output
				'<div class="ooui-playground-code ooui-playground-code-javascript" data-language="javascript">' .
				'<div dir="ltr" class="mw-geshi mw-code mw-content-ltr"><div class="javascript source-javascript">' .
				'<pre class="de1"><span class="kw1">var</span> MockWidget <span class="sy0">=</span> <span class="br0">&#123;</span>' .
				'<span class="st0">&quot;key&quot;</span><span class="sy0">:</span><span class="st0">&quot;val&quot;</span>' .
				'<span class="br0">&#125;</span><span class="sy0">;</span></pre></div></div></div>',
			),
			array(
				new MultiCodeRenderer( array( $mockRenderer ) ),
				new WidgetInfo( 'test', 'MockWidget' ),
				array( 'key' => 'val' ),
				"<div class=\"ooui-playground-code-group\">Test\n</div>"
			),
		);
	}

	/**
	 * @dataProvider provideCodeRenderer
	 * @param  ICodeRenderer $renderer The code renderer to use
	 * @param  WidgetInfo $widget     Widget to render for
	 * @param  array  $args           Arguments to pass to the widget
	 * @param  string $expectedOutput Expected HTML output
	 */
	public function testCodeRenderer(
		ICodeRenderer $renderer,
		$widgetInfo,
		array $args,
		$expectedOutput
	) {
		$code = $renderer->render( $widgetInfo, $args );
		// Unstrip
		$code = $this->getParser()->mStripState->unstripBoth( $code );

		$this->assertEquals( $code, $expectedOutput );
	}

	protected function getRendererConfig() {
		return array(
			'encodeVars' => function( array $args ) {
				return FormatJson::encode( $args );
			},
			'template' => <<<TEMPLATE
var \$class = \$args;
TEMPLATE
		);
	}

	protected function getParser() {
		static $parser = null;
		if ( $parser === null ) {
			global $wgParser;
			$parser = $wgParser->getFreshParser();
			$parser->startExternalParse( Title::newMainPage(), new ParserOptions, Parser::OT_HTML );
		}

		return $parser;
	}
}
