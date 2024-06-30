module.exports = {
	important: false,
	theme: {
		fontFamily: {
			body: ['montserratregular', 'sans-serif'],
			heading: ['cabinbold', 'serif'],
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
				adminXs: '600px',
				adminSm: '783px',
				xs: '475px',
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
		preflight: true,
	},
	plugins: [require('@tailwindcss/forms')],
};