/**
 * Period Toggle component.
 *
 * Switches between Daily and Weekly views.
 */
import React from 'react';
import { ToggleGroup, ToggleGroupItem } from '@Components/ui/toggle-group';

export function PeriodToggle( { value = 'daily', onChange } ) {
	return (
		<ToggleGroup
			type="single"
			value={ value }
			onValueChange={ ( val ) => val && onChange( val ) }
		>
			<ToggleGroupItem value="daily" className="text-xs px-3 py-1.5">
				Yesterday
			</ToggleGroupItem>
			<ToggleGroupItem value="weekly" className="text-xs px-3 py-1.5">
				Last 7 Days
			</ToggleGroupItem>
		</ToggleGroup>
	);
}
