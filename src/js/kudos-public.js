import $ from 'jquery';
import MicroModal from 'micromodal';
import { dom, library } from '@fortawesome/fontawesome-svg-core';
import { faChevronLeft } from '@fortawesome/free-solid-svg-icons';
import 'jquery-validation';
import '../img/logo-colour-40.png'; //used as email attachment
const { __ } = wp.i18n;
library.add( faChevronLeft );
dom.watch();

$( () => {
	'use strict';

	const $body = $( 'body' );
	const $kudosButtons = $( '.kudos_button_donate' );
	let animating = false;

	// Set validation defaults
	$.validator.setDefaults( {
		ignore: [],
		errorElement: 'small',
		rules: {
			value: {
				digits: true,
			},
		},
		messages: {
			name: {
				required: __( 'Your name is required', 'kudos-donations' ),
			},
			email_address: {
				required: __( 'Your email is required', 'kudos-donations' ),
				email: __( 'Please enter a valid email', 'kudos-donations' ),
			},
			value: {
				required: __(
					'Donation amount is required',
					'kudos-donations'
				),
				min: __( 'Minimum donation is 1 euro', 'kudos-donations' ),
				digits: __( 'Only digits are valid', 'kudos-donations' ),
			},
		},
	} );

	if ( $kudosButtons.length ) {
		// Setup button action
		$kudosButtons.each( function () {
			$( this ).click( function () {
				const $target = $( this ).data( 'target' );
				if ( $target ) {
					MicroModal.show( $target, {
						onShow( modal ) {
							$( modal )
								.find( '.kudos_error_message' )
								.text( '' );
							const $form = $( modal ).find( '.kudos_form' );
							if ( $form.length ) {
								$( 'fieldset.current-tab' ).removeClass(
									'current-tab'
								);
								$( 'fieldset:first-child' ).addClass(
									'current-tab'
								);
								$form.validate().resetForm();
								$form[ 0 ].reset();
							}
						},
						awaitCloseAnimation: true,
					} );
				}
			} );
		} );
	}

	// Show message modal if exists
	if ( $( '#kudos_modal-message' ).length ) {
		MicroModal.show( 'kudos_modal-message', {
			awaitCloseAnimation: true,
			awaitOpenAnimation: true,
		} );
	}

	// Multi step form navigation
	$( '.kudos_form_nav' ).on( 'click', function () {
		if ( animating ) return false;
		const $current_tab = $( this ).closest( '.form-tab' );
		const $modal = $( this ).closest( '.kudos_modal_container' );
		const direction = $( this ).data( 'direction' );
		const $inputs = $current_tab.find( ':input' );

		// Validate fields before proceeding
		if ( direction === 'next' ) {
			$inputs.validate();
			if ( ! $inputs.valid() ) {
				return;
			}
		}

		// Calculate next tab
		let $next_tab = $current_tab;
		let change = false;
		while ( ! change ) {
			$next_tab =
				direction === 'next' ? $next_tab.next() : $next_tab.prev();
			change = checkRequirements( $next_tab );
		}

		if ( $next_tab.hasClass( 'form-tab-final' ) ) {
			createSummary( $next_tab.find( '.kudos_summary' ) );
		}

		// Begin animation
		animating = true;
		const offset = 25;
		const duration = 150;
		$modal.animate(
			{ opacity: 0 },
			{
				step( now ) {
					const position = ( 1 - now ) * offset;
					$modal.css( {
						transform:
							'translateX(' +
							( direction === 'next' ? '-' : '' ) +
							position +
							'px)',
					} );
				},
				duration,
				easing: 'linear',
				complete() {
					$current_tab.removeClass( 'current-tab' );
					$next_tab.addClass( 'current-tab' );
					$modal.animate(
						{ opacity: 1 },
						{
							step( now ) {
								const position = ( 1 - now ) * offset;
								$modal.css( {
									transform:
										'translateX(' +
										( direction === 'next' ? '' : '-' ) +
										position +
										'px)',
								} );
							},
							duration,
							easing: 'linear',
							complete() {
								animating = false;
							},
						}
					);
				},
			}
		);
	} );

	// Check form before submit
	$body.on( 'click', '.kudos_submit', function ( e ) {
		e.preventDefault();
		const $form = $( this.form );
		$form.validate();
		if ( $form.valid() ) {
			$form.submit();
		}
	} );

	// Submit donation form action
	$body.on( 'submit', 'form.kudos_form', function ( e ) {
		e.preventDefault();
		const $kudosFormModal = $( this ).closest( '.kudos_form_modal' );
		const $kudosErrorMessage = $kudosFormModal.find(
			'.kudos_error_message'
		);

		$.ajax( {
			method: 'post',
			dataType: 'json',
			url: kudos.ajaxurl,
			data: {
				action: 'create_payment',
				form: $( e.currentTarget ).serialize(),
			},
			beforeSend() {
				$kudosFormModal.addClass( 'kudos_loading' );
			},
			success( result ) {
				console.log( result );
				if ( result.success ) {
					$( location ).attr( 'href', result.data );
				} else {
					$kudosErrorMessage.text( result.data.message );
					$kudosFormModal
						.removeClass( 'kudos_loading' )
						.addClass( 'error' );
				}
			},
			error( error ) {
				console.log( 'error', error );
			},
		} );
	} );
} );

// Checks the form tab data-requirements array against the current form values
function checkRequirements( $nextTab ) {
	const formValues = $( 'form.kudos_form' ).find( ':input' ).serializeArray();
	const requirements = $nextTab.data( 'requirements' );
	let result = true;
	if ( requirements ) {
		result = false;
		$nextTab.find( ':input' ).attr( 'disabled', 'disabled' );
		for ( const [ key, value ] of Object.entries( requirements ) ) {
			formValues.filter( function ( item ) {
				if ( item.name === key && value.includes( item.value ) ) {
					result = true;
					$nextTab.find( ':input' ).removeAttr( 'disabled' );
				}
			} );
		}
	}
	return result;
}

function createSummary() {
	const values = $( 'form.kudos_form' ).find( ':input' ).serializeArray();
	const name = values.find( ( i ) => i.name === 'name' ).value;
	const email = values.find( ( i ) => i.name === 'email_address' ).value;
	const value = values.find( ( i ) => i.name === 'value' ).value;
	const frequency = values.find( ( i ) => i.name === 'payment_frequency' )
		.value;
	const type =
		frequency === 'oneoff'
			? __( 'One-off', 'kudos-donations' )
			: __( 'Recurring', 'kudos-dontaions' );
	$( '#summary_name' ).text( name );
	$( '#summary_email' ).text( email );
	$( '#summary_value' ).text( value );
	$( '#summary_frequency' ).text( type );
}
