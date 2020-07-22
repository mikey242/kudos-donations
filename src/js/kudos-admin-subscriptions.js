import $ from 'jquery';

$( () => {
	'use strict';
	$( '.row-actions .cancel' ).click( function ( e ) {
		// eslint-disable-next-line no-alert,no-undef
		if ( ! confirm( window.kudos.confirmation ) ) {
			e.preventDefault();
		}
	} );
} );
