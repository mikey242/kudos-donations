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
									require('tailwindcss'), // Tailwind for front-end
									require('autoprefixer'),
								],
							},
						},
					},
				],
			},
		],
	},
	optimization: {
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
});
