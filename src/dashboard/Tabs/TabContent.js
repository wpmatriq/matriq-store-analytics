import { useEffect } from 'react';
import HomePage from '@Tabs/Dashboard/HomePage';
import SettingsPage from '@Tabs/Settings/SettingsPage';
import { useDispatch, useSelect } from '@wordpress/data';
import { STORE_NAME } from '@Store/constants';
import { __ } from '@wordpress/i18n';

const useDashboardStates = () => {
	const { navigateTo, setLoading } = useDispatch( STORE_NAME );
	const { activeTab, loading } = useSelect( ( select ) => {
		const { getActiveTab } = select( STORE_NAME );
		const { getLoading } = select( STORE_NAME );
		return {
			activeTab: getActiveTab(),
			loading: getLoading(),
		};
	} );

	const getParamsFromURL = () => {
		const url = new URL( window.location.href );
		return Object.fromEntries( url.searchParams.entries() );
	};

	// First effect for initial load.
	useEffect( () => {
		setLoading( true );
		navigateTo( getParamsFromURL() );
		setTimeout( () => {
			setLoading( false );
		}, 1000 ); // Simulate a delay for loading
	}, [] );

	// Second effect for URL changes.
	// This will be triggered when the user navigates using the back/forward buttons.
	useEffect( () => {
		const handlePopState = () => {
			setLoading( true );
			navigateTo( getParamsFromURL() );
			setTimeout( () => {
				setLoading( false );
			}, 1000 ); // Simulate a delay for loading
		};

		window.addEventListener( 'popstate', handlePopState );
		return () => {
			window.removeEventListener( 'popstate', handlePopState );
		};
	}, [ navigateTo ] );

	const renderActiveTab = ( tab ) => {
		switch ( tab ) {
			case 'home':
				return <HomePage />;
			case 'settings':
				return <SettingsPage />;
			default:
				return (
					<div className="w-full h-[calc(100vh-32px)] flex flex-col gap-2">
						{ __( '404 - Not Found!', 'suredash' ) }
					</div>
				);
		}
	};

	return {
		activeTab,
		renderActiveTab,
		loading,
	};
};

const TabContent = () => {
	const { activeTab, renderActiveTab } = useDashboardStates();

	return (
		<div
			className="h-full"
		>
			<div className="w-full">
				{ renderActiveTab( activeTab ) }
			</div>
		</div>
	);
};

export default TabContent;
