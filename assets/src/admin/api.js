/**
 * The plugin's REST API (affiliate-butler/v1).
 */
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

const { restPath } = window.larAdmin;

/**
 * An error of the API, with the field errors of a link when there are some.
 */
export class ApiError extends Error {
	constructor( message, fields = {} ) {
		super( message );
		this.fields = fields;
	}
}

/**
 * Turn what apiFetch rejects with into an ApiError.
 *
 * @param {Object|Response} error The error.
 * @return {Promise<ApiError>} The error.
 */
async function toApiError( error ) {
	let data = error;
	if ( typeof Response !== 'undefined' && error instanceof Response ) {
		try {
			data = await error.json();
		} catch {
			data = {};
		}
	}
	return new ApiError(
		data?.message || String( error ),
		data?.data?.errors || {}
	);
}

/**
 * Call the API.
 *
 * @param {string} path    The route, without the namespace.
 * @param {Object} options The apiFetch options.
 * @return {Promise<Object>} The response data.
 */
export async function request( path, options = {} ) {
	try {
		return await apiFetch( { path: restPath + path, ...options } );
	} catch ( error ) {
		throw await toApiError( error );
	}
}

/**
 * Get a page of links.
 *
 * @param {Object} query The `page`, `per_page`, `search` and `status`.
 * @return {Promise<{items: Array, total: number, totalPages: number}>} The links.
 */
export async function fetchLinks( query ) {
	try {
		const response = await apiFetch( {
			path: addQueryArgs( restPath + '/links', query ),
			parse: false,
		} );
		return {
			items: await response.json(),
			total: Number( response.headers.get( 'X-WP-Total' ) || 0 ),
			totalPages: Number(
				response.headers.get( 'X-WP-TotalPages' ) || 0
			),
		};
	} catch ( error ) {
		throw await toApiError( error );
	}
}
