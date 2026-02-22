/**
 * Campaigns Page — manage campaign context.
 *
 * Campaigns affect how the diagnosis engine interprets changes
 * (suppress false alarms during sales/launches).
 */
import React, { useState } from 'react';
import { __ } from '@wordpress/i18n';
import { useCampaigns, useEndCampaign, useDeleteCampaign } from '@DashboardApp/hooks/useCampaigns';
import { Card, CardContent, CardHeader, CardTitle } from '@Components/ui/card';
import { Button } from '@Components/ui/button';
import { Badge } from '@Components/ui/badge';
import { formatDate } from '@Utils/formatters';
import { CampaignForm } from './CampaignForm';
import { RefreshCw, Plus, Megaphone, StopCircle, Trash2 } from 'lucide-react';

const goalLabels = {
	orders: __( 'Increase Orders', 'sales-pulse' ),
	aov: __( 'Increase AOV', 'sales-pulse' ),
	clearance: __( 'Clearance Sale', 'sales-pulse' ),
	launch: __( 'Product Launch', 'sales-pulse' ),
};

export default function CampaignsPage() {
	const [ showForm, setShowForm ] = useState( false );
	const { data: campaigns, isLoading } = useCampaigns();
	const endCampaign = useEndCampaign();
	const deleteCampaign = useDeleteCampaign();

	return (
		<div className="space-y-6">
			<div className="flex items-center justify-between">
				<div>
					<h1 className="text-xl font-semibold mb-2">{ __( 'Campaigns', 'sales-pulse' ) }</h1>
					<p className="text-sm text-muted-foreground mt-1">
						{ __( 'Mark active campaigns to suppress false alarms in diagnosis', 'sales-pulse' ) }
					</p>
				</div>
				<Button onClick={ () => setShowForm( ! showForm ) } size="sm">
					<Plus className="h-4 w-4 mr-1" />
					{ __( 'New Campaign', 'sales-pulse' ) }
				</Button>
			</div>

			{ showForm && (
				<CampaignForm onSuccess={ () => setShowForm( false ) } onCancel={ () => setShowForm( false ) } />
			) }

			{ isLoading ? (
				<div className="flex items-center justify-center py-16">
					<RefreshCw className="h-6 w-6 animate-spin text-muted-foreground" />
				</div>
			) : ! campaigns?.length ? (
				<Card className="border border-solid">
					<CardContent className="py-12 text-center">
						<Megaphone className="h-10 w-10 text-muted-foreground/40 mx-auto mb-3" />
						<p className="text-sm text-muted-foreground">
							{ __( 'No campaigns yet. Create one when you run a sale, launch a product, or change pricing.', 'sales-pulse' ) }
						</p>
					</CardContent>
				</Card>
			) : (
				<div className="space-y-3">
					{ campaigns.map( ( campaign ) => (
						<Card key={ campaign.id } className="border border-solid">
							<CardContent className="p-4">
								<div className="flex items-center justify-between">
									<div className="space-y-1">
										<div className="flex items-center gap-2">
											<span className="font-medium text-sm">{ campaign.name }</span>
											{ campaign.is_active ? (
												<Badge className="text-xs bg-success/10 text-success border-success/20 border-solid">
													{ __( 'Active', 'sales-pulse' ) }
												</Badge>
											) : (
												<Badge variant="outline" className="text-xs border-solid">
													{ __( 'Ended', 'sales-pulse' ) }
												</Badge>
											) }
										</div>
										<div className="flex items-center gap-3 text-xs text-muted-foreground">
											<span>{ goalLabels[ campaign.goal ] || campaign.goal }</span>
											<span>{ formatDate( campaign.start_date ) } — { formatDate( campaign.end_date ) }</span>
										</div>
									</div>
									<div className="flex items-center gap-2">
										{ campaign.is_active && (
											<Button
												variant="outline"
												size="sm"
												onClick={ () => endCampaign.mutate( campaign.id ) }
												disabled={ endCampaign.isPending }
											>
												<StopCircle className="h-3.5 w-3.5 mr-1" />
												{ __( 'End', 'sales-pulse' ) }
											</Button>
										) }
										{ ! campaign.is_active && (
											<Button
												variant="ghost"
												size="sm"
												onClick={ () => deleteCampaign.mutate( campaign.id ) }
												disabled={ deleteCampaign.isPending }
											>
												<Trash2 className="h-3.5 w-3.5" />
											</Button>
										) }
									</div>
								</div>
							</CardContent>
						</Card>
					) ) }
				</div>
			) }
		</div>
	);
}
