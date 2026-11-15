/**
 * The views of the app are in the URL, so they can be bookmarked and the back button works.
 */
import { useEffect, useState } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';

const { adminUrl, slug } = window.larAdmin;

const EVENT = 'lar-navigate';

/**
 * The URL of a view.
 *
 * @param {string} view `links`, `edit` or `settings`.
 * @param {number} id   The link, for `edit`.
 * @return {string} The URL.
 */
export function viewUrl( view, id = 0 ) {
	if ( view === 'settings' ) {
		return addQueryArgs( adminUrl, { page: `${ slug }-${ view }` } );
	}
	if ( view === 'edit' && ! id ) {
		return addQueryArgs( adminUrl, { page: `${ slug }-new` } );
	}
	if ( view === 'links' ) {
		return addQueryArgs( adminUrl, { page: slug } );
	}
	return addQueryArgs( adminUrl, { page: slug, view, id } );
}

/**
 * Open a view without reloading the page.
 *
 * @param {string}  view    The view.
 * @param {number}  id      The link.
 * @param {boolean} replace Replace the current history entry.
 */
export function navigate( view, id = 0, replace = false ) {
	const state = { view, id };
	window.history[ replace ? 'replaceState' : 'pushState' ](
		state,
		'',
		viewUrl( view, id )
	);
	window.dispatchEvent( new window.CustomEvent( EVENT, { detail: state } ) );
	window.scrollTo( 0, 0 );
}

/**
 * The current view.
 *
 * @return {{view: string, id: number}} The view and the link.
 */
export function useRoute() {
	const [ route, setRoute ] = useState( () => ( {
		view: window.larAdmin.view,
		id: window.larAdmin.linkId,
	} ) );

	useEffect( () => {
		const onNavigate = ( event ) => setRoute( event.detail );
		const onPopState = ( event ) =>
			setRoute(
				event.state || {
					view: window.larAdmin.view,
					id: window.larAdmin.linkId,
				}
			);
		window.addEventListener( EVENT, onNavigate );
		window.addEventListener( 'popstate', onPopState );
		return () => {
			window.removeEventListener( EVENT, onNavigate );
			window.removeEventListener( 'popstate', onPopState );
		};
	}, [] );

	return route;
}

/**
 * A link to a view, opened without reloading the page.
 *
 * @param {Object}  props          The props.
 * @param {string}  props.view     The view.
 * @param {number}  props.id       The link.
 * @param {Element} props.children The content.
 * @return {Element} The link.
 */
export function ViewLink( { view, id = 0, children, ...props } ) {
	return (
		<a
			{ ...props }
			href={ viewUrl( view, id ) }
			onClick={ ( event ) => {
				if (
					event.button !== 0 ||
					event.metaKey ||
					event.ctrlKey ||
					event.shiftKey
				) {
					return;
				}
				event.preventDefault();
				navigate( view, id );
			} }
		>
			{ children }
		</a>
	);
}
