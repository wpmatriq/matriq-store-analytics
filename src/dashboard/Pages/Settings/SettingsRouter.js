/**
 * SettingsRouter - top-level shell for the Settings tab.
 *
 * Wraps the free `SettingsPage` (the "general" sub-tab) and any Pro-registered
 * sub-tabs from `window.salesPulse.settingsSubtabs`. When no Pro is active the
 * registry is empty and we render `SettingsPage` flat - preserving the
 * existing free-only experience without introducing a sub-tab strip the user
 * has nowhere to navigate to.
 *
 * URL pattern: `?tab=settings&stab=<id>`. Sub-tab state survives reloads and
 * deep-links (e.g. the chat drawer's "Open Settings" button hands the
 * merchant straight to `?stab=copilot`).
 *
 * @package
 */
import React, { useEffect, useState } from 'react';
import { __ } from '@wordpress/i18n';
import classnames from '@Utils/classnames';
import SettingsPage from './SettingsPage';

/**
 * Sub-tab descriptor for the General (free) sub-tab. Pro-registered
 * sub-tabs come in via `window.salesPulse.settingsSubtabs` and are merged
 * after this entry, sorted by `weight`.
 *
 * @type {{id: string, label: string, weight: number, component: any}}
 */
const GENERAL_SUBTAB = {
	id: 'general',
	label: __( 'General', 'sales-pulse' ),
	weight: 0,
	component: SettingsPage,
};

/**
 * Read the current sub-tab id from the URL query string. Falls back to
 * `general` when no `?stab=` is present or the requested id isn't registered.
 *
 * @param {Array<{id: string}>} subtabs Available sub-tab descriptors.
 * @return {string} Active sub-tab id.
 */
function readActiveSubtab( subtabs ) {
	if ( typeof window === 'undefined' ) {
		return 'general';
	}
	const params = new URLSearchParams( window.location.search );
	const requested = params.get( 'stab' );
	const ids = subtabs.map( ( s ) => s.id );
	return ids.indexOf( requested ) !== -1 ? requested : 'general';
}

/**
 * Sync the active sub-tab into `?stab=` without triggering a reload. Skips
 * when the URL already matches.
 *
 * @param {string} id Sub-tab id to write.
 * @return {void}
 */
function setActiveSubtab( id ) {
	if ( typeof window === 'undefined' || ! window.history?.replaceState ) {
		return;
	}
	const url = new URL( window.location.href );
	if ( url.searchParams.get( 'stab' ) === id ) {
		return;
	}
	if ( id === 'general' ) {
		url.searchParams.delete( 'stab' );
	} else {
		url.searchParams.set( 'stab', id );
	}
	window.history.replaceState( {}, '', url.toString() );
}

/**
 * Snapshot the live sub-tab registry into an ordered list.
 *
 * @return {Array<object>} Sub-tab descriptors, General first then Pro-registered.
 */
function snapshotSubtabs() {
	if ( typeof window === 'undefined' ) {
		return [ GENERAL_SUBTAB ];
	}
	const registered = Object.values( window.salesPulse?.settingsSubtabs || {} )
		.filter( ( entry ) => entry && entry.id && entry.component )
		.sort( ( a, b ) => ( a.weight || 0 ) - ( b.weight || 0 ) );
	return [ GENERAL_SUBTAB, ...registered ];
}

/**
 * SettingsRouter - the routed component for `?tab=settings`.
 *
 * @return {JSX.Element} Sub-tab strip + active sub-tab component.
 */
export default function SettingsRouter() {
	// Pro bundles often load AFTER the free shell mounts, so the registry
	// can grow during the lifetime of this component. Re-read it whenever
	// `salespulse:settings-subtab-registered` fires.
	const [ subtabs, setSubtabs ] = useState( () => snapshotSubtabs() );
	const [ active, setActive ] = useState( () => readActiveSubtab( snapshotSubtabs() ) );

	useEffect( () => {
		if ( typeof window === 'undefined' ) {
			return undefined;
		}
		const handler = () => {
			const next = snapshotSubtabs();
			setSubtabs( next );
			// Re-resolve the active sub-tab in case the URL points at one
			// that just registered.
			setActive( readActiveSubtab( next ) );
		};
		window.addEventListener( 'salespulse:settings-subtab-registered', handler );
		return () => {
			window.removeEventListener( 'salespulse:settings-subtab-registered', handler );
		};
	}, [] );

	useEffect( () => {
		setActiveSubtab( active );
	}, [ active ] );

	// Free-only mode: just render the General page flat. No tab strip when
	// there's nothing to navigate between.
	if ( subtabs.length <= 1 ) {
		return <SettingsPage />;
	}

	const activeDescriptor = subtabs.find( ( s ) => s.id === active ) || subtabs[ 0 ];
	const ActiveComponent = activeDescriptor.component;

	return (
		<div className="space-y-4">
			<div
				role="tablist"
				aria-label={ __( 'Settings sections', 'sales-pulse' ) }
				className="inline-flex items-center gap-1 rounded-full border border-solid border-border bg-card p-1 shadow-xs"
			>
				{ subtabs.map( ( tab ) => {
					const isActive = tab.id === active;
					return (
						<button
							key={ tab.id }
							type="button"
							role="tab"
							aria-selected={ isActive }
							onClick={ () => setActive( tab.id ) }
							className={ classnames(
								'inline-flex cursor-pointer items-center rounded-full px-3.5 py-1.5 text-sm font-medium transition-all',
								isActive
									? 'bg-primary text-primary-foreground shadow-sm'
									: 'text-muted-foreground hover:text-foreground'
							) }
						>
							{ tab.label }
						</button>
					);
				} ) }
			</div>

			<ActiveComponent />
		</div>
	);
}
