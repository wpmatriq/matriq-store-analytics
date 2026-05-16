/**
 * PluginSlot - inline component slot for premium extensions.
 *
 * Renders any components registered into the named slot via
 * `window.matriqMSA.registerSlot( name, { id, component, weight } )`.
 *
 * Components registered into the same slot render in weight-ascending order
 * (lower weight = higher in the DOM). Each registered component receives
 * the `props` object passed to PluginSlot — typically the page's data so
 * the slot component can render its inline UI without making its own request.
 *
 * Subscribes to the `matriq_msa:slot-registered` window event so slots that
 * mount inside otherwise-static parents (e.g. the header) re-render when a
 * Pro bundle loads after the SP shell has already painted.
 *
 * Returns `null` when no entries are registered, so the host page's layout
 * is unaffected by the empty slot.
 */
import React, { useEffect, useState } from 'react';

export function PluginSlot( { name, props } ) {
	const [ , bump ] = useState( 0 );

	useEffect( () => {
		const onRegistered = ( event ) => {
			if ( event?.detail?.name === name ) {
				bump( ( v ) => v + 1 );
			}
		};
		window.addEventListener( 'matriq_msa:slot-registered', onRegistered );
		return () => {
			window.removeEventListener( 'matriq_msa:slot-registered', onRegistered );
		};
	}, [ name ] );

	const entries = ( typeof window !== 'undefined' && window.matriqMSA?.slots?.[ name ] ) || [];

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
