const plugin = require('tailwindcss/plugin');

module.exports = {
	presets: [
		require('./tailwind.kudos.preset.js')
	],
	content: [
		'./src/components/*.{js,jsx}',
		'./src/components/public/**/*.{js,jsx}',
		'./src/components/controls/**/*.{js,jsx}',
	],
	theme: {
		fontFamily: {
			sans: 'var(--kudos-font-sans)',
			serif: 'var(--kudos-font-serif)'
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
