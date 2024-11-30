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
						comments(node, comment) {
							const text = comment.value;
							return /(translators:)/i.test(text);
						},
					},
				},
				extractComments: false,
			}),
		],
		moduleIds: 'deterministic',
	},
};
