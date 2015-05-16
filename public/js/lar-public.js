(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note that this assume you're going to use jQuery, so it prepares
	 * the $ function reference to be used within the scope of this
	 * function.
	 *
	 * From here, you're able to define handlers for when the DOM is
	 * ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * Or when the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and so on.
	 *
	 * Remember that ideally, we should not attach any more than a single DOM-ready or window-load handler
	 * for any particular page. Though other scripts in WordPress core, other plugins, and other themes may
	 * be doing this, we should try to minimize doing that in our own work.
	 */

	 jQuery(function() {
	 		jQuery('.lar_link').click(function(e){
	 			var link = jQuery(this);
	 			e.preventDefault();
	 			var data = {
		                        action: 'store_stats',
		                        security: lar_stats_nonce,
		                        link_id: link.attr('data-linkid')
		                    };
		                    jQuery.post(ajaxurl, data, function(response) {
		                    	window.location = link.attr('href');
		                    });
	 		});
	 });




})( jQuery );
