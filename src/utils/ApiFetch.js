import apiFetch from '@wordpress/api-fetch';

export default async function ApiFetch(
	data,
	formMethod = 'POST',
	setAjaxError
) {
	apiFetch( {
		url: matriqMSAData?.ajax_url,
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
