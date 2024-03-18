const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	optimization: {
		moduleIds: 'deterministic',
		splitChunks: {
			cacheGroups: {
				styles: {
					test: /kudos-fonts.css/,
					name: 'kudos-fonts',
					type: 'css/mini-extract',
					chunks: 'all',
					enforce: true,
				},
			},
		},
	},
};
