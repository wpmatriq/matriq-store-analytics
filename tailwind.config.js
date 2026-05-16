/**
 * Tailwind configuration - Matriq Store Analytics dashboard.
 *
 * Tokens are authored in SCSS (`src/dashboard/design-tokens.scss`) as OKLch
 * CSS variables. Tailwind theme values below reference `var(--*)` directly so
 * any change in the SCSS ripples through utility classes without editing here.
 *
 * `content` also scans Store Copilot's source so utility classes used only by
 * the Pro plugin's slot components (e.g. ChatTrigger, AnomalyBanner) end up
 * in the compiled CSS that loads on every Matriq Store Analytics admin page. Pro reuses
 * SP's stylesheet rather than shipping its own.
 */
const fs = require( 'fs' );
const path = require( 'path' );

const proSourcePath = path.resolve( __dirname, '../store-copilot/src' );
const content = [ './src/dashboard/**/*.{js,jsx,ts,tsx}' ];

if ( fs.existsSync( proSourcePath ) ) {
	content.push( '../store-copilot/src/**/*.{js,jsx,ts,tsx}' );
}

module.exports = {
	content,
	theme: {
		container: {
			center: true,
			padding: '2rem',
			screens: {
				'2xl': '1400px',
			},
		},
		extend: {
			colors: {
				border: 'var(--border)',
				input: 'var(--input)',
				ring: 'var(--ring)',
				background: 'var(--background)',
				foreground: 'var(--foreground)',
				canvas: 'var(--canvas)',
				surface: {
					DEFAULT: 'var(--surface)',
					elevated: 'var(--surface-elevated)',
				},
				ink: 'var(--ink)',
				pulse: {
					DEFAULT: 'var(--pulse)',
					foreground: 'var(--pulse-foreground)',
				},
				primary: {
					DEFAULT: 'var(--primary)',
					foreground: 'var(--primary-foreground)',
				},
				secondary: {
					DEFAULT: 'var(--secondary)',
					foreground: 'var(--secondary-foreground)',
				},
				destructive: {
					DEFAULT: 'var(--destructive)',
					foreground: 'var(--destructive-foreground)',
				},
				success: {
					DEFAULT: 'var(--success)',
					foreground: 'var(--success-foreground)',
				},
				warning: {
					DEFAULT: 'var(--warning)',
					foreground: 'var(--warning-foreground)',
				},
				muted: {
					DEFAULT: 'var(--muted)',
					foreground: 'var(--muted-foreground)',
				},
				accent: {
					DEFAULT: 'var(--accent)',
					foreground: 'var(--accent-foreground)',
				},
				popover: {
					DEFAULT: 'var(--popover)',
					foreground: 'var(--popover-foreground)',
				},
				card: {
					DEFAULT: 'var(--card)',
					foreground: 'var(--card-foreground)',
				},
				chart: {
					1: 'var(--chart-1)',
					2: 'var(--chart-2)',
					3: 'var(--chart-3)',
					4: 'var(--chart-4)',
					5: 'var(--chart-5)',
				},
			},
			fontFamily: {
				display: [ 'var(--font-display)' ],
				sans: [ 'var(--font-sans)' ],
				mono: [ 'var(--font-mono)' ],
			},
			spacing: {
				4.5: '1.125rem',
			},
			borderRadius: {
				sm: 'calc(var(--radius) - 4px)',
				md: 'calc(var(--radius) - 2px)',
				lg: 'var(--radius)',
				xl: 'calc(var(--radius) + 4px)',
				'2xl': 'calc(var(--radius) + 8px)',
				'3xl': 'calc(var(--radius) + 12px)',
			},
			boxShadow: {
				xs: 'var(--shadow-xs)',
				sm: 'var(--shadow-sm)',
				md: 'var(--shadow-md)',
				lg: 'var(--shadow-lg)',
				glow: 'var(--shadow-glow)',
				inset: 'var(--shadow-inset)',
			},
			backgroundImage: {
				'gradient-pulse': 'var(--gradient-pulse)',
				'gradient-ink': 'var(--gradient-ink)',
				'gradient-canvas': 'var(--gradient-canvas)',
				'gradient-card': 'var(--gradient-card)',
				'gradient-warning': 'var(--gradient-warning)',
				'gradient-danger': 'var(--gradient-danger)',
			},
			transitionTimingFunction: {
				'out-expo': 'cubic-bezier(0.16, 1, 0.3, 1)',
			},
			keyframes: {
				'accordion-down': {
					from: { height: '0' },
					to: { height: 'var(--radix-accordion-content-height)' },
				},
				'accordion-up': {
					from: { height: 'var(--radix-accordion-content-height)' },
					to: { height: '0' },
				},
				'fade-up': {
					from: { opacity: '0', transform: 'translateY(8px)' },
					to: { opacity: '1', transform: 'translateY(0)' },
				},
				'pulse-ring': {
					'0%': { boxShadow: '0 0 0 0 oklch(0.65 0.2 165 / 0.5)' },
					'70%': { boxShadow: '0 0 0 8px oklch(0.65 0.2 165 / 0)' },
					'100%': { boxShadow: '0 0 0 0 oklch(0.65 0.2 165 / 0)' },
				},
				shimmer: {
					'0%': { backgroundPosition: '-200% 0' },
					'100%': { backgroundPosition: '200% 0' },
				},
			},
			animation: {
				'accordion-down': 'accordion-down 0.2s ease-out',
				'accordion-up': 'accordion-up 0.2s ease-out',
				'fade-up': 'fade-up 0.4s cubic-bezier(0.16, 1, 0.3, 1)',
				'pulse-ring': 'pulse-ring 2s cubic-bezier(0.16, 1, 0.3, 1) infinite',
				shimmer: 'shimmer 3s linear infinite',
			},
		},
	},
	variants: {
		extend: {},
	},
	plugins: [ require( 'tailwindcss-animate' ) ],
	corePlugins: {
		preflight: false,
	},
};
