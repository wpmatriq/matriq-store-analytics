import { Card, CardContent, CardHeader, CardTitle } from "@Components/ui/card";
import { MetricCard } from "@Components/MetricCard";
import { Package, DollarSign, TrendingUp, Layers } from "lucide-react";
import { ResponsiveContainer, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip } from "recharts";

const variableProductData = [
  { name: "T-Shirt (Multiple Colors)", variants: 8, totalSales: 2400, revenue: 120000 },
  { name: "Smartphone Case (Multiple Models)", variants: 15, totalSales: 1800, revenue: 54000 },
  { name: "Shoes (Multiple Sizes)", variants: 12, totalSales: 950, revenue: 142500 },
  { name: "Laptop Bag (Multiple Sizes)", variants: 6, totalSales: 780, revenue: 85800 },
];

const topVariants = [
  { product: "T-Shirt", variant: "Black - Large", sales: 320, revenue: 16000 },
  { product: "T-Shirt", variant: "White - Medium", sales: 280, revenue: 14000 },
  { product: "Smartphone Case", variant: "iPhone 14 - Clear", sales: 240, revenue: 7200 },
  { product: "Shoes", variant: "Nike Air - Size 10", sales: 180, revenue: 27000 },
  { product: "Laptop Bag", variant: "15 inch - Black", sales: 150, revenue: 16500 },
];

export default function VariableProductsReports() {
  return (
    <div className="space-y-6">
      <div>
        <h1 className="text-3xl font-bold m-0 p-0">Variable Products Report</h1>
        <p className="text-base text-muted-foreground m-0 p-0">Analysis of products with multiple variants</p>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <MetricCard
          title="Variable Products"
          value="127"
          change="+8.3% from last month"
          changeType="increase"
          icon={<Layers className="h-6 w-6 text-primary" />}
        />
        <MetricCard
          title="Total Variants"
          value="1,845"
          change="+12.5% from last month"
          changeType="increase"
          icon={<Package className="h-6 w-6 text-chart-secondary" />}
        />
        <MetricCard
          title="Revenue"
          value="$402,300"
          change="+15.7% from last month"
          changeType="increase"
          icon={<DollarSign className="h-6 w-6 text-chart-tertiary" />}
        />
        <MetricCard
          title="Avg. Variants per Product"
          value="14.5"
          change="+2.1% from last month"
          changeType="increase"
          icon={<TrendingUp className="h-6 w-6 text-chart-quaternary" />}
        />
      </div>

      {/* Variable Products Performance */}
      <Card>
        <CardHeader>
          <CardTitle>Top Variable Products Performance</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <ResponsiveContainer width="100%" height={300}>
            <BarChart data={variableProductData}>
              <CartesianGrid strokeDasharray="3 3" className="opacity-30" />
              <XAxis dataKey="name" angle={-45} textAnchor="end" height={100} />
              <YAxis />
              <Tooltip
                formatter={(value, name) => [
                  name === 'revenue' ? `$${value.toLocaleString()}` : value,
                  name === 'revenue' ? 'Revenue' : name === 'totalSales' ? 'Total Sales' : 'Variants'
                ]}
              />
              <Bar dataKey="revenue" fill="hsl(var(--chart-primary))" name="revenue" />
            </BarChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Top Performing Variants */}
        <Card>
          <CardHeader>
            <CardTitle>Top Performing Variants</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <div className="space-y-4">
              {topVariants.map((variant, index) => (
                <div key={index} className="flex items-center justify-between p-3 rounded-lg bg-muted/20">
                  <div>
                    <p className="font-medium">{variant.product}</p>
                    <p className="text-sm text-muted-foreground">{variant.variant}</p>
                  </div>
                  <div className="text-right">
                    <p className="font-semibold">${variant.revenue.toLocaleString()}</p>
                    <p className="text-sm text-muted-foreground">{variant.sales} sales</p>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>

        {/* Variant Distribution */}
        <Card>
          <CardHeader>
            <CardTitle>Product Variant Overview</CardTitle>
          </CardHeader>
          <CardContent className="p-6">
            <div className="space-y-4">
              {variableProductData.map((product, index) => (
                <div key={index} className="space-y-2">
                  <div className="flex justify-between items-center">
                    <span className="font-medium text-sm">{product.name}</span>
                    <span className="text-sm text-muted-foreground">{product.variants} variants</span>
                  </div>
                  <div className="grid grid-cols-3 gap-4 text-center">
                    <div className="p-2 bg-muted/20 rounded">
                      <p className="text-lg font-bold">{product.totalSales}</p>
                      <p className="text-xs text-muted-foreground">Sales</p>
                    </div>
                    <div className="p-2 bg-muted/20 rounded">
                      <p className="text-lg font-bold">${product.revenue.toLocaleString()}</p>
                      <p className="text-xs text-muted-foreground">Revenue</p>
                    </div>
                    <div className="p-2 bg-muted/20 rounded">
                      <p className="text-lg font-bold">${Math.round(product.revenue / product.totalSales)}</p>
                      <p className="text-xs text-muted-foreground">Avg Price</p>
                    </div>
                  </div>
                </div>
              ))}
            </div>
          </CardContent>
        </Card>
      </div>

      {/* Detailed Variant Analysis */}
      <Card>
        <CardHeader>
          <CardTitle>Variant Performance Analysis</CardTitle>
        </CardHeader>
        <CardContent className="p-6">
          <div className="overflow-x-auto">
            <table className="w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left p-4 font-medium">Product</th>
                  <th className="text-left p-4 font-medium">Total Variants</th>
                  <th className="text-right p-4 font-medium">Total Sales</th>
                  <th className="text-right p-4 font-medium">Revenue</th>
                  <th className="text-right p-4 font-medium">Avg. per Variant</th>
                </tr>
              </thead>
              <tbody>
                {variableProductData.map((product, index) => (
                  <tr key={index} className="border-b hover:bg-muted/20">
                    <td className="p-4 font-medium">{product.name}</td>
                    <td className="p-4">
                      <span className="px-2 py-1 bg-primary/10 text-primary rounded-md text-sm">
                        {product.variants} variants
                      </span>
                    </td>
                    <td className="p-4 text-right font-medium">{product.totalSales}</td>
                    <td className="p-4 text-right font-medium">${product.revenue.toLocaleString()}</td>
                    <td className="p-4 text-right font-medium text-success">
                      ${Math.round(product.totalSales / product.variants)} units/variant
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
