import React from 'react';
import { __ } from '@wordpress/i18n';

import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import { Button } from "@Components/ui/button";
import { Input } from "@Components/ui/input";
import { Label } from "@Components/ui/label";
import { Switch } from "@Components/ui/switch";
import { Separator } from "@Components/ui/separator";
import {
  Settings as SettingsIcon,
  Database,
  Bell,
  Shield,
  Palette,
  Download,
  Trash2
} from "lucide-react";

export default function SettingsPage() {
  return (
	<div className="space-y-6">
	  <div className="flex justify-between">
		<div>
			<h1 className="text-3xl font-bold m-0 p-0">Settings</h1>
			<p className="text-base text-muted-foreground m-0 p-0">Configure your store analytics preferences</p>
		</div>

	  {/* Save Button */}
		<Button size="lg" className="bg-gradient-to-r from-primary to-accent">
		  Save All Changes
		</Button>
	  </div>

	  <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
		{/* General Settings */}
		<Card className="lg:col-span-2">
		  <CardHeader>
			<CardTitle className="flex items-center gap-2">
			  <SettingsIcon className="h-5 w-5" />
			  General Settings
			</CardTitle>
		  </CardHeader>
		  <CardContent className="space-y-6 p-6">
			<div className="space-y-2">
			  <Label htmlFor="store-name">Store Name</Label>
			  <Input id="store-name" placeholder="Your Store Name" defaultValue="My eCommerce Store" />
			</div>

			<div className="space-y-2">
			  <Label htmlFor="currency">Default Currency</Label>
			  <Input id="currency" placeholder="USD" defaultValue="USD" />
			</div>

			<div className="space-y-2">
			  <Label htmlFor="timezone">Timezone</Label>
			  <Input id="timezone" placeholder="UTC" defaultValue="America/New_York" />
			</div>

			<Separator />

			<div className="space-y-4">
			  <h3 className="text-lg font-semibold">Display Preferences</h3>

			  <div className="flex items-center justify-between">
				<div>
				  <h4 className='text-base m-0 p-0 font-normal'>Show percentage changes</h4>
				  <p className="text-sm text-muted-foreground">Display percentage changes in metric cards</p>
				</div>
				<Switch defaultChecked />
			  </div>

			  <div className="flex items-center justify-between">
				<div>
				  <h4 className='text-base m-0 p-0 font-normal'>Compact view</h4>
				  <p className="text-sm text-muted-foreground">Use compact layout for tables and charts</p>
				</div>
				<Switch />
			  </div>

			  <div className="flex items-center justify-between">
				<div>
				  <h4 className='text-base m-0 p-0 font-normal'>Auto-refresh data</h4>
				  <p className="text-sm text-muted-foreground">Automatically refresh dashboard data every 5 minutes</p>
				</div>
				<Switch defaultChecked />
			  </div>
			</div>
		  </CardContent>
		</Card>

		{/* Data & Privacy */}
		<Card>
		  <CardHeader>
			<CardTitle className="flex items-center gap-2">
			  <Shield className="h-5 w-5" />
			  Data & Privacy
			</CardTitle>
		  </CardHeader>
		  <CardContent className="space-y-4 p-6">
			<div className="space-y-2">
			  <Label htmlFor="data-retention">Data retention period</Label>
			  <Input id="data-retention" placeholder="365 days" defaultValue="365" />
			  <p className="text-sm text-muted-foreground">How long to keep historical data</p>
			</div>

			<div className="flex items-center justify-between">
			  <div>
				<h4 className='text-base m-0 p-0 font-normal'>Anonymous analytics</h4>
				<p className="text-sm text-muted-foreground">Share anonymous usage data to improve the service</p>
			  </div>
			  <Switch />
			</div>

			<div className="flex items-center justify-between">
			  <div>
				<h4 className='text-base m-0 p-0 font-normal'>GDPR compliance</h4>
				<p className="text-sm text-muted-foreground">Enable GDPR compliance features</p>
			  </div>
			  <Switch defaultChecked />
			</div>

			<Separator />

			<div className="space-y-4">
			  <Button className="w-full" variant="outline">
				Download Your Data
			  </Button>
			  <Button className="w-full" variant="destructive">
				Delete All Data
			  </Button>
			</div>
		  </CardContent>
		</Card>
	  </div>

	  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
		{/* Notifications */}
		<Card>
		  <CardHeader>
			<CardTitle className="flex items-center gap-2">
			  <Bell className="h-5 w-5" />
			  Notifications
			</CardTitle>
		  </CardHeader>
		  <CardContent className="space-y-4 p-6">
			<div className="flex items-center justify-between">
			  <div>
				<h4 className='text-base m-0 p-0 font-normal'>Email reports</h4>
				<p className="text-sm text-muted-foreground">Receive daily/weekly reports via email</p>
			  </div>
			  <Switch defaultChecked />
			</div>

			<div className="flex items-center justify-between">
			  <div>
				<h4 className='text-base m-0 p-0 font-normal'>Low stock alerts</h4>
				<p className="text-sm text-muted-foreground">Get notified when products are low in stock</p>
			  </div>
			  <Switch defaultChecked />
			</div>

			<div className="flex items-center justify-between">
			  <div>
				<h4 className='text-base m-0 p-0 font-normal'>Sales milestones</h4>
				<p className="text-sm text-muted-foreground">Celebrate when reaching sales goals</p>
			  </div>
			  <Switch />
			</div>

			<div className="flex items-center justify-between">
			  <div>
				<h4 className='text-base m-0 p-0 font-normal'>Security alerts</h4>
				<p className="text-sm text-muted-foreground">Important security notifications</p>
			  </div>
			  <Switch defaultChecked />
			</div>
		  </CardContent>
		</Card>

		{/* Quick Actions */}
		<Card>
		  <CardHeader>
			<CardTitle>Quick Actions</CardTitle>
		  </CardHeader>
		  <CardContent className="space-y-4 p-6">
			<Button className="w-full justify-start" variant="outline">
			  <Download className="mr-2 h-4 w-4" />
			  Export Data
			</Button>

			<Button className="w-full justify-start" variant="outline">
			  <Database className="mr-2 h-4 w-4" />
			  Sync Database
			</Button>

			<Button className="w-full justify-start" variant="outline">
			  <Palette className="mr-2 h-4 w-4" />
			  Customize Theme
			</Button>

			<Separator />

			<Button className="w-full justify-start" variant="default">
			  <Trash2 className="mr-2 h-4 w-4" />
			  Clear Cache
			</Button>
		  </CardContent>
		</Card>
	  </div>
	</div>
  );
}
