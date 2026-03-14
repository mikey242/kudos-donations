const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	devServer: {
		...defaultConfig.devServer,
		proxy: [
			{
				pathFilter: '/build',
				pathRewrite: { '^/build': '' },
				target: 'http://localhost:8887',
			},
		],
	},
	optimization: {
		...defaultConfig.optimization,
		runtimeChunk: false,
	},
};
