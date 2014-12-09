(function( $, mw ) {
$( function() {
	var $codeGroups = $( '.ooui-playground-code-group' );
	if ( $codeGroups.length ) {
		var $selector = $( '<div/>' )
				.addClass( 'ooui-playground-language-selector' ),
			options = [],
			selector;

		// XXX: Hardcoded
		$.each( ['javascript', 'php'], function( i, language ) {
			options.push( new OO.ui.ButtonOptionWidget( {
				data : language,
				label : language
			} ) );
		} );

		selector = new OO.ui.ButtonSelectWidget( {
			'items' : options
		} );

		$selector.append( selector.$element );
		$selector.prependTo( $( '#mw-content-text' ) );

		selector.on( 'select', function( item ) {
			var selectedLanguage = item.getData();

			$( '.ooui-playground-code' ).hide();
			$( '.ooui-playground-code-' + selectedLanguage ).show();
		} );

		selector.selectItem( options[0] );
	}
} );
})( jQuery, mediaWiki );