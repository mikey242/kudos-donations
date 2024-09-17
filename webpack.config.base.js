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
					format: {
						comments: /(__|_e|_n|_x)\(/,
					},
				},
				extractComments: false,
			}),
		],
		moduleIds: 'deterministic',
	},
};
