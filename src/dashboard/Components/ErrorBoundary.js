/**
 * ErrorBoundary - top-level fallback for uncaught render errors.
 *
 * Wraps the active page router so a single broken component doesn't white-screen
 * the whole dashboard. In development, logs the error to console so stack traces
 * are still available. Fallback UI matches the pulse design language.
 */
import React from 'react';
import { __ } from '@wordpress/i18n';
import { AlertTriangle, RefreshCcw } from 'lucide-react';
import { InsightCard } from '@Components/pulse/InsightCard';

export default class ErrorBoundary extends React.Component {
	constructor( props ) {
		super( props );
		this.state = { hasError: false, error: null };
	}

	static getDerivedStateFromError( error ) {
		return { hasError: true, error };
	}

	componentDidCatch( error, info ) {
		// eslint-disable-next-line no-console
		console.error( 'Matriq Store Analytics dashboard error:', error, info );
	}

	handleReload = () => {
		window.location.reload();
	};

	render() {
		if ( ! this.state.hasError ) {
			return this.props.children;
		}

		return (
			<InsightCard
				icon={ <AlertTriangle className="h-4 w-4" /> }
				title={ __( 'Something went wrong', 'matriq-store-analytics' ) }
				accent="warning"
			>
				<div className="flex flex-col items-start gap-4">
					<p className="m-0 text-sm text-muted-foreground">
						{ __(
							'The dashboard hit an unexpected error. Reload the page to try again.',
							'matriq-store-analytics'
						) }
					</p>
					{ this.state.error?.message && (
						<code className="block max-w-full overflow-x-auto rounded-lg bg-muted p-3 font-mono text-xs text-muted-foreground">
							{ this.state.error.message }
						</code>
					) }
					<button
						type="button"
						onClick={ this.handleReload }
						className="inline-flex cursor-pointer items-center gap-2 rounded-full border-0 bg-primary px-4 py-2 text-sm font-medium text-primary-foreground shadow-sm transition-all hover:shadow-md focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-pulse"
					>
						<RefreshCcw className="h-4 w-4" />
						{ __( 'Reload dashboard', 'matriq-store-analytics' ) }
					</button>
				</div>
			</InsightCard>
		);
	}
}
