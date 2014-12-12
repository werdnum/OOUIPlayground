<?php

namespace OOUIPlayground;

use MWException;

class GroupElementFilter implements ArgumentFilterInterface {
	/** @var WidgetFactory */
	protected $widgetFactory;

	function __construct( WidgetFactory $widgetFactory ) {
		$this->widgetFactory = $widgetFactory;
	}

	public function filter( WidgetInfo $widget, array &$arguments ) {
		if ( $widget->isA( 'GroupElement' ) && isset( $arguments['items'] ) ) {
			$newItems = array();
			foreach( $arguments['items'] as $item ) {
				if ( ! is_array( $item ) ) {
					throw new InvalidGroupElementItemException();
				} else {
					$newItems[] = $this->widgetFactory->create( $item );
				}
			}

			$arguments['items'] = $newItems;
		}
	}
}

class InvalidGroupElementItemException extends MWException {
	function __construct() {
		parent::__construct( 'Item in group element that cannot be instantiated' );
	}
}
