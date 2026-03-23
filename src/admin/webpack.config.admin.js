const { merge } = require('webpack-merge');
const commonConfig = require('../webpack.config.base');
// eslint-disable-next-line import/no-extraneous-dependencies
const CopyWebpackPlugin = require('copy-webpack-plugin');
const { resolve } = require('node:path');

module.exports = merge(commonConfig, {
	entry: {
		'kudos-admin': resolve(__dirname, 'kudos-admin.tsx'),
	},
	plugins: [
		new CopyWebpackPlugin({
			patterns: [
				{
					from: resolve(__dirname, '../images'),
					to: 'images',
				},
			],
		}),
	],
	module: {
		rules: [
			{
				test: /\.css$/,
				use: [
					{
						loader: 'postcss-loader',
						options: {
							postcssOptions: {
								plugins: [
									require('autoprefixer'), // Only Autoprefixer for admin
								],
							},
						},
					},
				],
			},
		],
	},
});
