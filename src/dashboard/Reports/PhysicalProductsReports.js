import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import { MetricCard } from "@Components/MetricCard";
import { Package, DollarSign, TrendingUp, ShoppingCart } from "lucide-react";
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, LineChart, Line } from "recharts";

const productPerformance = [
  { name: "Electronics", sales: 4500, revenue: 285000, units: 1250 },
  { name: "Clothing", sales: 3200, revenue: 189000, units: 2100 },
  { name: "Home & Garden", sales: 2800, revenue: 165000, units: 890 },
  { name: "Sports", sales: 2100, revenue: 142000, units: 750 },
  { name: "Books", sales: 1500, revenue: 85000, units: 980 },
];

const monthlyTrend = [
  { month: "Jan", units: 2400, revenue: 145000 },
  { month: "Feb", units: 2800, revenue: 168000 },
  { month: "Mar", units: 2200, revenue: 132000 },
  { month: "Apr", units: 3100, revenue: 186000 },
  { month: "May", units: 2900, revenue: 174000 },
  { month: "Jun", units: 3400, revenue: 204000 },
];

const topProducts = [
  { name: "Wireless Bluetooth Headphones", category: "Electronics", units: 425, revenue: 42500, profit: 15300 },
  { name: "Cotton T-Shirt Pack", category: "Clothing", units: 380, revenue: 19000, profit: 8550 },
  { name: "Yoga Mat Premium", category: "Sports", units: 320, revenue: 25600, profit: 12800 },
  { name: "LED Desk Lamp", category: "Home & Garden", units: 295, revenue: 20650, profit: 9292 },
  { name: "Smartphone Case", category: "Electronics", units: 510, revenue: 15300, profit: 6885 },
];

export default function PhysicalProductsReports() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold m-0 p-0">Physical Products Report</h1>
        <p className="text-base text-muted-foreground m-0 p-0">Comprehensive analysis of physical product performance</p>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Total Units Sold"
          value="18,750"
          change="+14.2% from last month"
          changeType="increase"
          icon={<Package className="h-6 w-6 text-primary" />}
        />
        <MetricCard
          title="Revenue"
          value="$1,124,500"
          change="+18.7% from last month"
          changeType="increase"
          icon={<DollarSign className="h-6 w-6 text-chart-secondary" />}
        />
        <MetricCard
          title="Avg. Order Value"
          value="$84.50"
          change="+5.3% from last month"
          changeType="increase"
          icon={<TrendingUp className="h-6 w-6 text-chart-tertiary" />}
        />
        <MetricCard
          title="Total Orders"
          value="13,310"
          change="+12.1% from last month"
          changeType="increase"
          icon={<ShoppingCart className="h-6 w-6 text-chart-quaternary" />}
        />
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Category Performance */}
        <Card>
          <CardHeader>
            <CardTitle>Performance by Category</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <ResponsiveContainer width="100%" height={300}>
              <BarChart data={productPerformance}>
                <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
                <XAxis dataKey="name" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => [
                    name === 'revenue' ? `$${value.toLocaleString()}` : value,
                    name === 'revenue' ? 'Revenue' : name === 'units' ? 'Units Sold' : 'Sales'
                  ]}
                />
                <Bar dataKey="revenue" fill="hsl(var(--chart-primary))" name="revenue" />
              </BarChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>

        {/* Monthly Trend */}
        <Card>
          <CardHeader>
            <CardTitle>Monthly Sales Trend</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <ResponsiveContainer width="100%" height={300}>
              <LineChart data={monthlyTrend}>
                <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
                <XAxis dataKey="month" />
                <YAxis />
                <Tooltip
                  formatter={(value, name) => [
                    name === 'revenue' ? `$${value.toLocaleString()}` : value,
                    name === 'revenue' ? 'Revenue' : 'Units Sold'
                  ]}
                />
                <Line
                  type="monotone"
                  dataKey="units"
                  stroke="hsl(var(--chart-secondary))"
                  strokeWidth={3}
                  name="units"
                />
                <Line
                  type="monotone"
                  dataKey="revenue"
                  stroke="hsl(var(--chart-primary))"
                  strokeWidth={3}
                  name="revenue"
                />
              </LineChart>
            </ResponsiveContainer>
          </CardContent>
        </Card>
      </div>

      {/* Top Products Table */}
      <Card>
        <CardHeader>
          <CardTitle>Top Performing Products</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left p-4 font-medium">Product Name</th>
                  <th className="text-left p-4 font-medium">Category</th>
                  <th className="text-right p-4 font-medium">Units Sold</th>
                  <th className="text-right p-4 font-medium">Revenue</th>
                  <th className="text-right p-4 font-medium">Profit</th>
                </tr>
              </thead>
              <tbody>
                {topProducts.map((product, index) => (
                  <tr key={index} className="border-b hover:bg-muted/20">
                    <td className="p-4">
                      <div>
                        <p className="font-medium">{product.name}</p>
                      </div>
                    </td>
                    <td className="p-4">
                      <span className="px-2 py-1 bg-primary/10 text-primary rounded-md text-sm">
                        {product.category}
                      </span>
                    </td>
                    <td className="p-4 text-right font-medium">{product.units}</td>
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

      {/* Category Breakdown */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        {productPerformance.slice(0, 3).map((category, index) => (
          <Card key={index}>
            <CardHeader>
              <CardTitle className="text-lg">{category.name}</CardTitle>
            </CardHeader>
            <CardContent className="space-y-4 p-6">
              <div className="flex justify-between">
                <span className="text-muted-foreground">Revenue</span>
                <span className="font-semibold">${category.revenue.toLocaleString()}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Units Sold</span>
                <span className="font-semibold">{category.units}</span>
              </div>
              <div className="flex justify-between">
                <span className="text-muted-foreground">Avg. Price</span>
                <span className="font-semibold">${(category.revenue / category.units).toFixed(2)}</span>
              </div>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
}
