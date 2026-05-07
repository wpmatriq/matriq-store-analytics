/**
 * Sales Pulse v2 - App shell.
 *
 * Provides QueryClient, PulseShell layout, ErrorBoundary, ReadinessGate,
 * and tab-based routing driven by `?tab=` query parameter.
 *
 * Premium extensions register additional tabs via the `window.salesPulse.registerTab`
 * slot exposed below. Built-in tabs (overview, history, campaigns, settings)
 * cannot be overridden.
 */
import React from 'react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { ReadinessGate } from '@Components/ReadinessGate';
import ErrorBoundary from '@Components/ErrorBoundary';
import { PluginSlot } from '@Components/PluginSlot';
import { PulseShell } from '@Components/pulse/PulseShell';
import OverviewPage from '@DashboardApp/pages/Overview/OverviewPage';
import HistoryPage from '@DashboardApp/pages/History/HistoryPage';
import CampaignsPage from '@DashboardApp/pages/Campaigns/CampaignsPage';
import ImpactPage from '@DashboardApp/pages/Impact/ImpactPage';
import SettingsPage from '@DashboardApp/pages/Settings/SettingsPage';

const BUILT_IN_TABS = [ 'overview', 'history', 'campaigns', 'settings' ];

// Public extension slot. Defensively initialised so Pro bundles loading either
// before or after this file can register without timing concerns.
if ( typeof window !== 'undefined' ) {
	window.salesPulse = window.salesPulse || {};
	window.salesPulse.tabs = window.salesPulse.tabs || {};
	window.salesPulse.slots = window.salesPulse.slots || {};

	if ( typeof window.salesPulse.registerTab !== 'function' ) {
		window.salesPulse.registerTab = function ( entry ) {
			if ( ! entry || ! entry.id || ! entry.component ) {
				return false;
			}
			if ( BUILT_IN_TABS.indexOf( entry.id ) !== -1 ) {
				return false;
			}
			window.salesPulse.tabs[ entry.id ] = {
				id: entry.id,
				label: entry.label || entry.id,
				component: entry.component,
			};
			return true;
		};
	}

	// Inline component slot registry (Phase 0.1). Premium extensions register
	// components into named slots that the host pages render via <PluginSlot>.
	// A custom `salespulse:slot-registered` event fires after each successful
	// registration so PluginSlot can re-render — Pro bundles often load AFTER
	// the host shell has already mounted, so a one-shot read at first render
	// would miss late registrations.
	if ( typeof window.salesPulse.registerSlot !== 'function' ) {
		window.salesPulse.registerSlot = function ( name, entry ) {
			if ( ! name || ! entry || ! entry.id || ! entry.component ) {
				return false;
			}
			const list = window.salesPulse.slots[ name ] = window.salesPulse.slots[ name ] || [];
			if ( list.some( ( e ) => e.id === entry.id ) ) {
				return false; // idempotent on re-register
			}
			list.push( {
				id: entry.id,
				component: entry.component,
				weight: typeof entry.weight === 'number' ? entry.weight : 0,
			} );
			list.sort( ( a, b ) => a.weight - b.weight );

			window.dispatchEvent(
				new CustomEvent( 'salespulse:slot-registered', { detail: { name, id: entry.id } } )
			);
			return true;
		};
	}
}

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
 * @return {string} Current tab slug (overview, history, campaigns, settings, or a registered tab).
 */
function getActiveTab() {
	const params = new URLSearchParams( window.location.search );
	return params.get( 'tab' ) || 'overview';
}

/**
 * Route to the correct page component.
 * @param root0
 * @param root0.tab
 */
function PageRouter( { tab } ) {
	switch ( tab ) {
		case 'history':
			return <HistoryPage />;
		case 'campaigns':
			return <CampaignsPage />;
		case 'settings':
			return <SettingsPage />;
		case 'overview':
			return <OverviewPage />;
		case 'impact': {
			// "Soft built-in": Pro can register a richer Impact component via
			// window.salesPulse.registerTab. When present, prefer it over the
			// free data-foundation surface so Pro merchants see attribution
			// numbers instead.
			const proImpact = window?.salesPulse?.tabs?.impact;
			if ( proImpact && proImpact.component ) {
				const ProComponent = proImpact.component;
				return <ProComponent />;
			}
			return <ImpactPage />;
		}
		default: {
			const registered = window?.salesPulse?.tabs?.[ tab ];
			if ( registered && registered.component ) {
				const Component = registered.component;
				return <Component />;
			}
			return <OverviewPage />;
		}
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
			<PluginSlot name="app-overlay" props={ { activeTab } } />
		</QueryClientProvider>
	);
}
