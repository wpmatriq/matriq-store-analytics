/**
 * Sales Pulse v2 — App shell.
 *
 * Provides QueryClient, PulseShell layout, ErrorBoundary, ReadinessGate,
 * and tab-based routing driven by `?tab=` query parameter.
 */
import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReadinessGate } from '@Components/ReadinessGate';
import ErrorBoundary from '@Components/ErrorBoundary';
import { PulseShell } from '@Components/pulse/PulseShell';
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
			<PulseShell activeTab={ activeTab }>
				<ErrorBoundary>
					<ReadinessGate>
						<PageRouter tab={ activeTab } />
					</ReadinessGate>
				</ErrorBoundary>
			</PulseShell>
		</QueryClientProvider>
	);
}
