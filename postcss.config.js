/* eslint-disable */
const purgecss = require('@fullhuman/postcss-purgecss');
const tailwindcss = require('tailwindcss');
const cssnano = require('cssnano');

module.exports = {
    plugins: [
        require('autoprefixer'),
        tailwindcss('./tailwind.config.js'),
        cssnano({
            preset: 'default'
        }),
        purgecss({
            content: ['./src/js/*.js', './public/**/*.php'],
            whitelistPatternsChildren: [/kudos/, /orange/]
        })
    ]
}
