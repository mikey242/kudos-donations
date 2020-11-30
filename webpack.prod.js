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
                    // mangle: false,
                    mangle: {
                        reserved: [ '__', '_n', '_x', '_nx' ],
                    },
                    output: {
                        comments: /translators:/i,
                    },
                },
                extractComments: false,
            } ),
        ],
    },
});
