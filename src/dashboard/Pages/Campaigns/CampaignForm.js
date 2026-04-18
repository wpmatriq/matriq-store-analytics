/**
 * Campaign Form — create a new campaign.
 */
import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useCreateCampaign } from '@DashboardApp/hooks/useCampaigns';
import { Card } from '@Components/ui/card';
import { Button } from '@Components/ui/button';
import { Input } from '@Components/ui/input';
import { Label } from '@Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@Components/ui/select';

const goals = [
	{ value: 'orders', label: __( 'Increase Orders — e.g., flash sale, coupon code', 'sales-pulse' ) },
	{ value: 'aov', label: __( 'Increase AOV — e.g., bundle deals, upsells', 'sales-pulse' ) },
	{ value: 'clearance', label: __( 'Clearance Sale — e.g., end-of-season, overstock', 'sales-pulse' ) },
	{ value: 'launch', label: __( 'Product Launch — e.g., new product, new category', 'sales-pulse' ) },
];

export function CampaignForm( { onSuccess, onCancel } ) {
	const [ name, setName ] = useState( '' );
	const [ goal, setGoal ] = useState( '' );
	const [ startDate, setStartDate ] = useState( new Date().toISOString().slice( 0, 10 ) );
	const [ endDate, setEndDate ] = useState( '' );
	const [ error, setError ] = useState( '' );

	const createCampaign = useCreateCampaign();

	const handleSubmit = ( e ) => {
		e.preventDefault();
		setError( '' );

		if ( ! name.trim() || ! goal || ! startDate || ! endDate ) {
			setError( __( 'All fields are required.', 'sales-pulse' ) );
			return;
		}

		if ( endDate < startDate ) {
			setError( __( 'End date must be after start date.', 'sales-pulse' ) );
			return;
		}

		createCampaign.mutate(
			{ name: name.trim(), goal, start_date: startDate, end_date: endDate },
			{
				onSuccess: () => onSuccess?.(),
				onError: ( err ) => setError( err?.message || __( 'Failed to create campaign.', 'sales-pulse' ) ),
			}
		);
	};

	return (
		<Card className="border border-solid">
			<div className="p-5">
				<h3 className="text-sm font-semibold m-0 mb-4">{ __( 'New Campaign', 'sales-pulse' ) }</h3>
				<form onSubmit={ handleSubmit } className="space-y-4">
					<div className="space-y-1.5">
						<Label htmlFor="campaign-name">{ __( 'Campaign Name', 'sales-pulse' ) }</Label>
						<Input
							id="campaign-name"
							placeholder={ __( 'e.g., Summer Sale 2026', 'sales-pulse' ) }
							value={ name }
							onChange={ ( e ) => setName( e.target.value ) }
						/>
					</div>

					<div className="space-y-1.5">
						<Label>{ __( 'Campaign Goal', 'sales-pulse' ) }</Label>
						<Select value={ goal } onValueChange={ setGoal }>
							<SelectTrigger>
								<SelectValue placeholder={ __( 'Select a goal...', 'sales-pulse' ) } />
							</SelectTrigger>
							<SelectContent>
								{ goals.map( ( g ) => (
									<SelectItem key={ g.value } value={ g.value }>
										{ g.label }
									</SelectItem>
								) ) }
							</SelectContent>
						</Select>
					</div>

					<div className="grid grid-cols-2 gap-4">
						<div className="space-y-1.5">
							<Label htmlFor="start-date">{ __( 'Start Date', 'sales-pulse' ) }</Label>
							<Input
								id="start-date"
								type="date"
								value={ startDate }
								onChange={ ( e ) => setStartDate( e.target.value ) }
							/>
						</div>
						<div className="space-y-1.5">
							<Label htmlFor="end-date">{ __( 'End Date', 'sales-pulse' ) }</Label>
							<Input
								id="end-date"
								type="date"
								value={ endDate }
								onChange={ ( e ) => setEndDate( e.target.value ) }
							/>
						</div>
					</div>

					{ error && (
						<p className="text-xs text-destructive">{ error }</p>
					) }

					<div className="flex items-center gap-2 pt-1">
						<Button type="submit" size="sm" disabled={ createCampaign.isPending }>
							{ createCampaign.isPending ? __( 'Creating...', 'sales-pulse' ) : __( 'Create Campaign', 'sales-pulse' ) }
						</Button>
						<Button type="button" variant="ghost" size="sm" onClick={ onCancel }>
							{ __( 'Cancel', 'sales-pulse' ) }
						</Button>
					</div>
				</form>
			</div>
		</Card>
	);
}
