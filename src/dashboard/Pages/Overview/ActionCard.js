/**
 * Action Card — contextual recommendation.
 */
import React from 'react';
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
					<h3 className="text-sm font-semibold m-0">Suggested Action</h3>
				</div>

				{ severity === 'info' || ! text ? (
					<div className="flex flex-col items-center justify-center py-6 text-center">
						<div className="h-10 w-10 rounded-full bg-success/10 flex items-center justify-center mb-2">
							<CheckCircle2 className="h-5 w-5 text-success/50" />
						</div>
						<p className="text-sm text-muted-foreground">
							No action needed right now.
						</p>
						<p className="text-xs text-muted-foreground/60 mt-1">
							Store performance is stable.
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
								Pattern: { scenario.replace( /_/g, ' ' ).replace( /\b\w/g, ( c ) => c.toUpperCase() ) }
							</p>
						) }
					</div>
				) }
			</div>
		</Card>
	);
}
