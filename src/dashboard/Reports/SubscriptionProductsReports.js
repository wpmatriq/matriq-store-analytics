import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import { MetricCard } from "@Components/MetricCard";
import { RefreshCw, DollarSign, Users, TrendingUp } from "lucide-react";
import { ResponsiveContainer, LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip, PieChart, Pie, Cell } from "recharts";

const subscriptionGrowth = [
  { month: "Jan", subscribers: 1200, revenue: 36000, churn: 5.2 },
  { month: "Feb", subscribers: 1350, revenue: 40500, churn: 4.8 },
  { month: "Mar", subscribers: 1520, revenue: 45600, churn: 4.1 },
  { month: "Apr", subscribers: 1680, revenue: 50400, churn: 3.9 },
  { month: "May", subscribers: 1850, revenue: 55500, churn: 3.5 },
  { month: "Jun", subscribers: 2020, revenue: 60600, churn: 3.2 },
];

const subscriptionPlans = [
  { name: "Basic", subscribers: 820, revenue: 16400, color: "hsl(var(--chart-primary))" },
  { name: "Premium", subscribers: 680, revenue: 34000, color: "hsl(var(--chart-secondary))" },
  { name: "Professional", subscribers: 340, revenue: 27200, color: "hsl(var(--chart-tertiary))" },
  { name: "Enterprise", subscribers: 180, revenue: 18000, color: "hsl(var(--chart-quaternary))" },
];

const recentSubscriptions = [
  { customer: "John Doe", plan: "Premium", amount: 50, status: "Active", date: "2024-01-15" },
  { customer: "Jane Smith", plan: "Basic", amount: 20, status: "Active", date: "2024-01-14" },
  { customer: "Mike Johnson", plan: "Professional", amount: 80, status: "Cancelled", date: "2024-01-13" },
  { customer: "Sarah Wilson", plan: "Enterprise", amount: 100, status: "Active", date: "2024-01-12" },
  { customer: "David Brown", plan: "Premium", amount: 50, status: "Active", date: "2024-01-11" },
];

export default function SubscriptionProductsReports() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold m-0 p-0">Subscription Products Report</h1>
        <p className="text-base text-muted-foreground m-0 p-0">Track recurring revenue and subscription metrics</p>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Active Subscribers"
          value="2,020"
          change="+9.2% from last month"
          changeType="increase"
          icon={<Users className="h-6 w-6 text-primary" />}
        />
        <MetricCard
          title="Monthly Recurring Revenue"
          value="$60,600"
          change="+15.4% from last month"
          changeType="increase"
          icon={<DollarSign className="h-6 w-6 text-chart-secondary" />}
        />
        <MetricCard
          title="Churn Rate"
          value="3.2%"
          change="-0.3% from last month"
          changeType="increase"
          icon={<TrendingUp className="h-6 w-6 text-chart-tertiary" />}
        />
        <MetricCard
          title="Avg. Revenue per User"
          value="$30.00"
          change="+2.1% from last month"
          changeType="increase"
          icon={<RefreshCw className="h-6 w-6 text-chart-quaternary" />}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Subscription Growth */}
        <Card>
          <CardHeader>
            <CardTitle>Subscription Growth Trend</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={subscriptionGrowth}>
                <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => [
                    name === 'revenue' ? `$${value.toLocaleString()}` :
                    name === 'churn' ? `${value}%` : value,
                    name === 'revenue' ? 'Revenue' :
                    name === 'churn' ? 'Churn Rate' : 'Subscribers'
                  ]}
                />
                <Line
                  type="monotone"
                  dataKey="subscribers"
                  stroke="hsl(var(--chart-primary))"
                  strokeWidth={3}
                  name="subscribers"
                />
                <Line
                  type="monotone"
                  dataKey="revenue"
                  stroke="hsl(var(--chart-secondary))"
                  strokeWidth={3}
                  name="revenue"
                />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Plan Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Subscription Plan Distribution</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <ResponsiveContainer width="100%" height={300}>
              <PieChart>
                <Pie
                  data={subscriptionPlans}
                  cx="50%"
                  cy="50%"
                  innerRadius={60}
                  outerRadius={120}
                  paddingAngle={5}
                  dataKey="subscribers"
                >
                  {subscriptionPlans.map((entry, index) => (
                    <Cell key={`cell-${index}`} fill={entry.color} />
                  ))}
                </Pie>
                <Tooltip formatter={(value) => [value, "Subscribers"]} />
              </PieChart>
            </ResponsiveContainer>
            <div className="flex flex-wrap gap-4 mt-4">
              {subscriptionPlans.map((plan, index) => (
                <div key={index} className="flex items-center gap-2">
                  <div
                    className="w-3 h-3 rounded-full"
                    style={{ backgroundColor: plan.color }}
                  />
                  <span className="text-sm text-muted-foreground">
                    {plan.name} ({plan.subscribers})
                  </span>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Plan Performance */}
      <Card>
        <CardHeader>
          <CardTitle>Plan Performance Overview</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {subscriptionPlans.map((plan, index) => (
              <div key={index} className="p-4 rounded-lg bg-muted/20">
                <div className="flex items-center justify-between mb-3">
                  <h3 className="font-semibold">{plan.name}</h3>
                  <div
                    className="w-3 h-3 rounded-full"
                    style={{ backgroundColor: plan.color }}
                  />
                </div>
                <div className="space-y-2">
                  <div className="flex justify-between">
                    <span className="text-sm text-muted-foreground">Subscribers</span>
                    <span className="font-medium">{plan.subscribers}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-muted-foreground">Revenue</span>
                    <span className="font-medium">${plan.revenue.toLocaleString()}</span>
                  </div>
                  <div className="flex justify-between">
                    <span className="text-sm text-muted-foreground">Avg/User</span>
                    <span className="font-medium">${(plan.revenue / plan.subscribers).toFixed(2)}</span>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </CardContent>
      </Card>

      {/* Recent Subscriptions */}
      <Card>
        <CardHeader>
          <CardTitle>Recent Subscription Activity</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left p-4 font-medium">Customer</th>
                  <th className="text-left p-4 font-medium">Plan</th>
                  <th className="text-right p-4 font-medium">Amount</th>
                  <th className="text-center p-4 font-medium">Status</th>
                  <th className="text-right p-4 font-medium">Date</th>
                </tr>
              </thead>
              <tbody>
                {recentSubscriptions.map((subscription, index) => (
                  <tr key={index} className="border-b hover:bg-muted/20">
                    <td className="p-4 font-medium">{subscription.customer}</td>
                    <td className="p-4">
                      <span className="px-2 py-1 bg-primary/10 text-primary rounded-md text-sm">
                        {subscription.plan}
                      </span>
                    </td>
                    <td className="p-4 text-right font-medium">${subscription.amount}</td>
                    <td className="p-4 text-center">
                      <span className={`px-2 py-1 rounded-md text-sm ${
                        subscription.status === 'Active'
                          ? 'bg-success/10 text-success'
                          : 'bg-destructive/10 text-destructive'
                      }`}>
                        {subscription.status}
                      </span>
                    </td>
                    <td className="p-4 text-right text-muted-foreground">{subscription.date}</td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
    </div>
  );
}
