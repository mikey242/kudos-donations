const path = require('path')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const WebpackAssetsManifest = require('webpack-assets-manifest');
const svgToMiniDataURI = require('mini-svg-data-uri');

const PATHS = {
    src: path.join(__dirname, 'src'),
    dist: path.resolve(__dirname, 'dist')
}

module.exports = {
    entry: {
        'kudos-admin' : [path.join(PATHS.src, '/js', '/kudos-admin.js'), path.join(PATHS.src, '/scss', '/kudos-admin.scss'),],
        'kudos-public' : [path.join(PATHS.src, '/js', '/kudos-public.js'), path.join(PATHS.src, '/scss', '/kudos-public.scss'),],
        'kudos-blocks' : [path.join(PATHS.src, '/js', '/kudos-blocks.js'),path.join(PATHS.src, '/scss', '/kudos-blocks.scss')],
    },
    output: {
        path: PATHS.dist,
        publicPath: '/wp-content/plugins/kudos-donations/dist/',
        filename: 'js/[name].[contenthash].js',
    },
    optimization: {
        moduleIds: 'hashed',
        // runtimeChunk: 'single',
        splitChunks: {
            cacheGroups: {
                vendor: {
                    test: /[\\/]node_modules[\\/]/,
                    name: 'vendors',
                    chunks: 'all',
                },
            },
        },
    },
    module: {
        rules: [{
            test: /.jsx?$/,
            include: [
                path.resolve(__dirname, 'src')
            ],
            exclude: [
                path.resolve(__dirname, 'node_modules')
            ],
            loader: 'babel-loader',
            query: {
                presets: [
                    ["@babel/preset-env"]
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
                test: /\.(ttf|otf|eot|woff2?)$/,
                loader: 'url-loader',
                options: {
                    limit: 4096,
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
        new CleanWebpackPlugin()
    ]
};