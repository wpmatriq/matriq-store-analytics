/**
 * Sales Pulse v2 — Top header with navigation.
 */
import { __ } from '@wordpress/i18n';
import { Activity, BookOpen, Megaphone } from 'lucide-react';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@Components/ui/tooltip';

const NAV_ITEMS = [
	{ label: __( 'Overview', 'sales-pulse' ), tab: 'overview', href: 'admin.php?page=sales-pulse' },
	{ label: __( 'History', 'sales-pulse' ), tab: 'history', href: 'admin.php?page=sales-pulse&tab=history' },
	{ label: __( 'Campaigns', 'sales-pulse' ), tab: 'campaigns', href: 'admin.php?page=sales-pulse&tab=campaigns' },
	{ label: __( 'Settings', 'sales-pulse' ), tab: 'settings', href: 'admin.php?page=sales-pulse&tab=settings' },
];

export default function TopHeader( { activeTab = 'overview' } ) {
	return (
		<TooltipProvider delayDuration={ 300 }>
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
								<span className="font-semibold text-sm">{ __( 'Sales Pulse', 'sales-pulse' ) }</span>
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

						{ /* Version + Links */ }
						<div className="flex items-center gap-3 text-xs text-muted-foreground">
							<Tooltip>
								<TooltipTrigger asChild>
									<span className="cursor-default">V-{ window.wc_sma_admin_data?.version || '' }</span>
								</TooltipTrigger>
								<TooltipContent>{ __( 'Core', 'sales-pulse' ) }</TooltipContent>
							</Tooltip>
							<span className="h-4 border-r border-solid border-border"></span>
							<Tooltip>
								<TooltipTrigger asChild>
									<a
										href="https://salespulseglobe.in/docs"
										target="_blank"
										rel="noopener noreferrer"
										className="text-muted-foreground hover:text-foreground transition-colors no-underline flex"
									>
										<BookOpen className="h-4 w-4" />
									</a>
								</TooltipTrigger>
								<TooltipContent>{ __( 'Knowledge Base', 'sales-pulse' ) }</TooltipContent>
							</Tooltip>
							<span className="h-4 border-r border-solid border-border"></span>
							<Tooltip>
								<TooltipTrigger asChild>
									<a
										href="https://salespulseglobe.in/changelog"
										target="_blank"
										rel="noopener noreferrer"
										className="text-muted-foreground hover:text-foreground transition-colors no-underline flex"
									>
										<Megaphone className="h-4 w-4" />
									</a>
								</TooltipTrigger>
								<TooltipContent>{ __( "What's New", 'sales-pulse' ) }</TooltipContent>
							</Tooltip>
						</div>
					</div>
				</div>
			</header>
		</TooltipProvider>
	);
}
