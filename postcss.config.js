/* eslint-disable */
const purgecss = require('@fullhuman/postcss-purgecss')
const cssnano = require('cssnano')

module.exports = {
    plugins: [
        require('autoprefixer'),
        cssnano({
            preset: 'default'
        }),
        purgecss({
            content: ['./src/js/*.js', './public/**/*.php'],
            whitelist: ['modal-content', 'ml-auto', 'style-1', 'style-2', 'btn', 'btn-primary', 'modal-footer']
            // defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
        })
    ]
}
