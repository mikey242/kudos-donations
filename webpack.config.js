const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = {
	...defaultConfig,
	optimization: {
		concatenateModules: false,
		minimize: true,
		minimizer: [
			new TerserPlugin({
				terserOptions: {
					mangle: {
						reserved: ['__', '_n', '_nx', '_x'],
					},
				},
			}),
		],
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
