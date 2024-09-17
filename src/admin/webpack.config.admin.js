const { merge } = require('webpack-merge');
const commonConfig = require('../../webpack.config.base');

module.exports = merge(commonConfig, {
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
