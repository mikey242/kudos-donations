const path = require('path')
const glob = require('glob-all')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
// const PurgecssPlugin = require('purgecss-webpack-plugin')
const svgToMiniDataURI = require('mini-svg-data-uri');

const PATHS = {
    src: path.join(__dirname, 'src')
}

module.exports = {
    entry: {
        'kudos-admin' : [path.join(PATHS.src, '/js', '/kudos-admin.js'), path.join(PATHS.src, '/scss', '/kudos-admin.scss'),],
        'kudos-public' : [path.join(PATHS.src, '/js', '/kudos-public.js'), path.join(PATHS.src, '/scss', '/kudos-public.scss'),],
        'kudos-blocks' : [path.join(PATHS.src, '/js', '/kudos-blocks.js'),path.join(PATHS.src, '/scss', '/kudos-blocks.scss')],
    },
    output: {
        path: path.resolve(__dirname, 'dist'),
        publicPath: '/wp-content/plugins/kudos-mollie/dist/',
        filename: 'js/[name].js',
        chunkFilename: 'js/[id].js'
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
                        options: {
                            // config: { path: __dirname, ctx: config },
                        }
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
            filename: 'css/[name].css'
        }),
        // new PurgecssPlugin({
        //     // paths: glob.sync(`${PATHS.src}/**/*`,  { nodir: true }),
        //     paths: glob.sync([
        //         'src/js/*.js',
        //         'public/includes/kudos-button.php'
        //     ]),
        //     // whitelist: [ 'modal', 'btn', 'btn-primary', 'modal-content', 'kudos-loading', 'modal-footer' ],
        //     // whiteListPatterns: [/^kudos-/]
        // })
    ]
};