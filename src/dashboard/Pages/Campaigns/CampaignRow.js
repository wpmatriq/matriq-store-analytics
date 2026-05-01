/**
 * CampaignRow - single active/recent campaign card.
 *
 * Active campaigns render with a left-accent success bar and a pulsing dot.
 * Destructive delete sits behind an AlertDialog confirmation; ending a
 * campaign fires directly (it just stamps an end date server-side).
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { CalendarRange, Megaphone, StopCircle, Tag, Trash2 } from 'lucide-react';
import {
	AlertDialog,
	AlertDialogAction,
	AlertDialogCancel,
	AlertDialogContent,
	AlertDialogDescription,
	AlertDialogFooter,
	AlertDialogHeader,
	AlertDialogTitle,
	AlertDialogTrigger,
} from '@Components/ui/alert-dialog';
import { formatDate } from '@Utils/formatters';
import classnames from '@Utils/classnames';

function goalLabel( goal ) {
	switch ( goal ) {
		case 'clearance':
			return __( 'Clearance Sale', 'sales-pulse' );
		case 'launch':
			return __( 'Product Launch', 'sales-pulse' );
		case 'orders':
			return __( 'Order Push', 'sales-pulse' );
		case 'aov':
			return __( 'AOV Boost', 'sales-pulse' );
		default:
			return goal;
	}
}

export function CampaignRow( { campaign, onEnd, onDelete, endPending, deletePending } ) {
	const isActive = !! campaign.is_active;

	return (
		<div
			className={ classnames(
				'relative overflow-hidden rounded-2xl border border-solid bg-card p-5 shadow-sm transition-all hover:shadow-md',
				isActive ? 'border-success/30' : 'border-border'
			) }
		>
			{ isActive && (
				<span className="pointer-events-none absolute inset-y-0 left-0 w-1 bg-gradient-to-b from-success to-pulse" />
			) }

			<div className="flex flex-wrap items-center justify-between gap-4">
				<div className="flex min-w-0 items-center gap-3">
					<span
						className={ classnames(
							'flex h-10 w-10 shrink-0 items-center justify-center rounded-xl',
							isActive
								? 'bg-success/10 text-success ring-1 ring-success/30'
								: 'bg-muted text-muted-foreground'
						) }
					>
						<Megaphone className="h-5 w-5" />
					</span>

					<div className="min-w-0">
						<div className="flex flex-wrap items-center gap-2">
							<h3 className="m-0 font-display text-lg leading-tight text-ink">
								{ campaign.name }
							</h3>
							{ isActive ? (
								<span className="inline-flex items-center gap-1.5 rounded-full border border-solid border-success/30 bg-success/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-success-foreground/80">
									<span className="h-1.5 w-1.5 rounded-full bg-success pulse-dot" />
									{ __( 'Active', 'sales-pulse' ) }
								</span>
							) : (
								<span className="inline-flex items-center gap-1.5 rounded-full border border-solid border-border bg-surface px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-muted-foreground">
									{ __( 'Ended', 'sales-pulse' ) }
								</span>
							) }
						</div>
						<div className="mt-1 flex flex-wrap items-center gap-3 text-xs text-muted-foreground">
							<span className="inline-flex items-center gap-1.5">
								<Tag className="h-3 w-3" />
								{ goalLabel( campaign.goal ) }
							</span>
							<span className="inline-flex items-center gap-1.5 font-mono">
								<CalendarRange className="h-3 w-3" />
								{ formatDate( campaign.start_date ) }
								{ ' - ' }
								{ formatDate( campaign.end_date ) }
							</span>
						</div>
					</div>
				</div>

				<div className="flex items-center gap-2">
					{ isActive && (
						<button
							type="button"
							onClick={ () => onEnd?.( campaign.id ) }
							disabled={ endPending }
							className="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-solid border-border bg-surface px-3 py-1.5 text-xs font-medium text-foreground transition-all hover:border-border/80 hover:bg-surface-elevated disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
						>
							<StopCircle className="h-3.5 w-3.5" />
							{ __( 'End campaign', 'sales-pulse' ) }
						</button>
					) }
					{ ! isActive && (
						<DeleteCampaignDialog
							campaign={ campaign }
							onDelete={ onDelete }
							pending={ deletePending }
						/>
					) }
				</div>
			</div>
		</div>
	);
}

function DeleteCampaignDialog( { campaign, onDelete, pending } ) {
	return (
		<AlertDialog>
			<AlertDialogTrigger asChild>
				<button
					type="button"
					aria-label={ __( 'Delete campaign', 'sales-pulse' ) }
					className="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
				>
					<Trash2 className="h-3.5 w-3.5" />
				</button>
			</AlertDialogTrigger>
			<AlertDialogContent>
				<AlertDialogHeader>
					<AlertDialogTitle>
						{ __( 'Delete this campaign?', 'sales-pulse' ) }
					</AlertDialogTitle>
					<AlertDialogDescription>
						{ __(
							'The campaign log entry will be removed permanently. Historical diagnoses that referenced it will keep their recorded context.',
							'sales-pulse'
						) }
					</AlertDialogDescription>
				</AlertDialogHeader>
				<AlertDialogFooter>
					<AlertDialogCancel disabled={ pending }>
						{ __( 'Cancel', 'sales-pulse' ) }
					</AlertDialogCancel>
					<AlertDialogAction
						disabled={ pending }
						onClick={ () => onDelete?.( campaign.id ) }
						className="bg-destructive text-destructive-foreground hover:bg-destructive/90"
					>
						{ pending
							? __( 'Deleting…', 'sales-pulse' )
							: __( 'Delete', 'sales-pulse' ) }
					</AlertDialogAction>
				</AlertDialogFooter>
			</AlertDialogContent>
		</AlertDialog>
	);
}
