/**
 * Readiness Gate component.
 *
 * Wraps the dashboard and shows setup/loading states
 * until all data prerequisites are met.
 */
import React from 'react';
import { useReadiness, useTriggerSnapshot, useTriggerBackfill } from '@DashboardApp/hooks/useReadiness';
import { Card } from '@Components/ui/card';
import { Button } from '@Components/ui/button';
import { Progress } from '@Components/ui/progress';
import { AlertCircle, Database, RefreshCw, CheckCircle2 } from 'lucide-react';

export function ReadinessGate( { children } ) {
	const { data, isLoading, error } = useReadiness();
	const triggerSnapshot = useTriggerSnapshot();
	const triggerBackfill = useTriggerBackfill();

	if ( isLoading ) {
		return (
			<div className="flex items-center justify-center min-h-[400px]">
				<div className="text-center space-y-3">
					<RefreshCw className="h-8 w-8 animate-spin mx-auto text-muted-foreground" />
					<p className="text-sm text-muted-foreground">Checking data readiness...</p>
				</div>
			</div>
		);
	}

	if ( error ) {
		return (
			<Card className="max-w-lg mx-auto mt-12 border border-solid">
				<div className="p-6 text-center space-y-3">
					<AlertCircle className="h-10 w-10 text-destructive mx-auto" />
					<p className="text-sm text-muted-foreground">
						Could not check system status. Please refresh the page.
					</p>
				</div>
			</Card>
		);
	}

	// All good — render dashboard.
	if ( data?.ready ) {
		return children;
	}

	// Show setup state.
	return (
		<div className="max-w-lg mx-auto mt-12 space-y-4">
			<Card className="border border-solid">
				<div className="p-5">
					<div className="flex items-center gap-2 mb-4">
						<Database className="h-5 w-5" />
						<h3 className="text-lg font-semibold m-0">Setting up Sales Pulse</h3>
					</div>

					<div className="space-y-3">
						<CheckItem
							label="WooCommerce active"
							checked={ data?.woocommerce_active }
						/>
						<CheckItem
							label="Analytics tables available"
							checked={ data?.analytics_tables_exist }
						/>
						<CheckItem
							label="Orders found"
							checked={ data?.orders_exist }
						/>
						<CheckItem
							label="Plugin tables created"
							checked={ data?.plugin_tables_exist }
						/>
						<CheckItem
							label="Snapshot data available"
							checked={ data?.has_data }
						/>
					</div>

					{ data?.plugin_tables_exist && ! data?.has_data && data?.orders_exist && (
						<div className="pt-4 space-y-3">
							<p className="text-sm text-muted-foreground">
								Your store has orders but no snapshots yet. Build the first snapshot to get started.
							</p>
							<Button
								onClick={ () => triggerSnapshot.mutate() }
								disabled={ triggerSnapshot.isPending }
								className="w-full"
							>
								{ triggerSnapshot.isPending ? (
									<>
										<RefreshCw className="h-4 w-4 animate-spin" />
										Building snapshot...
									</>
								) : (
									'Build First Snapshot'
								) }
							</Button>
						</div>
					) }

					{ ! data?.backfill_complete && data?.has_data && (
						<div className="pt-4 space-y-2">
							<div className="flex items-center justify-between text-xs text-muted-foreground">
								<span>Backfilling historical data...</span>
								<span>{ data?.snapshot_count || 0 } days</span>
							</div>
							<Progress value={ data?.snapshot_count > 0 ? Math.min( ( data.snapshot_count / 365 ) * 100, 95 ) : 5 } />
							<Button
								variant="outline"
								size="sm"
								onClick={ () => triggerBackfill.mutate() }
								disabled={ triggerBackfill.isPending }
							>
								{ triggerBackfill.isPending ? 'Processing...' : 'Speed up backfill' }
							</Button>
						</div>
					) }
				</div>
			</Card>
		</div>
	);
}

function CheckItem( { label, checked } ) {
	return (
		<div className="flex items-center gap-2 text-sm">
			{ checked ? (
				<CheckCircle2 className="h-4 w-4 text-success flex-shrink-0" />
			) : (
				<div className="h-4 w-4 rounded-full border-2 border-muted-foreground/30 flex-shrink-0" />
			) }
			<span className={ checked ? 'text-foreground' : 'text-muted-foreground' }>
				{ label }
			</span>
		</div>
	);
}
