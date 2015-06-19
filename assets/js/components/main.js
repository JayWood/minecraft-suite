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

	that.init = function() {
		that.cache();
		that.bindEvents();
	};

	that.cache = function() {
		$c.window = $( window );
		$c.body   = $( document.body );
	};

	that.bindEvents = function() {
	};

	$( that.init );

} ) ( window, document, jQuery, Minecraft_Suite );
