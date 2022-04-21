const mix = require('laravel-mix')
const DependencyExtractionWebpackPlugin = require('@wordpress/dependency-extraction-webpack-plugin')
const I18nLoaderWebpackPlugin = require('@automattic/i18n-loader-webpack-plugin')
const I18nCheckWebpackPlugin = require('@automattic/i18n-check-webpack-plugin')

mix

// Laravel Mix config.
  .setPublicPath('dist')
  .setResourceRoot('/wp-content/plugins/kudos-donations/dist')
  .options({
    terser: {
      extractComments: false,
      terserOptions: {
        ecma: 5,
        toplevel: true,
        mangle: { reserved: ['__', '_n', '_nx', '_x'] },
        compress: {
          passes: 2,
          conditionals: false, // Set to 'false' to prevent __() functions in ternary from being combined.
          drop_console: true
        },
        format: {
          comments: /translators:/i
        }
      }
    }
  })

// Webpack config.
  .webpackConfig({
    plugins: [
      new DependencyExtractionWebpackPlugin({}),
      new I18nLoaderWebpackPlugin({
        textdomain: 'kudos-donations'
      }),
      new I18nCheckWebpackPlugin()
    ],
    optimization: {
      concatenateModules: false // Important for preserving i18n functions.
    }
  })

// Public assets.
  .js('src/public/kudos-public.jsx', 'public')
  .postCss('src/public/kudos-public.css', 'public', [
    require('tailwindcss')('./tailwind.public.config.js')
  ])
  .sourceMaps(false)

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
  .sourceMaps(false)

// BrowserSync.
  .browserSync('localhost:8080')

// Add version hash to filenames.
  .version()
