const { merge } = require('webpack-merge');
const commonConfig = require('../webpack.config.base');
const { resolve } = require('node:path');

module.exports = merge(commonConfig, {
	entry: {
		'settings/kudos-admin-settings': resolve(
			__dirname,
			'kudos-admin-settings.tsx'
		),
		'campaigns/kudos-admin-campaigns': resolve(
			__dirname,
			'kudos-admin-campaigns.tsx'
		),
		'transactions/kudos-admin-transactions': resolve(
			__dirname,
			'kudos-admin-transactions.tsx'
		),
		'donors/kudos-admin-donors': resolve(
			__dirname,
			'kudos-admin-donors.tsx'
		),
		'migrations/kudos-admin-migrations': resolve(
			__dirname,
			'kudos-admin-migrations.ts'
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
