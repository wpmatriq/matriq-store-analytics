import { dispatch, select } from '@wordpress/data';
import { STORE_NAME } from '@Store/constants';

const updateOnboardingData = ( newData ) => {
	const existingOnboardingData =
		select( STORE_NAME ).getState().onboardingData || {};

	// Merge the new data with existing data
	const updatedData = { ...existingOnboardingData, ...newData };

	dispatch( STORE_NAME ).updateStoreData( {
		onboardingData: updatedData,
	} );
};

export default updateOnboardingData;
