/**
 * Campaigns Page — mark what's running so diagnosis adjusts.
 *
 * Active campaigns tell the diagnosis engine to suppress false alarms during
 * sales, launches, and retention pushes. Users create/end/delete campaigns
 * here; the form slides in via AnimatePresence.
 */
import React, { useState } from 'react';
import { AnimatePresence, motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import { AlertTriangle, Megaphone, Plus } from 'lucide-react';
import {
	useCampaigns,
	useDeleteCampaign,
	useEndCampaign,
} from '@DashboardApp/hooks/useCampaigns';
import { PageHeader } from '@Components/pulse/PageHeader';
import { InsightCard } from '@Components/pulse/InsightCard';
import { CampaignForm } from './CampaignForm';
import { CampaignRow } from './CampaignRow';

const EASE = [ 0.16, 1, 0.3, 1 ];

export default function CampaignsPage() {
	const [ showForm, setShowForm ] = useState( false );
	const { data: campaigns, isLoading, error, refetch } = useCampaigns();
	const endCampaign = useEndCampaign();
	const deleteCampaign = useDeleteCampaign();

	const hasCampaigns = Array.isArray( campaigns ) && campaigns.length > 0;

	return (
		<motion.div
			initial={ { opacity: 0 } }
			animate={ { opacity: 1 } }
			transition={ { duration: 0.4, ease: EASE } }
			className="space-y-8"
		>
			<PageHeader
				eyebrow={ __( 'Campaign tracker', 'sales-pulse' ) }
				title={ __( "Mark what's running.", 'sales-pulse' ) }
				subtitle={ __(
					'When a campaign is active, Sales Pulse adjusts diagnosis to suppress false alarms and credit lift correctly.',
					'sales-pulse'
				) }
				actions={
					! showForm && (
						<button
							type="button"
							onClick={ () => setShowForm( true ) }
							className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
						>
							<Plus className="h-4 w-4" />
							{ __( 'New campaign', 'sales-pulse' ) }
						</button>
					)
				}
			/>

			<AnimatePresence initial={ false }>
				{ showForm && (
					<CampaignForm
						onSuccess={ () => setShowForm( false ) }
						onCancel={ () => setShowForm( false ) }
					/>
				) }
			</AnimatePresence>

			{ error ? (
				<InsightCard
					icon={ <AlertTriangle className="h-4 w-4" /> }
					title={ __( 'Could not load campaigns', 'sales-pulse' ) }
					accent="warning"
				>
					<div className="flex flex-col items-start gap-4">
						<p className="m-0 text-sm text-muted-foreground">
							{ __(
								'We hit an error fetching your campaigns. Retry, or refresh the page.',
								'sales-pulse'
							) }
						</p>
						<button
							type="button"
							onClick={ () => refetch() }
							className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
						>
							{ __( 'Retry', 'sales-pulse' ) }
						</button>
					</div>
				</InsightCard>
			) : (
				<section className="space-y-4">
					<h2 className="m-0 text-[11px] font-semibold uppercase tracking-[0.14em] text-muted-foreground">
						{ __( 'Active & recent', 'sales-pulse' ) }
					</h2>

					{ isLoading ? (
						<div className="space-y-3">
							{ Array.from( { length: 3 } ).map( ( _, index ) => (
								<CampaignRowSkeleton key={ index } />
							) ) }
						</div>
					) : hasCampaigns ? (
						<div className="space-y-3">
							{ campaigns.map( ( campaign ) => (
								<CampaignRow
									key={ campaign.id }
									campaign={ campaign }
									onEnd={ ( id ) => endCampaign.mutate( id ) }
									onDelete={ ( id ) => deleteCampaign.mutate( id ) }
									endPending={ endCampaign.isPending }
									deletePending={ deleteCampaign.isPending }
								/>
							) ) }
						</div>
					) : (
						<InsightCard
							icon={ <Megaphone className="h-4 w-4" /> }
							title={ __( 'No campaigns yet', 'sales-pulse' ) }
							emptyTitle={ __( 'Nothing running right now', 'sales-pulse' ) }
							emptyDescription={ __(
								"Create one when you run a sale, launch a product, or change pricing — we'll adjust diagnosis during the campaign window.",
								'sales-pulse'
							) }
						/>
					) }
				</section>
			) }
		</motion.div>
	);
}

function CampaignRowSkeleton() {
	return (
		<div className="rounded-2xl border border-solid border-border bg-card p-5 shadow-sm">
			<div className="flex items-center gap-3">
				<span className="h-10 w-10 shrink-0 rounded-xl bg-muted shimmer" />
				<div className="flex-1 space-y-2">
					<span className="block h-4 w-1/3 rounded bg-muted shimmer" />
					<span className="block h-3 w-1/2 rounded bg-muted shimmer" />
				</div>
				<span className="h-8 w-28 rounded-full bg-muted shimmer" />
			</div>
		</div>
	);
}
