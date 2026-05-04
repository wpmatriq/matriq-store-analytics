/**
 * Sales Pulse v2 API Client.
 *
 * Thin wrapper around @wordpress/api-fetch for v2 REST endpoints.
 * All endpoints use the `sales-pulse/v2` namespace.
 */
import apiFetch from '@wordpress/api-fetch';

const API_NAMESPACE = '/sales-pulse/v2';

/**
 * Generic GET request.
 *
 * @param {string} endpoint - REST endpoint path (e.g., '/overview').
 * @param {Object} params   - Query parameters.
 * @return {Promise<Object>} Response data.
 */
export async function apiGet( endpoint, params = {} ) {
	const queryString = new URLSearchParams( params ).toString();
	const path = `${ API_NAMESPACE }${ endpoint }${ queryString ? `?${ queryString }` : '' }`;

	const response = await apiFetch( { path } );
	return response?.data ?? response;
}

/**
 * Generic POST request.
 *
 * @param {string} endpoint - REST endpoint path.
 * @param {Object} body     - Request body (JSON).
 * @return {Promise<Object>} Response data.
 */
export async function apiPost( endpoint, body = {} ) {
	const response = await apiFetch( {
		path: `${ API_NAMESPACE }${ endpoint }`,
		method: 'POST',
		data: body,
	} );
	return response?.data ?? response;
}

/**
 * Generic DELETE request.
 *
 * @param {string} endpoint - REST endpoint path.
 * @return {Promise<Object>} Response data.
 */
export async function apiDelete( endpoint ) {
	const response = await apiFetch( {
		path: `${ API_NAMESPACE }${ endpoint }`,
		method: 'DELETE',
	} );
	return response?.data ?? response;
}

// --- Typed endpoint helpers ---

export const overviewApi = {
	get: ( period = 'daily' ) => apiGet( '/overview', { period } ),
	trend: ( days = 7 ) => apiGet( '/overview/trend', { days } ),
};

export const historyApi = {
	list: ( page = 1, perPage = 10 ) => apiGet( '/history', { page, per_page: perPage } ),
};

export const campaignsApi = {
	list: () => apiGet( '/campaigns' ),
	create: ( data ) => apiPost( '/campaigns', data ),
	end: ( id ) => apiPost( `/campaigns/${ id }/end` ),
	remove: ( id ) => apiDelete( `/campaigns/${ id }` ),
};

export const settingsApi = {
	get: () => apiGet( '/settings' ),
	update: ( data ) => apiPost( '/settings', data ),
};

export const systemApi = {
	readiness: () => apiGet( '/system/readiness' ),
	triggerSnapshot: ( params ) => apiPost( '/system/snapshot', params || {} ),
	backfillStatus: () => apiGet( '/system/backfill' ),
	triggerBackfill: () => apiPost( '/system/backfill' ),
	sendTestDigest: ( recipient ) =>
		apiPost( '/system/digest/test', recipient ? { recipient } : {} ),
};
