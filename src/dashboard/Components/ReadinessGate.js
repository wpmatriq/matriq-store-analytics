/**
 * Readiness Gate component.
 *
 * Wraps the dashboard and shows setup/loading states
 * until all data prerequisites are met.
 */
import React from 'react';
import { __, sprintf } from '@wordpress/i18n';
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
					<p className="text-sm text-muted-foreground">{ __( 'Checking data readiness...', 'sales-pulse' ) }</p>
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
						{ __( 'Could not check system status. Please refresh the page.', 'sales-pulse' ) }
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
						<h3 className="text-lg font-semibold m-0">{ __( 'Setting up Sales Pulse', 'sales-pulse' ) }</h3>
					</div>

					<div className="space-y-3">
						<CheckItem
							label={ __( 'WooCommerce active', 'sales-pulse' ) }
							checked={ data?.woocommerce_active }
						/>
						<CheckItem
							label={ __( 'Analytics tables available', 'sales-pulse' ) }
							checked={ data?.analytics_tables_exist }
						/>
						<CheckItem
							label={ __( 'Orders found', 'sales-pulse' ) }
							checked={ data?.orders_exist }
						/>
						<CheckItem
							label={ __( 'Plugin tables created', 'sales-pulse' ) }
							checked={ data?.plugin_tables_exist }
						/>
						<CheckItem
							label={ __( 'Snapshot data available', 'sales-pulse' ) }
							checked={ data?.dashboard_ready }
						/>
					</div>

					{ data?.plugin_tables_exist && ! data?.dashboard_ready && data?.orders_exist && (
						<div className="pt-4 space-y-3">
							<p className="text-sm text-muted-foreground">
								{ __( 'Your store has orders ready to analyze. This will take a few seconds.', 'sales-pulse' ) }
							</p>
							<Button
								onClick={ () => triggerSnapshot.mutate( { days: 14 } ) }
								disabled={ triggerSnapshot.isPending }
								className="w-full"
							>
								{ triggerSnapshot.isPending ? (
									<>
										<RefreshCw className="h-4 w-4 animate-spin" />
										{ __( 'Analyzing your recent sales...', 'sales-pulse' ) }
									</>
								) : (
									__( 'Analyze Your Store', 'sales-pulse' )
								) }
							</Button>
						</div>
					) }

					{ ! data?.backfill_complete && data?.has_data && (
						<div className="pt-4 space-y-2">
							<div className="flex items-center justify-between text-xs text-muted-foreground">
								<span>{ __( 'Backfilling historical data...', 'sales-pulse' ) }</span>
								<span>{ sprintf(
									/* translators: %d: number of days */
									__( '%d days', 'sales-pulse' ),
									data?.snapshot_count || 0
								) }</span>
							</div>
							<Progress value={ data?.snapshot_count > 0 ? Math.min( ( data.snapshot_count / 365 ) * 100, 95 ) : 5 } />
							<Button
								variant="outline"
								size="sm"
								onClick={ () => triggerBackfill.mutate() }
								disabled={ triggerBackfill.isPending }
							>
								{ triggerBackfill.isPending ? __( 'Processing...', 'sales-pulse' ) : __( 'Speed up backfill', 'sales-pulse' ) }
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
