/**
 * Sales Pulse v2 — Top header with navigation.
 */
import { Activity } from 'lucide-react';

const NAV_ITEMS = [
	{ label: 'Overview', tab: 'overview', href: 'admin.php?page=sales-pulse' },
	{ label: 'History', tab: 'history', href: 'admin.php?page=sales-pulse&tab=history' },
	{ label: 'Campaigns', tab: 'campaigns', href: 'admin.php?page=sales-pulse&tab=campaigns' },
	{ label: 'Settings', tab: 'settings', href: 'admin.php?page=sales-pulse&tab=settings' },
];

export default function TopHeader( { activeTab = 'overview' } ) {
	return (
		<header className="bg-white border-b border-solid border-border">
			<div className="max-w-7xl mx-auto px-4">
				<div className="flex items-center justify-between h-14">
					{ /* Brand */ }
					<div className="flex items-center gap-6">
						<a
							href="admin.php?page=sales-pulse"
							className="flex items-center gap-2 text-foreground no-underline hover:no-underline"
						>
							<Activity className="h-5 w-5 text-accent" />
							<span className="font-semibold text-sm">Sales Pulse</span>
						</a>

						{ /* Nav */ }
						<nav className="flex items-center gap-1">
							{ NAV_ITEMS.map( ( item ) => {
								const isActive = activeTab === item.tab;
								return (
									<a
										key={ item.tab }
										href={ item.href }
										className={ `
											px-3 py-1.5 text-sm font-medium rounded-md no-underline transition-colors
											${ isActive
			? 'bg-accent/10 text-accent'
			: 'text-muted-foreground hover:text-foreground hover:bg-muted'
		}
										` }
									>
										{ item.label }
									</a>
								);
							} ) }
						</nav>
					</div>

					{ /* Version */ }
					<div className="flex items-center text-xs text-muted-foreground">
						<span>V-{ window.wc_sma_admin_data?.version || '' }</span>
					</div>
				</div>
			</div>
		</header>
	);
}
