module.exports = {
	content: [ './src/dashboard/**/*.{js,jsx,ts,tsx}' ],
	theme: {
		extend: {
			colors: {
				wpprimary: 'var(--wp-admin-theme-color)',
				wpcolor: '#2271b1',
				wphovercolor: '#135e96',
				wphoverbgcolor: '#2271b117',
				wpcolorfaded: '#2271b120',
				portal_primary_color: 'var(--portal-secondary-color)',
				portal_primary_text_color: 'var(--portal-primary-text-color)',
				required_icon_color: '#EF4444',
				portal: {
					DEFAULT: '#0084c6',
					hover: '#045CB4',
				},

				'brand-background-50': '#eef0ff', // Very light background
				'brand-background-hover-100': '#e0e7ff', // Light hover background
				'brand-200': '#c7d2fe', // Light border/background
				'brand-border-300': '#a5b4fc', // Soft border color
				'brand-400': '#818cf8', // Lighter shade for hover text/button
				'brand-500': '#6366f1', // Slightly lighter than your brand
				'brand-primary-600': '#4f46e5', // Close to your main color, bold primary
				'brand-hover-700': '#4338ca', // **Your Brand Color**
				'brand-800': '#3730a3', // Darker for active/focus
				'brand-900': '#312e81', // Very dark hover or text
				'brand-text-950': '#1e1b4b', // Super dark for text on light bg

				// background
				'background-primary': '#FFFFFF',
				'background-secondary': '#F9FAFB',
				'background-inverse': '#111827',
				'background-brand': '#4f46e5',
				'background-brand-light': '#A5B4FC',
				'background-important': '#DC2626',

				//Buttons
				'button-primary': '#4f46e5',
				'button-primary-hover': '#3323e3',
				'button-secondary': '#1F2937',
				'button-secondary-hover': '#374151',

				// text
				'text-primary': '#111827',
				'text-secondary': '#4B5563',
				'text-tertiary': '#9CA3AF',
				'text-on-color': '#FFFFFF',
				'text-error': '#DC2626',
				'text-error-inverse': '#F87171',
				'text-inverse': '#FFFFFF',
				'text-disabled': '#D1D5DB',
				'text-on-button-disabled': '#9CA3AF',

				// tab
				'tab-background': '#F3F4F6',
				'tab-border': '#E5E7EB',

				// icon
				'icon-primary': '#111827',
				'icon-secondary': '#4B5563',
				'icon-on-color': '#FFFFFF',
				'icon-inverse': '#FFFFFF',
				'icon-interactive': '#4f46e5',
				'icon-on-color-disabled': '#9CA3AF',
				'icon-disabled': '#D1D5DB',

				// focus
				focus: '#4f46e5',
				'focus-inset': '#FFFFFF',
				'focus-inverse': '#38BDF8',
				'focus-inverse-inset': '#111827',
				'focus-error': '#DC2626',
				'focus-border': '#c7d2fe',
				'focus-error-border': '#FECACA',

				// border
				'border-interactive': '#4f46e5',
				'border-subtle': '#E5E7EB',
				'border-strong': '#6B7280',
				'border-inverse': '#374151',
				'border-disabled': '#E5E7EB',
				'border-muted': '#E5E7EB',
				'border-error': '#DC2626',
				'border-transparent-subtle': '#37415114',
				'border-white': '#FFFFFF',

				// link
				'link-primary': '#4f46e5',
				'link-primary-hover': '#4338ca',
				'link-inverse': '#38BDF8',
				'link-visited': '#7C3AED',
				'link-visited-inverse': '#A78BFA',
				'link-inverse-hover': '#7DD3FC',

				// toggle
				'toggle-off': '#E5E7EB',
				'toggle-on': '#4f46e5',
				'toggle-dial-background': '#FFFFFF',
				'toggle-off-hover': '#D1D5DB',
				'toggle-off-border': '#D1D5DB',
				'toggle-on-hover': '#6366f1',
				'toggle-on-border': '#818cf8',
				'toggle-off-disabled': '#F3F4F6',
			},
			fontFamily: {
				inter: [ '"Inter"', 'sans-serif' ],
			},
			screens: {
				tablet: { max: '782px' },
				// => @media (max-width: 782px) { ... }
				mobile: { max: '600px' },
				// => @media (max-width: 600px) { ... }
			},
		},
	},
	variants: {
		extend: {},
	},
	plugins: [],
	corePlugins: {
		preflight: false,
	},
};
