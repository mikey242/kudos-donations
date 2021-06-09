const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const WebpackAssetsManifest = require('webpack-assets-manifest')
const CopyPlugin = require("copy-webpack-plugin")
const {join, resolve} = require('path')

const PATHS = {
    src: join(__dirname, 'src'),
    dist: resolve(__dirname, 'dist'),
}

const vendors = {
    'jquery.validate.min.js': 'jquery-validation/dist',
    'micromodal.min.js': 'micromodal/dist',
}

const vendorCopies = mapVendorCopies('dist')

module.exports = {
    entry: {
        'kudos-admin-transactions': [
            join(PATHS.src, '/js', '/kudos-admin-transactions.js'),
        ],
        'kudos-admin-table': [
            join(PATHS.src, '/js', '/kudos-admin-table.js'),
        ],
        'kudos-admin-settings': [
            join(PATHS.src, '/js', '/kudos-admin-settings.jsx'),
            join(PATHS.src, '/css', '/kudos-admin-settings.css'),
        ],
        'kudos-button-block': [
            join(PATHS.src, '/js', '/kudos-button-block.jsx'),
        ],
        'kudos-public': [
            join(PATHS.src, '/js', '/kudos-public.js'),
            join(PATHS.src, '/css', '/kudos-public.css'),
        ],
    },
    externals: {
        jquery: 'jQuery',
        react: 'React',
        lodash: 'lodash',
        jqueryValidation: 'jquery-validation',
        micromodal: 'MicroModal',
    },
    output: {
        path: PATHS.dist,
        publicPath: '/wp-content/plugins/kudos-donations/dist/',
        filename: 'js/[name].js',
        clean: true
    },
    module: {
        rules: [
            {
                test: /.jsx?$/,
                exclude: [resolve(__dirname, 'node_modules')],
                loader: 'babel-loader',
                options: {
                    babelrc: false,
                    plugins: ['lodash'],
                    presets: ['@wordpress/default'],
                },
            },
            {
                test: /.s?css$/,
                use: [
                    MiniCssExtractPlugin.loader, 'css-loader', 'postcss-loader'
                ],
            },
            {
                test: /\.(png|jpe?g|gif|svg)$/i,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            name: '[name].[ext]',
                            outputPath: './img/',
                        },
                    },
                ],
            },
            {
                test: /\.(ttf|otf|eot|woff2?)$/,
                loader: 'url-loader',
                options: {
                    limit: 4096,
                    name: '[name].[ext]',
                    outputPath: './fonts/',
                },
            },
        ],
    },
    resolve: {
        extensions: ['.json', '.js', '.jsx'],
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css',
        }),
        new CopyPlugin({
            patterns: [...vendorCopies]
        }),
        new WebpackAssetsManifest(),
    ],
}

/**
 * Maps vendors to copy commands for the CopyWebpackPlugin.
 *
 * @source https://github.com/WordPress/wordpress-develop/blob/master/tools/webpack/packages.js
 * @param {string} buildTarget The folder in which to build the packages.
 * @return {Object[]} Copy object suitable for the CopyWebpackPlugin.
 */
function mapVendorCopies(buildTarget) {
    return Object.keys(vendors).map((filename) => ({
        from: join(
            __dirname,
            `node_modules/${vendors[filename]}/${filename}`
        ),
        to: join(__dirname, `${buildTarget}/js/vendor/${filename}`),
    }))
}
