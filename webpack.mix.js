let mix = require('laravel-mix')
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' )

mix

    // Laravel Mix config.
    .setPublicPath('dist')
    .setResourceRoot('/wp-content/plugins/kudos-donations/dist')
    .options({
        postCss: [
            require('postcss-nested')
        ],
        terser: {
            terserOptions: {
                toplevel: false,
                output: {
                    comments: /translators:/i,
                },
                compress: {
                    passes: 2,
                    conditionals: false, // Needed to prevent __() functions in ternary from being combined
                    drop_console: true,
                },
                mangle: {
                    reserved: ['__', '_n', '_nx', '_x'],
                },
            },
            extractComments: false,
        },
    })

    // Webpack config.
    .webpackConfig({
        plugins: [
            new DependencyExtractionWebpackPlugin()
        ]
    })

    // Copy vendor files.
    // .copy('node_modules/jquery-validation/dist/jquery.validate.min.js', 'dist/js')
    // .copy('node_modules/micromodal/dist/micromodal.min.js', 'dist/js')

    // Public assets.
    .js('src/js/kudos-public.js', 'js')
    .postCss('src/css/kudos-public.css', 'css', [
        require('tailwindcss')('./tailwind.config.js'),
        require("postcss-prefixwrap")('[id^=kudos\\-donations\\-]', {
            ignoredSelectors: [':root'],
        })
    ])

    // Block assets.
    .js('src/js/Blocks/Button/kudos-button.jsx', 'blocks/kudos-button').react()
    .copy('src/js/Blocks/Button/block.json', 'dist/blocks/kudos-button/')

    // Admin assets.
    .js('src/js/kudos-admin-settings.jsx', 'js').react()
    .js('src/js/kudos-admin-transactions.js', 'js')
    .js('src/js/kudos-admin-table.js', 'js')
    .postCss('src/css/kudos-admin-settings.css', 'css', [
        require('tailwindcss'),
    ])

    // BrowserSync.
    .browserSync('localhost')

    // Add version hash to filenames.
    .version()