module.exports = {
	presets: [require('./tailwind.kudos.preset.js')],
	content: [
		'./src/components/common/**/*.{js,jsx}',
		'./src/components/admin/*.{js,jsx}',
		'./src/components/admin/campaigns/*.{js,jsx}',
	],
};
