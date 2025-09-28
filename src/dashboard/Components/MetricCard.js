import React from "react";
import { Card, CardContent } from "@Components/ui/card";
import classnames from "@Utils/classnames";

export function MetricCard({
  title,
  value,
  change,
  changeType = "neutral",
  icon,
  className
}) {
  const changeColor = {
    increase: "text-success",
    decrease: "text-destructive",
    neutral: "text-muted-foreground"
  }[changeType];

  return (
    <Card className={classnames("bg-metric-card shadow-[var(--shadow-card)] hover:shadow-[var(--shadow-metric)] transition-all duration-200 border border-solid", className)}>
      <CardContent className="p-6">
        <div className="flex items-center justify-between">
          <div className="space-y-2">
            <p className="text-sm font-medium text-muted-foreground">{title}</p>
            <p className="text-2xl font-bold">{value}</p>
            {change && (
              <p className={classnames("text-xs font-medium", changeColor)}>
                {change}
              </p>
            )}
          </div>
          {icon && (
            <div className="h-12 w-12 rounded-full bg-gradient-to-br from-primary/10 to-accent/10 flex items-center justify-center">
              {icon}
            </div>
          )}
        </div>
      </CardContent>
    </Card>
  );
}
