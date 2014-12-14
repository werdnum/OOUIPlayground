<?php

namespace OOUI;

class MockWidget extends Widget {
	public $config;

	/**
	 * @param string[]|bool $config['testparam'] A test parameter
	 */
	function __construct( array $config ) {
		$this->config = $config;
	}
}

class MockGroupWidget extends MockWidget {
	public function __construct( array $config ) {
		parent::__construct( $config );

		$this->mixin( new GroupElement( $this, array_merge( $config, array( 'group' => $this ) ) ) );
		$this->mixin( new LabelElement( $this, $config ) );
	}
}
