const { merge } = require('webpack-merge');
const commonConfig = require('../webpack.config.base');
const { resolve } = require('node:path');

module.exports = merge(commonConfig, {
	entry: {
		'kudos-admin': resolve(__dirname, 'kudos-admin.tsx'),
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
