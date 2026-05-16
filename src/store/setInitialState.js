import apiFetch from '@wordpress/api-fetch';
import { dispatch } from '@wordpress/data';
import { store } from '@Store/setup';

const setInitialState = () => {
	apiFetch( { path: '/matriq-store-analytics/v1/dataset' } ).then( ( response ) => {
		response.isLoaded = true;
		dispatch( store ).updateStoreData( response );

		dispatch( store ).updateOnboardingData( response.onboardingData );

		// Fire custom event to load the data after 2 seconds.
		setTimeout( () => {
			const event = new Event( 'matriq_msa_app_loaded' );
			window.dispatchEvent( event );
		}, 2000 );
	} );
};

export default setInitialState;
