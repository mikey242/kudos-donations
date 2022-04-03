const mix = require('laravel-mix')
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin')

mix

// Laravel Mix config.
  .setPublicPath('dist')
  .setResourceRoot('/wp-content/plugins/kudos-donations/dist')
  .options({
    terser: {
      terserOptions: {
        toplevel: false,
        output: {
          comments: /translators:/i
        },
        compress: {
          passes: 2,
          conditionals: false, // Needed to prevent __() functions in ternary from being combined
          drop_console: true
        },
        mangle: {
          reserved: ['__', '_n', '_nx', '_x']
        }
      },
      extractComments: false
    }
  })

// Webpack config.
  .webpackConfig({
    plugins: [
      new DependencyExtractionWebpackPlugin({})
    ]
  })

// Public assets.
  .js('src/public/kudos-public.js', 'public').react()
  .postCss('src/public/kudos-public.css', 'public', [
    require('tailwindcss')('./tailwind.public.config.js')
  ])
  .sourceMaps()

// Block assets.
  .js('src/blocks/kudos-button/index.jsx', 'blocks/kudos-button').react()

// Admin assets.
  .js('src/admin/kudos-admin-settings.jsx', 'admin').react()
  .js('src/admin/kudos-admin-campaigns.jsx', 'admin').react()
  .js('src/admin/kudos-admin-transactions.js', 'admin')
  .js('src/admin/kudos-admin-table.js', 'admin')
  .postCss('src/admin/kudos-admin-settings.css', 'admin', [
    require('tailwindcss')('./tailwind.admin.config.js')
  ])
  .sourceMaps()

// BrowserSync.
  .browserSync('kudos.test')

// Add version hash to filenames.
  .version()
