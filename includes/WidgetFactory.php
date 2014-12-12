<?php

namespace OOUIPlayground;

use MWException;

class WidgetFactory {
	/** @var ArgumentFilterGroup */
	protected $filter;
	/** @var WidgetRepository */
	protected $repo;

	/**
	 * @param WidgetRepository 				 $repo
	 * @param array[ArgumentFilterInterface] $filters
	 */
	function __construct( WidgetRepository $repo, array $filters = array() ) {
		$this->repo = $repo;
		$this->filter = new ArgumentFilterGroup( $filters );
	}

	function getWidget( WidgetInfo $info, array $args ) {
		$this->filter->filter( $info, $args );
		$className = $info->getFullClassName();
		return new $className( $args );
	}

	/**
	 * Creates a widget from an array configuration
	 * @param  array  $args Array of parameters for the widget.
	 * The 'type' parameter is required
	 * @throws NoSuchWidgetException
	 * @throws NoTypeGivenException
	 * @return Widget
	 */
	public function create( array $args ) {
		if ( ! isset( $args['type'] ) ) {
			throw new NoTypeGivenException();
		}

		$widgetClass = $this->repo->getInfo( $args['type'] );
		unset( $args['type'] );

		return $this->getWidget( $widgetClass, $args );
	}

	public function addFilter( ArgumentFilterInterface $filter ) {
		$this->filter->addFilter( $filter );
	}
}

class NoTypeGiven extends MWException {
	function __construct() {
		parent::__construct( "No widget name was provided in the configuration." );
	}
}
