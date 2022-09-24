module.exports = {
	important: false,
	content: [
		'./src/admin/**/*.{js,jsx}',
		'./src/blocks/**/*.{js,jsx}',
		'./src/common/**/*.{js,jsx}',
		'./src/helpers/**/*.{js,jsx}',
	],
	theme: {
		fontFamily: {
			sans: ['montserratregular', 'sans-serif'],
			serif: ['cabinbold', 'serif'],
			mono: [
				'ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace',
			],
		},
		zIndex: {
			'-1': -1,
			1: 1,
			1050: 1050,
		},
		extend: {
			screens: {
				xs: '475px',
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
				primary: 'var(--kudos-theme-primary)',
				'primary-dark': 'var(--kudos-theme-primary-dark)',
				'primary-darker': 'var(--kudos-theme-primary-darker)',
				secondary: 'var(--kudos-theme-secondary)',
			},
		},
	},
	corePlugins: {
		preflight: true,
	},
	plugins: [require('@tailwindcss/forms')],
};
