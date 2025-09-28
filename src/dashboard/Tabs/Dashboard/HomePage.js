import React from 'react';
import { __ } from '@wordpress/i18n';
import { MetricCard } from "@Components/MetricCard";
import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import {
  DollarSign,
  ShoppingCart,
  Users,
  TrendingUp,
  Package,
  Eye,
  Target,
  Clock
} from "lucide-react";
import { ResponsiveContainer, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, BarChart, Bar, PieChart, Pie, Cell } from "recharts";

const revenueData = [
  { month: "Jan", revenue: 45000, orders: 280 },
  { month: "Feb", revenue: 52000, orders: 320 },
  { month: "Mar", revenue: 48000, orders: 295 },
  { month: "Apr", revenue: 61000, orders: 380 },
  { month: "May", revenue: 55000, orders: 340 },
  { month: "Jun", revenue: 67000, orders: 420 },
];

const topProducts = [
  { name: "Wireless Headphones", sales: 1250, revenue: 89500 },
  { name: "Smart Watch", sales: 980, revenue: 147000 },
  { name: "Phone Case", sales: 2100, revenue: 52500 },
  { name: "Bluetooth Speaker", sales: 750, revenue: 67500 },
];

const trafficSources = [
  { name: "Organic Search", value: 45, color: "hsl(var(--chart-primary))" },
  { name: "Direct", value: 30, color: "hsl(var(--chart-secondary))" },
  { name: "Social Media", value: 15, color: "hsl(var(--chart-tertiary))" },
  { name: "Paid Ads", value: 10, color: "hsl(var(--chart-quaternary))" },
];

export default function HomePage() {
  return (
	<div className="space-y-6">
	  <div>
		<h1 className="text-3xl font-bold m-0 p-0">Dashboard</h1>
		<p className="text-base text-muted-foreground m-0 p-0">Overview of your store performance</p>
	  </div>

	  {/* Key Metrics */}
	  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
		<MetricCard
		  title="Total Revenue"
		  value="$67,430"
		  change="+12.5% from last month"
		  changeType="increase"
		  icon={<DollarSign className="h-6 w-6 text-primary" />}
		/>
		<MetricCard
		  title="Total Orders"
		  value="1,247"
		  change="+8.2% from last month"
		  changeType="increase"
		  icon={<ShoppingCart className="h-6 w-6 text-chart-secondary" />}
		/>
		<MetricCard
		  title="New Customers"
		  value="321"
		  change="+5.1% from last month"
		  changeType="increase"
		  icon={<Users className="h-6 w-6 text-chart-tertiary" />}
		/>
		<MetricCard
		  title="Conversion Rate"
		  value="3.24%"
		  change="+0.4% from last month"
		  changeType="increase"
		  icon={<Target className="h-6 w-6 text-chart-quaternary" />}
		/>
	  </div>

	  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
		{/* Revenue Chart */}
		<Card>
		  <CardHeader className="pb-0">
			<CardTitle className="flex items-center gap-2">
			  <TrendingUp className="h-5 w-5" />
			  	Revenue Trend
			</CardTitle>
		  </CardHeader>
		  <CardContent className="p-6">
			<ResponsiveContainer width="100%" height={300}>
			  <LineChart data={revenueData}>
				<CartesianGrid strokeDasharray="3 3" className="opacity-30" />
				<XAxis dataKey="month" />
				<YAxis />
				<Tooltip
				  formatter={(value, name) => [
					name === 'revenue' ? `$${value.toLocaleString()}` : value,
					name === 'revenue' ? 'Revenue' : 'Orders'
				  ]}
				/>
				<Line
				  type="monotone"
				  dataKey="revenue"
				  stroke="hsl(var(--chart-primary))"
				  strokeWidth={3}
				  dot={{ fill: "hsl(var(--chart-primary))" }}
				/>
			  </LineChart>
			</ResponsiveContainer>
		  </CardContent>
		</Card>

		{/* Traffic Sources */}
		<Card>
		  <CardHeader className="pb-0">
			<CardTitle className="flex items-center gap-2">
			  <Eye className="h-5 w-5" />
			  Traffic Sources
			</CardTitle>
		  </CardHeader>
		  <CardContent className="p-6">
			<ResponsiveContainer width="100%" height={300}>
			  <PieChart>
				<Pie
				  data={trafficSources}
				  cx="50%"
				  cy="50%"
				  innerRadius={60}
				  outerRadius={120}
				  paddingAngle={5}
				  dataKey="value"
				>
				  {trafficSources.map((entry, index) => (
					<Cell key={`cell-${index}`} fill={entry.color} />
				  ))}
				</Pie>
				<Tooltip formatter={(value) => [`${value}%`, "Share"]} />
			  </PieChart>
			</ResponsiveContainer>
			<div className="flex flex-wrap gap-4 mt-4">
			  {trafficSources.map((source, index) => (
				<div key={index} className="flex items-center gap-2">
				  <div
					className="w-3 h-3 rounded-full"
					style={{ backgroundColor: source.color }}
				  />
				  <span className="text-sm text-muted-foreground">{source.name}</span>
				</div>
			  ))}
			</div>
		  </CardContent>
		</Card>
	  </div>

	  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
		{/* Top Products */}
		<Card>
		  <CardHeader className="pb-0">
			<CardTitle className="flex items-center gap-2">
			  <Package className="h-5 w-5" />
			  Top Products
			</CardTitle>
		  </CardHeader>
		  <CardContent className="p-6">
			<div className="space-y-4">
			  {topProducts.map((product, index) => (
				<div key={index} className="flex items-center justify-between p-3 rounded-lg bg-muted/20">
				  <div>
					<h3 className="font-medium text-base m-0 p-0">{product.name}</h3>
					<p className="text-sm text-muted-foreground">{product.sales} units sold</p>
				  </div>
				  <div className="text-right">
					<p className="font-semibold">${product.revenue.toLocaleString()}</p>
				  </div>
				</div>
			  ))}
			</div>
		  </CardContent>
		</Card>

		{/* Recent Activity */}
		<Card>
		  <CardHeader className="pb-0">
			<CardTitle className="flex items-center gap-2">
			  <Clock className="h-5 w-5" />
			  Recent Activity
			</CardTitle>
		  </CardHeader>
		  <CardContent className="p-6">
			<div className="space-y-4">
			  <div className="flex items-center gap-3 p-3 rounded-lg bg-muted/20">
				<div className="w-2 h-2 bg-chart-secondary rounded-full"></div>
				<div className="flex-1">
				  <p className="text-sm font-medium">New order #1247</p>
				  <p className="text-xs text-muted-foreground">2 minutes ago</p>
				</div>
				<span className="text-sm font-semibold">$156.00</span>
			  </div>
			  <div className="flex items-center gap-3 p-3 rounded-lg bg-muted/20">
				<div className="w-2 h-2 bg-chart-tertiary rounded-full"></div>
				<div className="flex-1">
				  <p className="text-sm font-medium">Product review added</p>
				  <p className="text-xs text-muted-foreground">5 minutes ago</p>
				</div>
				<span className="text-xs bg-success/10 text-success px-2 py-1 rounded">5★</span>
			  </div>
			  <div className="flex items-center gap-3 p-3 rounded-lg bg-muted/20">
				<div className="w-2 h-2 bg-chart-quaternary rounded-full"></div>
				<div className="flex-1">
				  <p className="text-sm font-medium">New customer registered</p>
				  <p className="text-xs text-muted-foreground">12 minutes ago</p>
				</div>
			  </div>
			  <div className="flex items-center gap-3 p-3 rounded-lg bg-muted/20">
				<div className="w-2 h-2 bg-chart-primary rounded-full"></div>
				<div className="flex-1">
				  <p className="text-sm font-medium">Inventory alert</p>
				  <p className="text-xs text-muted-foreground">18 minutes ago</p>
				</div>
				<span className="text-xs bg-warning/10 text-warning px-2 py-1 rounded">Low Stock</span>
			  </div>
			</div>
		  </CardContent>
		</Card>
	  </div>
	</div>
  );
}
