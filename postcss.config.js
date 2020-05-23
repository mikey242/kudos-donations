/* eslint-disable */
const tailwindcss = require('tailwindcss');

const purgecss = require('@fullhuman/postcss-purgecss')({
    content: ['./src/js/*.js', './public/**/*.php', './templates/**/*.twig'],
    whitelistPatternsChildren: [/kudos/, /orange/]
});

const cssnano = require('cssnano')({
    preset: 'default'
});

module.exports = {
    plugins: [
        require('autoprefixer'),
        tailwindcss('./tailwind.config.js'),
        // ...[cssnano, purgecss]
    ]
}
