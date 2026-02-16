/**
 * Sales Pulse v2 — App shell.
 *
 * Provides QueryClient, ReadinessGate, routing, and top-level layout.
 */
import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReadinessGate } from '@Components/ReadinessGate';
import TopHeader from '@Components/TopHeader';
import OverviewPage from '@DashboardApp/pages/Overview/OverviewPage';
import HistoryPage from '@DashboardApp/pages/History/HistoryPage';
import CampaignsPage from '@DashboardApp/pages/Campaigns/CampaignsPage';
import SettingsPage from '@DashboardApp/pages/Settings/SettingsPage';

const queryClient = new QueryClient( {
	defaultOptions: {
		queries: {
			refetchOnWindowFocus: false,
			retry: 1,
			staleTime: 5 * 60 * 1000, // 5 minutes.
		},
	},
} );

/**
 * Read the active tab from the URL query string.
 *
 * @return {string} Current tab slug (overview, history, campaigns, settings).
 */
function getActiveTab() {
	const params = new URLSearchParams( window.location.search );
	return params.get( 'tab' ) || 'overview';
}

/**
 * Route to the correct page component.
 */
function PageRouter( { tab } ) {
	switch ( tab ) {
		case 'history':
			return <HistoryPage />;
		case 'campaigns':
			return <CampaignsPage />;
		case 'settings':
			return <SettingsPage />;
		default:
			return <OverviewPage />;
	}
}

export default function App() {
	const activeTab = getActiveTab();

	return (
		<QueryClientProvider client={ queryClient }>
			<div className="wc-sma-application-main-container">
				<TopHeader activeTab={ activeTab } />
				<div className="px-4 py-6 max-w-7xl mx-auto">
					<ReadinessGate>
						<PageRouter tab={ activeTab } />
					</ReadinessGate>
				</div>
			</div>
		</QueryClientProvider>
	);
}
