/**
 * SettingSection — framed section for the Settings page.
 *
 * Header: small icon tile + title + description. Body: section content.
 * Animated entry with a small stagger, driven by `delay`.
 */
import React from 'react';
import { motion } from 'framer-motion';

const EASE = [ 0.16, 1, 0.3, 1 ];

export function SettingSection( { icon, title, description, delay = 0, children } ) {
	return (
		<motion.section
			initial={ { opacity: 0, y: 10 } }
			animate={ { opacity: 1, y: 0 } }
			transition={ { duration: 0.4, delay, ease: EASE } }
			className="overflow-hidden rounded-2xl border border-solid border-border bg-card shadow-sm"
		>
			<header className="flex items-center gap-3 border-b border-solid border-border/70 px-6 py-4">
				<span className="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary text-muted-foreground ring-1 ring-border">
					{ icon }
				</span>
				<div>
					<h3 className="m-0 text-sm font-semibold text-foreground">
						{ title }
					</h3>
					{ description && (
						<p className="m-0 mt-0.5 text-xs text-muted-foreground">
							{ description }
						</p>
					) }
				</div>
			</header>
			<div className="p-6">{ children }</div>
		</motion.section>
	);
}
