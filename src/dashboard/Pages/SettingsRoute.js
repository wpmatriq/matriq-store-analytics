import React from 'react';
import Onboarding from '@DashboardApp/Pages/Onboarding';
import TabContent from '@DashboardApp/Tabs/TabContent';
import TopHeader from '@Components/TopHeader';
import { useSelect } from '@wordpress/data';
import { STORE_NAME } from '@Store/constants';

// If any routing needs to be there, then that can manage from here.
function SettingsRoute() {
	const urlParams = new URLSearchParams( window.location.search );
	const page = urlParams.get( 'page' );

	const { showTopBar } = useSelect( ( select ) => {
		const { getShowTopBar } = select( STORE_NAME );
		return {
			showTopBar: getShowTopBar(),
		};
	} );

	if ( page === 'wc-sma-onboarding' ) {
		return <Onboarding />;
	}

	return (
		<>
			{ showTopBar && <TopHeader /> } <TabContent />
		</>
	);
}

export default SettingsRoute;
