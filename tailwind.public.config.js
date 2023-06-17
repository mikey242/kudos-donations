const plugin = require('tailwindcss/plugin');

module.exports = {
	important: false,
	content: [
		'./src/components/*.{js,jsx}',
		'./src/components/public/**/*.{js,jsx}',
		'./src/components/controls/**/*.{js,jsx}',
	],
	theme: {
		fontFamily: {
			sans: 'var(--kudos-font-sans)',
			serif: 'var(--kudos-font-serif)',
			mono: [
				'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
			],
		},
		extend: {
			screens: {
				xs: '475px',
			},
			zIndex: {
				'-1': -1,
				1: 1,
				1050: 1050,
			},
			colors: {
				orange: {
					200: '#ffd59c',
					500: '#ff9f1c',
					700: '#f58d00',
				},
				green: {
					500: '#2ec4b6',
					700: '#2bb9ac',
				},
				'primary': 'var(--kudos-theme-primary)',
				'primary-dark': 'var(--kudos-theme-primary-dark)',
				'primary-darker': 'var(--kudos-theme-primary-darker)',
				'secondary': 'var(--kudos-theme-secondary)',
			},
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
				'loader-spin': 'loaderSpin 2s infinite',
			},
		},
	},
	plugins: [
		require('@tailwindcss/forms'),
		plugin(({ addUtilities }) => {
			addUtilities({
				'.rotate-x-180': {
					transform: 'rotateX(180deg)',
				},
			});
		}),
	],
};
