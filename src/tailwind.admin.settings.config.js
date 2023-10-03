module.exports = {
	presets: [require('./tailwind.kudos.preset.js')],
	content: [
		'./src/components/*.{js,jsx}',
		'./src/components/admin/*.{js,jsx}',
		'./src/components/controls/*.{js,jsx}',
		'./src/components/settings/*.{js,jsx}',
	],
};
