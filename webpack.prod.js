const merge = require('webpack-merge');
const TerserPlugin = require( 'terser-webpack-plugin' );
const common = require('./webpack.common.js');

module.exports = merge(common, {
    mode: 'production',
    optimization: {
        minimize: true,
        concatenateModules: false,
        minimizer: [
            new TerserPlugin( {
                terserOptions: {
                    mangle: {
                        reserved: [ '__', '_n', '_x', '_nx' ],
                    },
                    output: {
                        comments: /translators:/i,
                    },
                    compress: {
                        conditionals: false, // Needed to prevent __() functions in ternary from being combined
                        drop_console: true,
                    },
                },
                extractComments: false,
            } ),
        ],
    },
});
