import apiFetch from '@wordpress/api-fetch';

export default async function ApiFetch(
	data,
	formMethod = 'POST',
	setAjaxError
) {
	apiFetch( {
		url: wc_sma_admin_data?.ajax_url,
		method: formMethod,
		body: data,
	} )
		.then( ( response ) => {
			return response;
		} )
		.catch( ( error ) => {
			console.error( error );
			setAjaxError( error );
		} );
}
