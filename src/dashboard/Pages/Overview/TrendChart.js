/**
 * Trend Chart — 7-day revenue sparkline.
 */
import React from 'react';
import { useTrend } from '@DashboardApp/hooks/useOverview';
import { Card } from '@Components/ui/card';
import { formatCurrency, formatDate } from '@Utils/formatters';
import { TrendingUp } from 'lucide-react';
import {
	ResponsiveContainer,
	AreaChart,
	Area,
	XAxis,
	YAxis,
	Tooltip,
	CartesianGrid,
} from 'recharts';

function CustomTooltip( { active, payload, label } ) {
	if ( ! active || ! payload?.length ) {
		return null;
	}

	return (
		<div className="bg-popover border border-solid rounded-md p-2 shadow-md text-xs">
			<p className="font-medium">{ formatDate( label ) }</p>
			<p className="text-muted-foreground">
				Revenue: { formatCurrency( payload[ 0 ]?.value ) }
			</p>
		</div>
	);
}

export function TrendChart() {
	const { data, isLoading } = useTrend( 7 );

	if ( isLoading || ! data?.trend?.length ) {
		return null;
	}

	return (
		<Card className="border border-solid">
			<div className="p-5">
				<div className="flex items-center gap-2 mb-4">
					<TrendingUp className="h-4 w-4 text-muted-foreground" />
					<h3 className="text-sm font-semibold m-0">7-Day Revenue Trend</h3>
				</div>
				<ResponsiveContainer width="100%" height={ 200 }>
					<AreaChart data={ data.trend } margin={ { top: 5, right: 5, bottom: 5, left: 5 } }>
						<defs>
							<linearGradient id="revGradient" x1="0" y1="0" x2="0" y2="1">
								<stop offset="5%" stopColor="hsl(var(--chart-primary))" stopOpacity={ 0.2 } />
								<stop offset="95%" stopColor="hsl(var(--chart-primary))" stopOpacity={ 0 } />
							</linearGradient>
						</defs>
						<CartesianGrid strokeDasharray="3 3" className="stroke-muted" />
						<XAxis
							dataKey="date"
							tickFormatter={ ( d ) => formatDate( d ).split( ', ' )[ 0 ] }
							tick={ { fontSize: 11 } }
							className="text-muted-foreground"
						/>
						<YAxis
							tickFormatter={ ( v ) => formatCurrency( v ).replace( /\.00$/, '' ) }
							tick={ { fontSize: 11 } }
							width={ 70 }
							className="text-muted-foreground"
						/>
						<Tooltip content={ <CustomTooltip /> } />
						<Area
							type="monotone"
							dataKey="revenue"
							stroke="hsl(var(--chart-primary))"
							fill="url(#revGradient)"
							strokeWidth={ 2 }
						/>
					</AreaChart>
				</ResponsiveContainer>
			</div>
		</Card>
	);
}
