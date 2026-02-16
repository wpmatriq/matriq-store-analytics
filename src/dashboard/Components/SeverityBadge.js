/**
 * Severity Badge component.
 *
 * Visual indicator for diagnosis severity: success, warning, info.
 */
import React from 'react';
import { Badge } from '@Components/ui/badge';
import classnames from '@Utils/classnames';

const severityStyles = {
	success: 'bg-success/10 text-success border-success/20 hover:bg-success/20',
	warning: 'bg-warning/10 text-warning border-warning/20 hover:bg-warning/20',
	info: 'bg-muted text-muted-foreground border-muted hover:bg-muted/80',
};

const severityLabels = {
	success: 'Positive',
	warning: 'Needs Attention',
	info: 'Stable',
};

export function SeverityBadge( { severity = 'info', label, className } ) {
	return (
		<Badge
			variant="outline"
			className={ classnames(
				'text-xs font-medium border border-solid',
				severityStyles[ severity ] || severityStyles.info,
				className
			) }
		>
			{ label || severityLabels[ severity ] || severity }
		</Badge>
	);
}
