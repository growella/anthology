/**
 * Scripting for Anthology's admin UI.
 *
 * @package Anthology
 * @author  Growella
 */

jQuery( function ( $ ) {
	'use strict';

	$( '.anthology-series-order-list > tbody' ).sortable( {
		items: 'tr',
	} );
} );
