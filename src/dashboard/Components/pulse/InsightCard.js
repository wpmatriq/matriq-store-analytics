/**
 * InsightCard — generic card with icon header + content slot + empty state.
 *
 * Used for "What changed", "Suggested action", error fallbacks, etc.
 * Children render in the body; absent children reveal the empty-state block.
 */
import React from 'react';
import { motion } from 'framer-motion';
import classnames from '@Utils/classnames';

const ACCENT_STYLES = {
	neutral: 'bg-secondary text-muted-foreground ring-border',
	success: 'bg-success/10 text-success ring-success/20',
	warning: 'bg-warning/15 text-warning-foreground ring-warning/30',
};

const EASE = [ 0.16, 1, 0.3, 1 ];

export function InsightCard( {
	icon,
	title,
	emptyTitle,
	emptyDescription,
	children,
	delay = 0,
	accent = 'neutral',
	className,
} ) {
	const accentClasses = ACCENT_STYLES[ accent ] || ACCENT_STYLES.neutral;

	return (
		<motion.section
			initial={ { opacity: 0, y: 14 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.5, delay, ease: EASE } }
			className={ classnames(
				'rounded-2xl border border-solid border-border bg-card shadow-sm',
				className
			) }
		>
			<header className="flex items-center gap-2.5 border-b border-solid border-border/70 px-6 py-4">
				<div
					className={ classnames(
						'flex h-7 w-7 items-center justify-center rounded-lg ring-1',
						accentClasses
					) }
				>
					{ icon }
				</div>
				<h3 className="m-0 text-sm font-semibold text-foreground">{ title }</h3>
			</header>

			<div className="p-6">
				{ children ? (
					children
				) : (
					<div className="flex flex-col items-center justify-center py-10 text-center">
						<div
							className={ classnames(
								'flex h-14 w-14 items-center justify-center rounded-2xl ring-1',
								accentClasses
							) }
						>
							{ icon }
						</div>
						{ emptyTitle && (
							<p className="mt-4 text-base font-medium text-foreground">
								{ emptyTitle }
							</p>
						) }
						{ emptyDescription && (
							<p className="mt-1 max-w-xs text-sm text-muted-foreground">
								{ emptyDescription }
							</p>
						) }
					</div>
				) }
			</div>
		</motion.section>
	);
}
