const { merge } = require('webpack-merge');
const commonConfig = require('../webpack.config.base');
const { resolve } = require('node:path');

module.exports = merge(commonConfig, {
	entry: {
		'settings/kudos-admin-settings': resolve(
			__dirname,
			'kudos-admin-settings.jsx'
		),
		'campaigns/kudos-admin-campaigns': resolve(
			__dirname,
			'kudos-admin-campaigns.jsx'
		),
		'migrations/kudos-admin-migrations': resolve(
			__dirname,
			'kudos-admin-migrations.js'
		),
	},
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
