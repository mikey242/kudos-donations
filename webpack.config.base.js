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
							const type = comment.type;
							if (type === 'comment2') {
								// multiline comment
								return /translators:/i.test(text);
							}
							return false;
						},
					},
				},
				extractComments: false,
			}),
		],
		moduleIds: 'deterministic',
	},
};
