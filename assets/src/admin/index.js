/**
 * The admin app of Keywords to Links Converter: links, link editor and settings.
 */
import { createRoot } from '@wordpress/element';
import domReady from '@wordpress/dom-ready';

import App from './App';
import './style.scss';

domReady( () => {
	const root = document.getElementById( 'lar-admin' );
	if ( root ) {
		createRoot( root ).render( <App /> );
	}
} );
