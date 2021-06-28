let mix = require('laravel-mix')

mix
    .setPublicPath('dist')
    .setResourceRoot('/wp-content/plugins/kudos-donations/dist')
    .options({
        autoprefixer: { remove: false },
        terser: {
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
        }
    })
    .webpackConfig({
    externals: {
        jquery: 'jQuery',
        react: 'React',
        lodash: 'lodash',
        jqueryValidation: 'jquery-validation',
        micromodal: 'MicroModal',
    }
})

// Vendor files
mix.copy('node_modules/jquery-validation/dist/jquery.validate.min.js', 'dist/js');
mix.copy('node_modules/micromodal/dist/micromodal.min.js', 'dist/js');

// Public
mix
    .js('src/js/kudos-public.js', 'js')
    .postCss('src/css/kudos-public.css', 'css', [
        require('tailwindcss')('./tailwind.config.js'),
        require('postcss-nested')
    ])

// Block
    .js('src/js/kudos-button-block.jsx', 'js').react()
    .postCss('src/css/kudos-button-block.css', 'css', [
        require('tailwindcss')('./tailwind-block.config.js'),
        require('postcss-nested')
    ])

// Admin
    .js('src/js/kudos-admin-settings.jsx', 'js').react()
    .js('src/js/kudos-admin-transactions.js', 'js')
    .js('src/js/kudos-admin-table.js', 'js')
    .postCss('src/css/kudos-admin-settings.css', 'css', [
        require('tailwindcss'),
        require('postcss-nested')
    ])

// Add version hash to filenames
    .version();