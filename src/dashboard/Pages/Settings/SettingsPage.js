/**
 * Settings Page — minimal plugin configuration.
 */
import React, { useState, useEffect } from 'react';
import { __ } from '@wordpress/i18n';
import { useSettings, useUpdateSettings } from '@DashboardApp/hooks/useSettings';
import { Card } from '@Components/ui/card';
import { Button } from '@Components/ui/button';
import { Input } from '@Components/ui/input';
import { Label } from '@Components/ui/label';
import { Switch } from '@Components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@Components/ui/select';
import { RefreshCw, Save, CheckCircle2, Globe, Clock, Mail } from 'lucide-react';

export default function SettingsPage() {
	const { data: settings, isLoading } = useSettings();
	const updateSettings = useUpdateSettings();
	const [ form, setForm ] = useState( {} );
	const [ saved, setSaved ] = useState( false );

	// Sync form with fetched settings.
	useEffect( () => {
		if ( settings ) {
			setForm( { ...settings } );
		}
	}, [ settings ] );

	const handleChange = ( key, value ) => {
		setForm( ( prev ) => ( { ...prev, [ key ]: value } ) );
		setSaved( false );
	};

	const handleSave = () => {
		updateSettings.mutate(
			{
				revenue_basis: form.revenue_basis,
				snapshot_hour: parseInt( form.snapshot_hour, 10 ),
				snapshot_min: parseInt( form.snapshot_min, 10 ),
				email_enabled: form.email_enabled,
				email_address: form.email_address,
			},
			{
				onSuccess: () => setSaved( true ),
			}
		);
	};

	if ( isLoading ) {
		return (
			<div className="flex items-center justify-center py-16">
				<RefreshCw className="h-6 w-6 animate-spin text-muted-foreground" />
			</div>
		);
	}

	return (
		<div className="space-y-5 max-w-3xl mx-auto">
			<div className="flex items-center justify-between">
				<div>
					<h1 className="text-xl font-semibold mb-2">{ __( 'Settings', 'sales-pulse' ) }</h1>
					<p className="text-sm text-muted-foreground mt-1">
						{ __( 'Configure Sales Pulse behavior', 'sales-pulse' ) }
					</p>
				</div>
				<div className="flex items-center gap-3">
					{ saved && (
						<span className="text-sm text-success flex items-center gap-1">
							<CheckCircle2 className="h-4 w-4" />
							{ __( 'Saved', 'sales-pulse' ) }
						</span>
					) }
					<Button onClick={ handleSave } disabled={ updateSettings.isPending }>
						{ updateSettings.isPending ? (
							<>
								<RefreshCw className="h-4 w-4 animate-spin" />
								{ __( 'Saving...', 'sales-pulse' ) }
							</>
						) : (
							<>
								<Save className="h-4 w-4" />
								{ __( 'Save Settings', 'sales-pulse' ) }
							</>
						) }
					</Button>
				</div>
			</div>

			<div className="space-y-5">
				{ /* General */ }
				<Card className="border border-solid">
					<div className="p-5">
						<div className="flex items-center gap-2 mb-4">
							<Globe className="h-4 w-4 text-muted-foreground" />
							<h3 className="text-sm font-semibold m-0">{ __( 'General', 'sales-pulse' ) }</h3>
						</div>
						<div className="space-y-4">
							<div className="space-y-1.5">
								<Label>{ __( 'Timezone', 'sales-pulse' ) }</Label>
								<Input value={ form.timezone || '' } disabled className="bg-muted" />
								<p className="text-xs text-muted-foreground">
									{ __( 'Inherited from WordPress settings.', 'sales-pulse' ) }
								</p>
							</div>

							<div className="space-y-1.5">
								<Label>{ __( 'Revenue Basis', 'sales-pulse' ) }</Label>
								<Select
									value={ form.revenue_basis || 'net' }
									onValueChange={ ( val ) => handleChange( 'revenue_basis', val ) }
								>
									<SelectTrigger>
										<SelectValue />
									</SelectTrigger>
									<SelectContent>
										<SelectItem value="net">{ __( 'Net Revenue (after discounts & refunds)', 'sales-pulse' ) }</SelectItem>
										<SelectItem value="gross">{ __( 'Gross Revenue (before discounts)', 'sales-pulse' ) }</SelectItem>
									</SelectContent>
								</Select>
							</div>

							<div className="space-y-1.5">
								<Label>{ __( 'Currency', 'sales-pulse' ) }</Label>
								<Input value={ `${ form.currency || '' } (${ form.currency_symbol || '' })` } disabled className="bg-muted" />
								<p className="text-xs text-muted-foreground">
									{ __( 'Inherited from WooCommerce settings.', 'sales-pulse' ) }
								</p>
							</div>
						</div>
					</div>
				</Card>

				{ /* Snapshot Schedule */ }
				<Card className="border border-solid">
					<div className="p-5">
						<div className="flex items-center gap-2 mb-4">
							<Clock className="h-4 w-4 text-muted-foreground" />
							<h3 className="text-sm font-semibold m-0">{ __( 'Snapshot Schedule', 'sales-pulse' ) }</h3>
						</div>
						<div className="space-y-4">
							<div className="grid grid-cols-2 gap-4">
								<div className="space-y-1.5">
									<Label>{ __( 'Hour (0-23)', 'sales-pulse' ) }</Label>
									<Input
										type="number"
										min="0"
										max="23"
										value={ form.snapshot_hour ?? 2 }
										onChange={ ( e ) => handleChange( 'snapshot_hour', e.target.value ) }
									/>
								</div>
								<div className="space-y-1.5">
									<Label>{ __( 'Minute (0-59)', 'sales-pulse' ) }</Label>
									<Input
										type="number"
										min="0"
										max="59"
										value={ form.snapshot_min ?? 10 }
										onChange={ ( e ) => handleChange( 'snapshot_min', e.target.value ) }
									/>
								</div>
							</div>
							<p className="text-xs text-muted-foreground">
								{ __( 'Default: 02:10 AM. The nightly snapshot runs at this time in your store timezone.', 'sales-pulse' ) }
							</p>
						</div>
					</div>
				</Card>

				{ /* Email Digest */ }
				<Card className="border border-solid">
					<div className="p-5">
						<div className="flex items-center gap-2 mb-4">
							<Mail className="h-4 w-4 text-muted-foreground" />
							<h3 className="text-sm font-semibold m-0">{ __( 'Email Digest', 'sales-pulse' ) }</h3>
						</div>
						<div className="space-y-4">
							<div className="flex items-center justify-between">
								<Label htmlFor="email-toggle">{ __( 'Enable email digest', 'sales-pulse' ) }</Label>
								<Switch
									id="email-toggle"
									checked={ !! form.email_enabled }
									onCheckedChange={ ( val ) => handleChange( 'email_enabled', val ) }
								/>
							</div>
							{ form.email_enabled && (
								<div className="space-y-1.5">
									<Label htmlFor="email-address">{ __( 'Recipient email', 'sales-pulse' ) }</Label>
									<Input
										id="email-address"
										type="email"
										value={ form.email_address || '' }
										onChange={ ( e ) => handleChange( 'email_address', e.target.value ) }
										placeholder={ __( 'admin@yourstore.com', 'sales-pulse' ) }
									/>
								</div>
							) }
						</div>
					</div>
				</Card>
			</div>
		</div>
	);
}
