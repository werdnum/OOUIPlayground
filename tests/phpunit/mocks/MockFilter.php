<?php

namespace OOUIPlayground;

class MockFilter implements ArgumentFilterInterface {
	public function filter( WidgetInfo $widget, array &$args ) {
		$args['filtered'] = true;
	}
}
