import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import { MetricCard } from "@Components/MetricCard";
import { Input } from "@Components/ui/input";
import { Button } from "@Components/ui/button";
import { BarChart2, Search, Package, DollarSign, TrendingUp, Eye } from "lucide-react";
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, LineChart, Line } from "recharts";

const productAnalytics = [
  {
    name: "Wireless Headphones",
    sales: 1250,
    revenue: 125000,
    profit: 45000,
    views: 15600,
    conversion: 8.0,
    category: "Electronics"
  },
  {
    name: "Smart Watch",
    sales: 980,
    revenue: 147000,
    profit: 58800,
    views: 12400,
    conversion: 7.9,
    category: "Electronics"
  },
  {
    name: "Cotton T-Shirt",
    sales: 2100,
    revenue: 52500,
    profit: 23625,
    views: 28000,
    conversion: 7.5,
    category: "Clothing"
  },
  {
    name: "Yoga Mat",
    sales: 750,
    revenue: 67500,
    profit: 33750,
    views: 9800,
    conversion: 7.7,
    category: "Sports"
  },
  {
    name: "Coffee Maker",
    sales: 450,
    revenue: 67500,
    profit: 27000,
    views: 7200,
    conversion: 6.3,
    category: "Home"
  },
];

const performanceTrend = [
  { week: "Week 1", sales: 180, views: 2400, conversion: 7.5 },
  { week: "Week 2", sales: 210, views: 2800, conversion: 7.5 },
  { week: "Week 3", sales: 165, views: 2200, conversion: 7.5 },
  { week: "Week 4", sales: 245, views: 3100, conversion: 7.9 },
];

export default function ProductWiseReports() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold m-0 p-0">Product-Wise Reports</h1>
        <p className="text-base text-muted-foreground m-0 p-0">Detailed analysis for individual products</p>
      </div>

      {/* Search and Filter */}
      <Card>
        <CardContent className="p-6">
          <div className="flex gap-4">
            <div className="flex-1">
              <div className="relative">
                <Search className="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Search products..."
                  className="pl-9"
                />
              </div>
            </div>
            <Button variant="outline">
              Filter by Category
            </Button>
            <Button>
              Export Report
            </Button>
          </div>
        </CardContent>
      </Card>

      {/* Overview Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Total Products Analyzed"
          value="1,247"
          change="+23 new products"
          changeType="increase"
          icon={<Package className="h-6 w-6 text-primary" />}
        />
        <MetricCard
          title="Total Revenue"
          value="$459,500"
          change="+18.3% from last month"
          changeType="increase"
          icon={<DollarSign className="h-6 w-6 text-chart-secondary" />}
        />
        <MetricCard
          title="Avg. Conversion Rate"
          value="7.4%"
          change="+0.5% from last month"
          changeType="increase"
          icon={<TrendingUp className="h-6 w-6 text-chart-tertiary" />}
        />
        <MetricCard
          title="Total Product Views"
          value="73,000"
          change="+12.8% from last month"
          changeType="increase"
          icon={<Eye className="h-6 w-6 text-chart-quaternary" />}
        />
      </div>

      {/* Product Performance Chart */}
      <Card>
        <CardHeader>
          <CardTitle>Top Products Revenue Comparison</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={productAnalytics}>
              <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
              <XAxis dataKey="name" angle={-45} textAnchor="end" height={100} />
              <YAxis />
              <Tooltip
                formatter={(value, name) => [
                  name === 'revenue' ? `$${value.toLocaleString()}` :
                  name === 'profit' ? `$${value.toLocaleString()}` : value,
                  name === 'revenue' ? 'Revenue' :
                  name === 'profit' ? 'Profit' : 'Sales'
                ]}
              />
              <Bar dataKey="revenue" fill="hsl(var(--chart-primary))" name="revenue" />
              <Bar dataKey="profit" fill="hsl(var(--chart-secondary))" name="profit" />
            </BarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Conversion Rate Trend */}
        <Card>
          <CardHeader>
            <CardTitle>Conversion Rate Trend</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <ResponsiveContainer width="100%" height={250}>
              <LineChart data={performanceTrend}>
                <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
                <XAxis dataKey="week" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => [
                    name === 'conversion' ? `${value}%` : value,
                    name === 'conversion' ? 'Conversion Rate' : 'Sales'
                  ]}
                />
                <Line
                  type="monotone"
                  dataKey="conversion"
                  stroke="hsl(var(--chart-tertiary))"
                  strokeWidth={3}
                  name="conversion"
                />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Product Categories */}
        <Card>
          <CardHeader>
            <CardTitle>Performance by Category</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <div className="space-y-4">
              {Array.from(new Set(productAnalytics.map(p => p.category))).map((category) => {
                const categoryProducts = productAnalytics.filter(p => p.category === category);
                const totalRevenue = categoryProducts.reduce((sum, p) => sum + p.revenue, 0);
                const totalSales = categoryProducts.reduce((sum, p) => sum + p.sales, 0);

                return (
                  <div key={category} className="p-4 rounded-lg bg-muted/20">
                    <div className="flex justify-between items-center mb-2">
                      <h3 className="font-semibold">{category}</h3>
                      <span className="text-sm text-muted-foreground">
                        {categoryProducts.length} products
                      </span>
                    </div>
                    <div className="grid grid-cols-2 gap-4">
                      <div>
                        <p className="text-sm text-muted-foreground">Revenue</p>
                        <p className="text-lg font-bold">${totalRevenue.toLocaleString()}</p>
                      </div>
                      <div>
                        <p className="text-sm text-muted-foreground">Sales</p>
                        <p className="text-lg font-bold">{totalSales}</p>
                      </div>
                    </div>
                  </div>
                );
              })}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Detailed Product Table */}
      <Card>
        <CardHeader>
          <CardTitle>Detailed Product Analysis</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left p-4 font-medium">Product Name</th>
                  <th className="text-left p-4 font-medium">Category</th>
                  <th className="text-right p-4 font-medium">Sales</th>
                  <th className="text-right p-4 font-medium">Views</th>
                  <th className="text-right p-4 font-medium">Conversion</th>
                  <th className="text-right p-4 font-medium">Revenue</th>
                  <th className="text-right p-4 font-medium">Profit</th>
                </tr>
              </thead>
              <tbody>
                {productAnalytics.map((product, index) => (
                  <tr key={index} className="border-b hover:bg-muted/20">
                    <td className="p-4 font-medium">{product.name}</td>
                    <td className="p-4">
                      <span className="px-2 py-1 bg-primary/10 text-primary rounded-md text-sm">
                        {product.category}
                      </span>
                    </td>
                    <td className="p-4 text-right font-medium">{product.sales}</td>
                    <td className="p-4 text-right font-medium">{product.views.toLocaleString()}</td>
                    <td className="p-4 text-right font-medium">{product.conversion}%</td>
                    <td className="p-4 text-right font-medium">${product.revenue.toLocaleString()}</td>
                    <td className="p-4 text-right font-medium text-success">
                      ${product.profit.toLocaleString()}
                    </td>
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
