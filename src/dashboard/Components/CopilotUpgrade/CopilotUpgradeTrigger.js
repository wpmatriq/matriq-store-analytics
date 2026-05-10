/**
 * CopilotUpgradeTrigger - free-side "Ask Copilot" header chip.
 *
 * Mirrors Pro's ChatTrigger pixel-for-pixel so the visual handoff is
 * seamless: free merchants see the same call-to-action button as Pro
 * merchants, but clicking it opens an upgrade pitch drawer instead of
 * the chat surface. When Pro is active we return null so Pro's chip
 * takes over the same `header-action` slot.
 *
 * Click dispatches the `salespulse:copilot-upgrade-toggle` window event;
 * CopilotUpgradeDrawer listens for it.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { Sparkles } from 'lucide-react';
import { isProActive } from './proActive';

export function CopilotUpgradeTrigger() {
	if ( isProActive() ) {
		return null;
	}

	const handleClick = () => {
		window.dispatchEvent( new CustomEvent( 'salespulse:copilot-upgrade-toggle' ) );
	};

	return (
		<button
			type="button"
			onClick={ handleClick }
			className="hidden cursor-pointer items-center gap-1.5 rounded-full border border-solid border-border bg-card px-3 py-1 text-xs font-semibold text-foreground transition-all hover:border-pulse/50 hover:bg-pulse/5 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse lg:inline-flex"
			aria-label={ __( 'Discover Copilot', 'sales-pulse' ) }
		>
			<Sparkles className="h-3.5 w-3.5 text-pulse" strokeWidth={ 2.25 } />
			{ __( 'Ask Copilot', 'sales-pulse' ) }
		</button>
	);
}
