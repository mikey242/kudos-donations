const { merge } = require('webpack-merge');
const commonConfig = require('../webpack.config.base');
// eslint-disable-next-line import/no-extraneous-dependencies
const CopyWebpackPlugin = require('copy-webpack-plugin');
const { resolve } = require('node:path');
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin');
// An ES module; Node 22.12+ can require() it.
const {
	default: dsTokenFallbacks,
} = require('@wordpress/theme-build-tools/postcss-plugins/postcss-ds-token-fallbacks');

const config = merge(commonConfig, {
	entry: {
		'kudos-admin': resolve(__dirname, 'kudos-admin.tsx'),
	},
	plugins: [
		new CopyWebpackPlugin({
			patterns: [
				{
					from: resolve(__dirname, '../images/logo-colour.png'),
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
									// Adds each var(--wpds-*) token's value as its fallback,
									// for WordPress versions that don't define the token.
									dsTokenFallbacks,
								],
							},
						},
					},
				],
			},
		],
	},
});

/*
 * Bundle @wordpress/theme rather than using WordPress's copy, so the admin works on
 * WordPress 6.9 and 7.0: 7.0's wp-theme script has no ThemeProvider, and 6.9 has no
 * wp-theme script at all. Every other @wordpress package still comes from WordPress.
 */
config.plugins = [
	...config.plugins.filter(
		(plugin) => !(plugin instanceof DependencyExtractionWebpackPlugin)
	),
	new DependencyExtractionWebpackPlugin({
		requestToExternal: (request) =>
			request === '@wordpress/theme' ? false : undefined,
	}),
];

module.exports = config;
