/**
 * Action Card — contextual recommendation.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { Card } from '@Components/ui/card';
import { SeverityBadge } from '@Components/SeverityBadge';
import { Lightbulb, CheckCircle2 } from 'lucide-react';

export function ActionCard( { recommendation } ) {
	if ( ! recommendation ) {
		return null;
	}

	const { recommendation: text, severity, scenario } = recommendation;

	return (
		<Card className="border border-solid">
			<div className="p-5">
				<div className="flex items-center gap-2 mb-4">
					<Lightbulb className="h-4 w-4 text-warning" />
					<h3 className="text-sm font-semibold m-0">{ __( 'Suggested Action', 'sales-pulse' ) }</h3>
				</div>

				{ severity === 'info' || ! text ? (
					<div className="flex flex-col items-center justify-center py-6 text-center">
						<div className="h-10 w-10 rounded-full bg-success/10 flex items-center justify-center mb-2">
							<CheckCircle2 className="h-5 w-5 text-success/50" />
						</div>
						<p className="text-sm text-muted-foreground">
							{ __( 'No action needed right now.', 'sales-pulse' ) }
						</p>
						<p className="text-xs text-muted-foreground/60 mt-1">
							{ __( 'Store performance is stable.', 'sales-pulse' ) }
						</p>
					</div>
				) : (
					<div className="space-y-3">
						<SeverityBadge severity={ severity } />
						<p className="text-sm leading-relaxed">
							{ text }
						</p>
						{ scenario && scenario !== 'stable' && (
							<p className="text-xs text-muted-foreground">
								{ __( 'Pattern:', 'sales-pulse' ) } { scenario.replace( /_/g, ' ' ).replace( /\b\w/g, ( c ) => c.toUpperCase() ) }
							</p>
						) }
					</div>
				) }
			</div>
		</Card>
	);
}
