const plugin = require('tailwindcss/plugin');

module.exports = {
	content: ['./src/front/**/*.{js,jsx}'], // Preserves content paths for front-end
	theme: {
		fontFamily: {
			body: ['var(--kudos-font-body)', 'montserratregular', 'sans-serif'], // Merged font settings
			heading: ['var(--kudos-font-heading)', 'cabinbold', 'serif'],
			mono: [
				'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
			],
		},
		zIndex: {
			'-1': -1,
			1: 1,
			1050: 1050, // Merged zIndex settings
		},
		extend: {
			keyframes: {
				loaderSpin: {
					'0%': {
						transform: 'rotate(0)',
						'animation-timing-function':
							'cubic-bezier(0.55, 0.055, 0.675, 0.19)',
					},
					'50%': {
						transform: 'rotate(900deg)',
						'animation-timing-function':
							'cubic-bezier(0.215, 0.61, 0.355, 1)',
					},
					'100%': { transform: 'rotate(1800deg)' },
				},
			},
			animation: {
				'loader-spin': 'loaderSpin 2s infinite', // Preserves loader spin animation
			},
			screens: {
				adminXs: '600px',
				adminSm: '783px',
				xs: '475px', // Preserves custom screen breakpoints
			},
			colors: {
				orange: {
					200: '#ffd59c',
					500: '#ff9f1c',
					700: '#f58d00',
				},
				primary: 'var(--kudos-theme-primary)',
				'primary-dark': 'var(--kudos-theme-primary-dark)',
				'primary-darker': 'var(--kudos-theme-primary-darker)',
				secondary: 'var(--kudos-theme-secondary)',
			},
		},
	},
	corePlugins: {
		preflight: true, // Keeps core plugin settings
	},
	plugins: [
		require('@tailwindcss/forms'),
		plugin(({ addUtilities }) => {
			addUtilities({
				'.rotate-x-180': {
					transform: 'rotateX(180deg)', // Adds custom utility for rotation
				},
			});
		}),
	],
};
