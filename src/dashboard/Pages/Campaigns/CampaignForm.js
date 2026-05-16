/**
 * CampaignForm - create a campaign.
 *
 * Uses an OptionCard grid for goal selection and pulse-styled date inputs.
 * Rendered inside an <AnimatePresence> from CampaignsPage so the form slides
 * in and out rather than pop-appearing.
 */
import React, { useState } from 'react';
import { motion } from 'framer-motion';
import { __ } from '@wordpress/i18n';
import { CalendarDays, Plus, Tag, Target, X } from 'lucide-react';
import { useCreateCampaign } from '@DashboardApp/hooks/useCampaigns';
import { OptionCardGroup } from '@Components/pulse/OptionCard';

const EASE = [ 0.16, 1, 0.3, 1 ];

function buildGoalOptions() {
	return [
		{
			value: 'clearance',
			title: __( 'Clearance Sale', 'matriq-store-analytics' ),
			description: __( 'Move existing inventory', 'matriq-store-analytics' ),
		},
		{
			value: 'launch',
			title: __( 'Product Launch', 'matriq-store-analytics' ),
			description: __( 'Drive awareness for new SKUs', 'matriq-store-analytics' ),
		},
		{
			value: 'orders',
			title: __( 'Order Push', 'matriq-store-analytics' ),
			description: __( 'Flash sale or coupon-driven campaign', 'matriq-store-analytics' ),
		},
		{
			value: 'aov',
			title: __( 'AOV Boost', 'matriq-store-analytics' ),
			description: __( 'Bundles, upsells, or basket builders', 'matriq-store-analytics' ),
		},
	];
}

export function CampaignForm( { onSuccess, onCancel } ) {
	const [ name, setName ] = useState( '' );
	const [ goal, setGoal ] = useState( 'clearance' );
	const [ startDate, setStartDate ] = useState(
		new Date().toISOString().slice( 0, 10 )
	);
	const [ endDate, setEndDate ] = useState( '' );
	const [ error, setError ] = useState( '' );

	const createCampaign = useCreateCampaign();

	const handleSubmit = ( event ) => {
		event.preventDefault();
		setError( '' );

		if ( ! name.trim() || ! goal || ! startDate || ! endDate ) {
			setError( __( 'All fields are required.', 'matriq-store-analytics' ) );
			return;
		}
		if ( endDate < startDate ) {
			setError( __( 'End date must be after start date.', 'matriq-store-analytics' ) );
			return;
		}

		createCampaign.mutate(
			{
				name: name.trim(),
				goal,
				start_date: startDate,
				end_date: endDate,
			},
			{
				onSuccess: () => onSuccess?.(),
				onError: ( err ) =>
					setError(
						err?.message ||
							__( 'Failed to create campaign.', 'matriq-store-analytics' )
					),
			}
		);
	};

	return (
		<motion.form
			key="campaign-form"
			initial={ { opacity: 0, height: 0 } }
			animate={ { opacity: 1, height: 'auto' } }
			exit={ { opacity: 0, height: 0 } }
			transition={ { duration: 0.4, ease: EASE } }
			onSubmit={ handleSubmit }
			className="relative overflow-hidden rounded-3xl border border-solid border-border bg-gradient-card shadow-md"
		>
			<div className="pointer-events-none absolute -right-20 -top-32 h-64 w-64 rounded-full bg-pulse/10 blur-3xl" />

			<div className="relative space-y-6 p-8 md:p-10">
				<div className="flex items-start justify-between gap-4">
					<div className="flex items-center gap-3">
						<span className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-ink text-pulse shadow-md">
							<Plus className="h-5 w-5" strokeWidth={ 2.25 } />
						</span>
						<div>
							<h3 className="m-0 font-display text-2xl leading-tight text-ink">
								{ __( 'New campaign', 'matriq-store-analytics' ) }
							</h3>
							<p className="m-0 mt-1 text-sm text-muted-foreground">
								{ __(
									'Set the scope so we know when to dampen alerts.',
									'matriq-store-analytics'
								) }
							</p>
						</div>
					</div>
					<button
						type="button"
						onClick={ onCancel }
						aria-label={ __( 'Close form', 'matriq-store-analytics' ) }
						className="inline-flex h-8 w-8 cursor-pointer items-center justify-center rounded-full border-0 bg-transparent text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					>
						<X className="h-4 w-4" />
					</button>
				</div>

				<div className="space-y-2">
					<label
						htmlFor="campaign-name"
						className="flex items-center gap-2 text-sm font-semibold text-foreground"
					>
						<Tag className="h-4 w-4" />
						{ __( 'Campaign name', 'matriq-store-analytics' ) }
					</label>
					<input
						id="campaign-name"
						type="text"
						placeholder={ __( 'e.g. Summer Sale 2026', 'matriq-store-analytics' ) }
						value={ name }
						onChange={ ( e ) => setName( e.target.value ) }
						className="block w-full rounded-xl border border-solid border-border bg-surface px-4 py-2.5 text-sm text-foreground shadow-xs transition-all placeholder:text-muted-foreground focus-visible:border-pulse focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					/>
				</div>

				<div className="space-y-2">
					<span className="flex items-center gap-2 text-sm font-semibold text-foreground">
						<Target className="h-4 w-4" />
						{ __( 'Campaign goal', 'matriq-store-analytics' ) }
					</span>
					<OptionCardGroup
						label={ __( 'Campaign goal', 'matriq-store-analytics' ) }
						value={ goal }
						onChange={ setGoal }
						options={ buildGoalOptions() }
						gridClassName="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4"
					/>
				</div>

				<div className="grid grid-cols-1 gap-4 md:grid-cols-2">
					<DateField
						id="campaign-start"
						label={ __( 'Start date', 'matriq-store-analytics' ) }
						value={ startDate }
						onChange={ setStartDate }
					/>
					<DateField
						id="campaign-end"
						label={ __( 'End date', 'matriq-store-analytics' ) }
						value={ endDate }
						onChange={ setEndDate }
						placeholder="dd/mm/yyyy"
					/>
				</div>

				{ error && (
					<p className="m-0 text-sm text-destructive">{ error }</p>
				) }

				<div className="flex items-center gap-3 pt-2">
					<button
						type="submit"
						disabled={ createCampaign.isPending }
						className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-5 py-2.5 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md disabled:cursor-not-allowed disabled:opacity-60 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					>
						{ createCampaign.isPending
							? __( 'Creating…', 'matriq-store-analytics' )
							: __( 'Create campaign', 'matriq-store-analytics' ) }
					</button>
					<button
						type="button"
						onClick={ onCancel }
						className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-transparent px-4 py-2 text-sm font-medium text-muted-foreground transition-colors hover:text-foreground focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					>
						{ __( 'Cancel', 'matriq-store-analytics' ) }
					</button>
				</div>
			</div>
		</motion.form>
	);
}

function DateField( { id, label, value, onChange, placeholder } ) {
	return (
		<div className="space-y-2">
			<label
				htmlFor={ id }
				className="flex items-center gap-2 text-sm font-semibold text-foreground"
			>
				<CalendarDays className="h-4 w-4" />
				{ label }
			</label>
			<input
				id={ id }
				type="date"
				value={ value }
				placeholder={ placeholder }
				onChange={ ( e ) => onChange( e.target.value ) }
				className="block w-full rounded-xl border border-solid border-border bg-surface px-4 py-2.5 text-sm text-foreground shadow-xs transition-all focus-visible:border-pulse focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
			/>
		</div>
	);
}
