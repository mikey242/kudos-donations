const defaultConfig = require('@wordpress/scripts/config/webpack.config');

module.exports = {
	...defaultConfig,
	optimization: {
		...defaultConfig.optimization,
		concatenateModules: false,
		moduleIds: 'deterministic',
	},
};
