/**
 * PluginSlot - inline component slot for premium extensions.
 *
 * Renders any components registered into the named slot via
 * `window.salesPulse.registerSlot( name, { id, component, weight } )`.
 *
 * Components registered into the same slot render in weight-ascending order
 * (lower weight = higher in the DOM). Each registered component receives
 * the `props` object passed to PluginSlot - typically the page's data so
 * the slot component can render its inline UI without making its own request.
 *
 * Returns `null` when no entries are registered, so the host page's layout
 * is unaffected by the empty slot.
 */
import React from 'react';

export function PluginSlot( { name, props } ) {
	const entries = ( typeof window !== 'undefined' && window.salesPulse?.slots?.[ name ] ) || [];

	if ( ! entries.length ) {
		return null;
	}

	return (
		<>
			{ entries.map( ( entry ) => {
				const Component = entry.component;
				if ( ! Component ) {
					return null;
				}
				return <Component key={ entry.id } { ...( props || {} ) } />;
			} ) }
		</>
	);
}
