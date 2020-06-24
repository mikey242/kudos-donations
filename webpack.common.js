const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const svgToMiniDataURI = require('mini-svg-data-uri');
const TerserPlugin = require('terser-webpack-plugin');
const CopyWebpackPlugin = require( 'copy-webpack-plugin' );
const { join, resolve } = require( 'path' );

const env = process.env.NODE_ENV;

const PATHS = {
    src: join(__dirname, 'src'),
    dist: resolve(__dirname, 'dist')
}

const vendors = {
    'jquery.validate.min.js': 'jquery-validation/dist/jquery.validate.min.js',
    // 'micromodal.min.js': 'micromodal/dist/micromodal.min.js'
};

/**
 * Maps vendors to copy commands for the CopyWebpackPlugin.
 *
 * @source https://github.com/WordPress/wordpress-develop/blob/master/tools/webpack/packages.js
 * @param {Object} vendors     Vendors to include in the vendor folder.
 * @param {string} buildTarget The folder in which to build the packages.
 * @return {Object[]} Copy object suitable for the CopyWebpackPlugin.
 */
function mapVendorCopies( vendors, buildTarget ) {
    return Object.keys( vendors ).map( ( filename ) => ( {
        from: join( __dirname, `node_modules/${ vendors[ filename ] }` ),
        to: join( __dirname, `${ buildTarget }/js/vendor/${ filename }` ),
    } ) );
}

const vendorCopies = mapVendorCopies(vendors, 'dist');

module.exports = {
    entry: {
        'kudos-admin-transactions' : [join(PATHS.src, '/js', '/kudos-admin-transactions.js')],
        'kudos-admin-subscriptions' : [join(PATHS.src, '/js', '/kudos-admin-subscriptions.js')],
        'kudos-admin-settings' : [join(PATHS.src, '/js', '/kudos-admin-settings.jsx'), join(PATHS.src, '/scss', '/kudos-admin-settings.scss')],
        'kudos-button-block' : [join(PATHS.src, '/js', '/kudos-button-block.jsx'),join(PATHS.src, '/scss', '/kudos-button-block.scss')],
        'kudos-public' : [join(PATHS.src, '/js', '/kudos-public.js'), join(PATHS.src, '/scss', '/kudos-public.scss')],
    },
    output: {
        path: PATHS.dist,
        publicPath: '/wp-content/plugins/kudos-donations/dist/',
        filename: 'js/[name].[contenthash].js',
    },
    externals: {
        jquery: 'jQuery',
        'lodash': 'lodash',
        // micromodal: 'MicroModal',
    },
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin({
            extractComments: false,
        })],
    },
    module: {
        rules: [{
            test: /.jsx?$/,
            include: [
                resolve(__dirname, 'src')
            ],
            exclude: [
                resolve(__dirname, 'node_modules')
            ],
            loader: 'babel-loader',
            options: {
                plugins: ['lodash'],
                presets: [
                    ["@wordpress/default"],
                    ['@babel/env', { 'targets': { 'node': 6 } }]
                ]
            },
            },
            {
                test: /\.(sa|sc|c)ss$/,
                use: [
                    MiniCssExtractPlugin.loader,
                    'css-loader',
                    {
                        loader: 'postcss-loader',
                    }, 'sass-loader'
                ]
            },
            {
                test: /\.svg$/i,
                loader: 'url-loader',
                options: {
                    generator: (content) => svgToMiniDataURI(content.toString()),
                },
            },
            {
                test: /\.(png|jpe?g|gif)$/i,
                use :[{
                    loader: 'file-loader',
                    options: {
                        name: '[name].[contenthash].[ext]',
                        outputPath:'./img/'
                    },
                }]
            },
            {
                test: /\.(ttf|otf|eot|woff2?)$/,
                loader: 'url-loader',
                options: {
                    limit: 4096,
                    name: "[name].[ext]",
                    outputPath:'./fonts/'
                },
            },
        ]
    },
    resolve: {
        extensions: ['.json', '.js', '.jsx']
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].[contenthash].css'
        }),
        new WebpackAssetsManifest(),
        new CleanWebpackPlugin(),
        new CopyWebpackPlugin(
            [
                ...vendorCopies,
            ],
        ),
    ]
};