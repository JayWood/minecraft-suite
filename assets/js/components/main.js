/**
 * Minecraft Suite
 * http://plugish.com
 *
 * Licensed under the GPLv2+ license.
 */

window.Minecraft_Suite = window.Minecraft_Suite || {};

( function( window, document, $, that, undefined ) {
	'use strict';

	var $c   = {};

	that.cache = function() {
		$c.window           = $( window );
		$c.body             = $( document.body );
		$c.application_form = $( '#ms-application-form' );
		$c.submit_btn       = $c.application_form.find( '.mcs-submit' );
		$c.msg_box          = $c.application_form.find( '.status_message' );
	};

	that.bindEvents = function() {

		$c.body.on( 'click', '.mcs-submit', that.process_form );

	};
	that.init = function() {
		that.cache();
		that.bindEvents();
	};


	that.process_form = function( evt ) {
		evt.preventDefault();
		var serialized_data = $c.application_form.serialize();

		$c.application_form.find( 'input, textarea' ).prop( 'disabled', true );

		$.ajax({
			url: window.mc_l10n.ajaxurl,
			data: serialized_data,
			dataType: 'json',
			method: 'POST'
		} ).done( function( response ) {
			$c.application_form.find( 'input, textarea' ).prop( 'disabled', false );
			if ( ! response.success ) {
				$c.msg_box.removeClass( 'success' ).addClass( 'error' ).text( response.data );
			} else {
				$c.msg_box.removeClass( 'error' ).addClass( 'success' ).text( response.data );
				$c.application_form.find( 'input' ).not('.button-primary' ).attr( 'value', '' );
				$c.application_form.find( 'textarea' ).val( '' );
			}
		} );

	};

	$( document ).ready( that.init );

} ) ( window, document, jQuery, Minecraft_Suite );
