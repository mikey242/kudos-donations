const path = require('path')
const MiniCssExtractPlugin = require("mini-css-extract-plugin")

module.exports = {
    entry: {
        'kudos-admin' : [path.join(__dirname, '/src', '/js', '/kudos-admin.js'), path.join(__dirname, '/src', '/scss', '/kudos-admin.scss'),],
        'kudos-public' : [path.join(__dirname, '/src', '/js', '/kudos-public.js'), path.join(__dirname, '/src', '/scss', '/kudos-public.scss'),],
        'kudos-blocks' : [path.join(__dirname, '/src', '/js', '/kudos-blocks.js'),path.join(__dirname, '/src', '/scss', '/kudos-blocks.scss')],
        // 'css/kudos-public.css' : path.join(__dirname, '/src', '/scss', '/kudos-public.scss'),
        // 'js/kudos-public.js' : path.join(__dirname, '/src', '/js', '/kudos-public.js'),
    },
    watch: true,
    output: {
        path: path.resolve(__dirname, 'dist'),
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
                            plugins: () => [require('autoprefixer')]
                        }
                    }, 'sass-loader'
                ]
            }
        ]
    },
    resolve: {
        extensions: ['.json', '.js', '.jsx']
    },
    plugins: [
        new MiniCssExtractPlugin({
            filename: 'css/[name].css'
        })
    ]
};
