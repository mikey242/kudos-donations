const { merge } = require('webpack-merge');
const commonConfig = require('../webpack.config.base');
const convertRemToEm = {
	postcssPlugin: 'convertRemToEm',
	// When debugging this, https://astexplorer.net/#/2uBU1BLuJ1 is very helpful
	Declaration(declaration) {
		declaration.value = declaration.value.replaceAll(remRegex, 'em');
	},
};

// Regex to find all occurrences of "rem" units
const remRegex = /(?<=\d)rem/g;

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
									convertRemToEm,
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