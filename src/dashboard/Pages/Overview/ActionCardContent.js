/**
 * ActionCardContent - rendered inside <InsightCard title="Suggested action">.
 *
 * Displays the recommendation headline with a severity pill and an optional
 * scenario tag. Card chrome is owned by InsightCard.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { StatusBadge } from '@Components/pulse/StatusBadge';
import { statusVariant } from '@DashboardApp/Utils/statusMap';

function prettyScenario( scenario ) {
	return scenario
		.replace( /_/g, ' ' )
		.replace( /\b\w/g, ( c ) => c.toUpperCase() );
}

export function ActionCardContent( { recommendation } ) {
	if ( ! recommendation ) {
		return null;
	}

	const { recommendation: text, severity, scenario } = recommendation;

	if ( ! text ) {
		return null;
	}

	return (
		<div className="space-y-3">
			<StatusBadge variant={ statusVariant( severity ) } />
			<p className="m-0 text-sm leading-relaxed text-foreground">
				{ text }
			</p>
			{ scenario && scenario !== 'stable' && (
				<p className="m-0 text-xs text-muted-foreground">
					{ __( 'Pattern:', 'sales-pulse' ) }{ ' ' }
					<span className="font-mono">{ prettyScenario( scenario ) }</span>
				</p>
			) }
		</div>
	);
}
