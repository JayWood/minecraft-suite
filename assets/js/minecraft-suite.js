/**
 * Minecraft Suite - v0.1.0 - 2016-02-22
 * http://plugish.com
 *
 * Copyright (c) 2016;
 * Licensed GPLv2+
 */
window.Minecraft_Suite = window.Minecraft_Suite || {};

( function( window, document, $, that, undefined ) {
	'use strict';

	var $c   = {};

	that.cache = function() {
		$c.window           = $( window );
		$c.body             = $( document.body );
		$c.application_form = $( '#ms-application-form' );
		$c.submit_btn       = $( '.mcs-submit' );
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
		var form_data = $c.application_form.serialize();// log it

		$.ajax({
			url: window.mc_l10n.ajaxurl,
			data: $c.application_form.serialize(),
			dataType: 'json',
			method: 'POST'
		} ).done( function( response ) {
			window.console.log( response );
		});

	};// test

	$( document ).ready( that.init );

} ) ( window, document, jQuery, Minecraft_Suite );
