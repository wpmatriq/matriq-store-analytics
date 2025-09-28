import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import { MetricCard } from "@Components/MetricCard";
import {
  Users,
  Eye,
  MousePointer,
  Clock,
  Globe
} from "lucide-react";
import { ResponsiveContainer, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, BarChart, Bar, PieChart, Pie, Cell } from "recharts";

const trafficData = [
  { day: "Mon", visitors: 1200, pageViews: 4500, bounceRate: 45 },
  { day: "Tue", visitors: 1350, pageViews: 5100, bounceRate: 42 },
  { day: "Wed", visitors: 1100, pageViews: 4200, bounceRate: 48 },
  { day: "Thu", visitors: 1420, pageViews: 5800, bounceRate: 38 },
  { day: "Fri", visitors: 1580, pageViews: 6200, bounceRate: 35 },
  { day: "Sat", visitors: 1750, pageViews: 6800, bounceRate: 40 },
  { day: "Sun", visitors: 1680, pageViews: 6500, bounceRate: 43 },
];

const deviceData = [
  { name: "Desktop", value: 55, color: "hsl(var(--chart-primary))" },
  { name: "Mobile", value: 35, color: "hsl(var(--chart-secondary))" },
  { name: "Tablet", value: 10, color: "hsl(var(--chart-tertiary))" },
];

const topPages = [
  { page: "/products/wireless-headphones", views: 2450, bounce: "32%" },
  { page: "/", views: 2100, bounce: "28%" },
  { page: "/products/smart-watch", views: 1890, bounce: "35%" },
  { page: "/categories/electronics", views: 1650, bounce: "42%" },
  { page: "/products/phone-case", views: 1420, bounce: "38%" },
];

export default function TrafficAnalysisPage() {
  return (
	<div className="space-y-6">
	  <div>
		<h1 className="text-3xl font-bold m-0 p-0">Traffic Analysis</h1>
		<p className="text-base text-muted-foreground m-0 p-0">Detailed insights into your website traffic</p>
	  </div>

	  {/* Traffic Metrics */}
	  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
		<MetricCard
		  title="Total Visitors"
		  value="9,580"
		  change="+15.3% from last week"
		  changeType="increase"
		  icon={<Users className="h-6 w-6 text-primary" />}
		/>
		<MetricCard
		  title="Page Views"
		  value="38,200"
		  change="+12.8% from last week"
		  changeType="increase"
		  icon={<Eye className="h-6 w-6 text-chart-secondary" />}
		/>
		<MetricCard
		  title="Avg. Session Duration"
		  value="3m 24s"
		  change="+8.1% from last week"
		  changeType="increase"
		  icon={<Clock className="h-6 w-6 text-chart-tertiary" />}
		/>
		<MetricCard
		  title="Bounce Rate"
		  value="38.7%"
		  change="-2.4% from last week"
		  changeType="increase"
		  icon={<MousePointer className="h-6 w-6 text-chart-quaternary" />}
		/>
	  </div>

	  <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
		{/* Traffic Trend */}
		<Card>
		  <CardHeader>
			<CardTitle>Weekly Traffic Trend</CardTitle>
		  </CardHeader>
		  <CardContent className="p-6">
			<ResponsiveContainer width="100%" height={300}>
			  <LineChart data={trafficData}>
				<CartesianGrid strokeDasharray="3 3" className="opacity-30" />
				<XAxis dataKey="day" />
				<YAxis />
				<Tooltip />
				<Line
				  type="monotone"
				  dataKey="visitors"
				  stroke="hsl(var(--chart-primary))"
				  strokeWidth={3}
				  name="Visitors"
				/>
				<Line
				  type="monotone"
				  dataKey="pageViews"
				  stroke="hsl(var(--chart-secondary))"
				  strokeWidth={3}
				  name="Page Views"
				/>
			  </LineChart>
			</ResponsiveContainer>
		  </CardContent>
		</Card>

		{/* Device Breakdown */}
		<Card>
		  <CardHeader>
			<CardTitle>Device Usage</CardTitle>
		  </CardHeader>
		  <CardContent className="p-6">
			<ResponsiveContainer width="100%" height={300}>
			  <PieChart>
				<Pie
				  data={deviceData}
				  cx="50%"
				  cy="50%"
				  innerRadius={60}
				  outerRadius={120}
				  paddingAngle={5}
				  dataKey="value"
				>
				  {deviceData.map((entry, index) => (
					<Cell key={`cell-${index}`} fill={entry.color} />
				  ))}
				</Pie>
				<Tooltip formatter={(value) => [`${value}%`, "Usage"]} />
			  </PieChart>
			</ResponsiveContainer>
			<div className="flex flex-wrap gap-4 mt-4">
			  {deviceData.map((device, index) => (
				<div key={index} className="flex items-center gap-2">
				  <div
					className="w-3 h-3 rounded-full"
					style={{ backgroundColor: device.color }}
				  />
				  <span className="text-sm text-muted-foreground">{device.name} ({device.value}%)</span>
				</div>
			  ))}
			</div>
		  </CardContent>
		</Card>
	  </div>

	  {/* Top Pages */}
	  <Card>
		<CardHeader>
		  <CardTitle>Top Pages</CardTitle>
		</CardHeader>
		<CardContent className="p-6">
		  <div className="space-y-4">
			{topPages.map((page, index) => (
			  <div key={index} className="flex items-center justify-between p-4 rounded-lg bg-muted/20">
				<div className="flex-1">
				  <p className="font-medium text-sm">{page.page}</p>
				</div>
				<div className="flex items-center gap-8 text-sm">
				  <div className="text-center">
					<p className="font-semibold">{page.views.toLocaleString()}</p>
					<p className="text-muted-foreground text-xs">Views</p>
				  </div>
				  <div className="text-center">
					<p className="font-semibold">{page.bounce}</p>
					<p className="text-muted-foreground text-xs">Bounce Rate</p>
				  </div>
				</div>
			  </div>
			))}
		  </div>
		</CardContent>
	  </Card>

	  {/* Referrer Sources */}
	  <Card>
		<CardHeader>
		  <CardTitle>Top Referrer Sources</CardTitle>
		</CardHeader>
		<CardContent className="p-6">
		  <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
			<div className="p-4 rounded-lg bg-muted/20 text-center">
			  <Globe className="h-8 w-8 mx-auto mb-2 text-chart-primary" />
			  <p className="font-semibold">Google</p>
			  <p className="text-2xl font-bold">45.2%</p>
			  <p className="text-xs text-muted-foreground">4,320 visitors</p>
			</div>
			<div className="p-4 rounded-lg bg-muted/20 text-center">
			  <Globe className="h-8 w-8 mx-auto mb-2 text-chart-secondary" />
			  <p className="font-semibold">Direct</p>
			  <p className="text-2xl font-bold">30.1%</p>
			  <p className="text-xs text-muted-foreground">2,880 visitors</p>
			</div>
			<div className="p-4 rounded-lg bg-muted/20 text-center">
			  <Globe className="h-8 w-8 mx-auto mb-2 text-chart-tertiary" />
			  <p className="font-semibold">Facebook</p>
			  <p className="text-2xl font-bold">12.4%</p>
			  <p className="text-xs text-muted-foreground">1,190 visitors</p>
			</div>
			<div className="p-4 rounded-lg bg-muted/20 text-center">
			  <Globe className="h-8 w-8 mx-auto mb-2 text-chart-quaternary" />
			  <p className="font-semibold">Others</p>
			  <p className="text-2xl font-bold">12.3%</p>
			  <p className="text-xs text-muted-foreground">1,180 visitors</p>
			</div>
		  </div>
		</CardContent>
	  </Card>
	</div>
  );
}
