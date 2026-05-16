/**
 * Matriq Store Analytics v2 - App shell.
 *
 * Provides QueryClient, PulseShell layout, ErrorBoundary, ReadinessGate,
 * and tab-based routing driven by `?tab=` query parameter.
 *
 * Premium extensions register additional tabs via the `window.matriqMSA.registerTab`
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
import SettingsRouter from '@DashboardApp/pages/Settings/SettingsRouter';
import {
	CopilotUpgradeDrawer,
	CopilotUpgradeTrigger,
} from '@Components/CopilotUpgrade';

const BUILT_IN_TABS = [ 'overview', 'history', 'campaigns', 'settings' ];

// Public extension slot. Defensively initialised so Pro bundles loading either
// before or after this file can register without timing concerns.
if ( typeof window !== 'undefined' ) {
	window.matriqMSA = window.matriqMSA || {};
	window.matriqMSA.tabs = window.matriqMSA.tabs || {};
	window.matriqMSA.slots = window.matriqMSA.slots || {};
	window.matriqMSA.settingsSubtabs = window.matriqMSA.settingsSubtabs || {};

	if ( typeof window.matriqMSA.registerTab !== 'function' ) {
		window.matriqMSA.registerTab = function ( entry ) {
			if ( ! entry || ! entry.id || ! entry.component ) {
				return false;
			}
			if ( BUILT_IN_TABS.indexOf( entry.id ) !== -1 ) {
				return false;
			}
			window.matriqMSA.tabs[ entry.id ] = {
				id: entry.id,
				label: entry.label || entry.id,
				component: entry.component,
			};
			return true;
		};
	}

	// Settings sub-tab registry (Option C). Pro bundles register additional
	// sub-tabs (AI / Privacy / Alerts / Licence) here; the free shell renders
	// `general` flat when this registry is empty so no Pro = no sub-tab strip.
	// `weight` controls render order (lower = earlier); `id` is the URL slug
	// surfaced via the `?stab=` query param.
	if ( typeof window.matriqMSA.registerSettingsSubtab !== 'function' ) {
		window.matriqMSA.registerSettingsSubtab = function ( entry ) {
			if ( ! entry || ! entry.id || ! entry.component ) {
				return false;
			}
			if ( entry.id === 'general' ) {
				return false; // `general` is reserved for the free sections.
			}
			window.matriqMSA.settingsSubtabs[ entry.id ] = {
				id: entry.id,
				label: entry.label || entry.id,
				component: entry.component,
				weight: typeof entry.weight === 'number' ? entry.weight : 0,
			};
			window.dispatchEvent(
				new CustomEvent( 'matriq_msa:settings-subtab-registered', {
					detail: { id: entry.id },
				} )
			);
			return true;
		};
	}

	// Inline component slot registry (Phase 0.1). Premium extensions register
	// components into named slots that the host pages render via <PluginSlot>.
	// A custom `matriq_msa:slot-registered` event fires after each successful
	// registration so PluginSlot can re-render — Pro bundles often load AFTER
	// the host shell has already mounted, so a one-shot read at first render
	// would miss late registrations.
	if ( typeof window.matriqMSA.registerSlot !== 'function' ) {
		window.matriqMSA.registerSlot = function ( name, entry ) {
			if ( ! name || ! entry || ! entry.id || ! entry.component ) {
				return false;
			}
			const list = window.matriqMSA.slots[ name ] = window.matriqMSA.slots[ name ] || [];
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
				new CustomEvent( 'matriq_msa:slot-registered', { detail: { name, id: entry.id } } )
			);
			return true;
		};
	}

	// Free-side "Ask Copilot" trigger + upgrade drawer. Both components
	// self-gate on Pro presence, so when Store Copilot is installed and
	// active these registrations stay in the slot list but render null —
	// Pro's ChatTrigger/ChatDrawer take over the same slot positions.
	// Registering here means free merchants see the chip immediately on
	// first paint, with no Pro-bundle dependency.
	window.matriqMSA.registerSlot( 'header-action', {
		id: 'matriq-store-analytics-copilot-upgrade-trigger',
		component: CopilotUpgradeTrigger,
		weight: 10,
	} );
	window.matriqMSA.registerSlot( 'app-overlay', {
		id: 'matriq-store-analytics-copilot-upgrade-drawer',
		component: CopilotUpgradeDrawer,
		weight: 0,
	} );
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
 * Route to the correct page component for the requested tab.
 *
 * @param {Object} props     Component props.
 * @param {string} props.tab Active tab slug from the URL.
 * @return {JSX.Element} The mounted page component.
 */
function PageRouter( { tab } ) {
	switch ( tab ) {
		case 'history':
			return <HistoryPage />;
		case 'campaigns':
			return <CampaignsPage />;
		case 'settings':
			return <SettingsRouter />;
		case 'overview':
			return <OverviewPage />;
		case 'impact': {
			// "Soft built-in": Pro can register a richer Impact component via
			// window.matriqMSA.registerTab. When present, prefer it over the
			// free data-foundation surface so Pro merchants see attribution
			// numbers instead.
			const proImpact = window?.matriqMSA?.tabs?.impact;
			if ( proImpact && proImpact.component ) {
				const ProComponent = proImpact.component;
				return <ProComponent />;
			}
			return <ImpactPage />;
		}
		default: {
			const registered = window?.matriqMSA?.tabs?.[ tab ];
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
