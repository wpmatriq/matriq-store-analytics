/**
 * Overview Page — Morning Briefing.
 *
 * The primary dashboard view showing:
 * 1. Headline diagnosis
 * 2. Metric cards (Revenue, Orders, AOV, Items/Order)
 * 3. Impact breakdown (what changed)
 * 4. Action recommendation
 * 5. 7-day trend sparkline
 */
import React, { useState } from 'react';
import { useOverview } from '@DashboardApp/hooks/useOverview';
import { PeriodToggle } from '@Components/PeriodToggle';
import { DiagnosisCard } from './DiagnosisCard';
import { MetricCards } from './MetricCards';
import { ImpactBreakdown } from './ImpactBreakdown';
import { ActionCard } from './ActionCard';
import { TrendChart } from './TrendChart';
import { RefreshCw } from 'lucide-react';

export default function OverviewPage() {
	const [ period, setPeriod ] = useState( 'daily' );
	const { data, isLoading, error } = useOverview( period );

	if ( isLoading ) {
		return (
			<div className="flex items-center justify-center min-h-[300px]">
				<RefreshCw className="h-6 w-6 animate-spin text-muted-foreground" />
			</div>
		);
	}

	if ( error ) {
		return (
			<div className="text-center py-12 text-muted-foreground">
				<p>Could not load overview data. Please try refreshing.</p>
			</div>
		);
	}

	const { diagnosis, recommendation, metric_cards: metricCards, campaign } = data || {};

	return (
		<div className="space-y-6">
			{ /* Header with period toggle */ }
			<div className="flex items-center justify-between">
				<div>
					<h1 className="text-xl font-semibold mb-2">Morning Briefing</h1>
					<p className="text-sm text-muted-foreground mt-1">
						{ period === 'daily' ? "Yesterday's store performance" : 'Last 7 days vs previous 7 days' }
					</p>
				</div>
				<PeriodToggle value={ period } onChange={ setPeriod } />
			</div>

			{ /* Diagnosis headline */ }
			<DiagnosisCard diagnosis={ diagnosis } campaign={ campaign } />

			{ /* Metric cards */ }
			<MetricCards cards={ metricCards } />

			{ /* Impact breakdown + Action recommendation side by side */ }
			<div className="grid grid-cols-1 lg:grid-cols-2 gap-4">
				<ImpactBreakdown diagnosis={ diagnosis } />
				<ActionCard recommendation={ recommendation } />
			</div>

			{ /* Trend sparkline */ }
			<TrendChart />
		</div>
	);
}
