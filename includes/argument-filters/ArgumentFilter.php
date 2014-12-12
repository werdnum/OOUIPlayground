<?php
namespace OOUIPlayground;

interface ArgumentFilterInterface {
	function filter( WidgetInfo $widget, array &$args );
}

class ArgumentFilterGroup implements ArgumentFilterInterface {
	/** @var array[ArgumentFilterInterface] */
	protected $filters;

	/**
	 * @param array[ArgumentFilterInterface] $filters
	 */
	public function __construct( array $filters = array() ) {
		$this->filters = $filters;
	}

	public function filter( WidgetInfo $widget, array &$args ) {
		foreach( $this->filters as $filter ) {
			$filter->filter( $widget, $args );
		}
	}

	public function addFilter( ArgumentFilterInterface $filter ) {
		$this->filters[] = $filter;
	}
}
