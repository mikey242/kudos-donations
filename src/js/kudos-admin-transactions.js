import $ from 'jquery';
import { dom, library } from '@fortawesome/fontawesome-svg-core';
import { faCreditCard } from '@fortawesome/free-solid-svg-icons';
import { faFilePdf } from '@fortawesome/free-regular-svg-icons';
import { faIdeal, faPaypal } from '@fortawesome/free-brands-svg-icons';

library.add( faCreditCard, faIdeal, faPaypal, faFilePdf );
dom.watch();

$( () => {
	'use strict';
	$( '.row-actions .delete' ).click( function ( e ) {
		if ( ! confirm( window.kudos.confirmation ) ) {
			e.preventDefault();
		}
	} );
} );
