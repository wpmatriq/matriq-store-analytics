/**
 * Diagnosis Card — headline + confidence + campaign context.
 *
 * Uses a colored left border and subtle background tint
 * to visually communicate the diagnosis at a glance.
 */
import React from 'react';
import { SeverityBadge } from '@Components/SeverityBadge';
import { Badge } from '@Components/ui/badge';
import { formatPercent } from '@Utils/formatters';
import { TrendingUp, TrendingDown, Minus, Megaphone } from 'lucide-react';
import classnames from '@Utils/classnames';

const directionConfig = {
	growth: {
		icon: <TrendingUp className="h-5 w-5" />,
		border: 'border-l-success',
		bg: 'bg-success/5',
		iconColor: 'text-success',
		severity: 'success',
	},
	decline: {
		icon: <TrendingDown className="h-5 w-5" />,
		border: 'border-l-destructive',
		bg: 'bg-destructive/5',
		iconColor: 'text-destructive',
		severity: 'warning',
	},
	stable: {
		icon: <Minus className="h-5 w-5" />,
		border: 'border-l-muted-foreground',
		bg: 'bg-muted/30',
		iconColor: 'text-muted-foreground',
		severity: 'info',
	},
};

export function DiagnosisCard( { diagnosis, campaign } ) {
	if ( ! diagnosis ) {
		return null;
	}

	const { direction, headline, revenue_change_percent: changePct, confidence_label: confidenceLabel } = diagnosis;
	const config = directionConfig[ direction ] || directionConfig.stable;

	return (
		<div className={ classnames(
			'rounded-lg border border-solid border-l-4 p-5',
			config.border,
			config.bg
		) }>
			<div className="flex items-start gap-4">
				<div className={ classnames( 'mt-0.5 flex-shrink-0', config.iconColor ) }>
					{ config.icon }
				</div>
				<div className="flex-1 min-w-0">
					<div className="flex items-center gap-2 flex-wrap mb-1.5">
						<span className="text-2xl font-bold tabular-nums">
							{ formatPercent( changePct ) }
						</span>
						<SeverityBadge severity={ config.severity } />
						{ campaign && (
							<Badge variant="outline" className="text-xs border-solid gap-1">
								<Megaphone className="h-3 w-3" />
								{ campaign.name }
							</Badge>
						) }
					</div>
					<p className="text-sm text-foreground/80 leading-relaxed">
						{ headline }
					</p>
					{ confidenceLabel && (
						<p className="text-xs text-muted-foreground mt-1">
							{ confidenceLabel }
						</p>
					) }
				</div>
			</div>
		</div>
	);
}
