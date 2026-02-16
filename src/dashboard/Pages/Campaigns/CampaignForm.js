/**
 * Campaign Form — create a new campaign.
 */
import React, { useState } from 'react';
import { useCreateCampaign } from '@DashboardApp/hooks/useCampaigns';
import { Card } from '@Components/ui/card';
import { Button } from '@Components/ui/button';
import { Input } from '@Components/ui/input';
import { Label } from '@Components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@Components/ui/select';

const goals = [
	{ value: 'orders', label: 'Increase Orders — e.g., flash sale, coupon code' },
	{ value: 'aov', label: 'Increase AOV — e.g., bundle deals, upsells' },
	{ value: 'clearance', label: 'Clearance Sale — e.g., end-of-season, overstock' },
	{ value: 'launch', label: 'Product Launch — e.g., new product, new category' },
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
			setError( 'All fields are required.' );
			return;
		}

		if ( endDate < startDate ) {
			setError( 'End date must be after start date.' );
			return;
		}

		createCampaign.mutate(
			{ name: name.trim(), goal, start_date: startDate, end_date: endDate },
			{
				onSuccess: () => onSuccess?.(),
				onError: ( err ) => setError( err?.message || 'Failed to create campaign.' ),
			}
		);
	};

	return (
		<Card className="border border-solid">
			<div className="p-5">
				<h3 className="text-sm font-semibold m-0 mb-4">New Campaign</h3>
				<form onSubmit={ handleSubmit } className="space-y-4">
					<div className="space-y-1.5">
						<Label htmlFor="campaign-name">Campaign Name</Label>
						<Input
							id="campaign-name"
							placeholder="e.g., Summer Sale 2026"
							value={ name }
							onChange={ ( e ) => setName( e.target.value ) }
						/>
					</div>

					<div className="space-y-1.5">
						<Label>Campaign Goal</Label>
						<Select value={ goal } onValueChange={ setGoal }>
							<SelectTrigger>
								<SelectValue placeholder="Select a goal..." />
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
							<Label htmlFor="start-date">Start Date</Label>
							<Input
								id="start-date"
								type="date"
								value={ startDate }
								onChange={ ( e ) => setStartDate( e.target.value ) }
							/>
						</div>
						<div className="space-y-1.5">
							<Label htmlFor="end-date">End Date</Label>
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
							{ createCampaign.isPending ? 'Creating...' : 'Create Campaign' }
						</Button>
						<Button type="button" variant="ghost" size="sm" onClick={ onCancel }>
							Cancel
						</Button>
					</div>
				</form>
			</div>
		</Card>
	);
}
