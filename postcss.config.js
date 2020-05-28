/* eslint-disable */
const tailwindcss = require('tailwindcss');

const cssnano = require('cssnano')({
    preset: 'default'
});

module.exports = {
    plugins: [
        require('autoprefixer'),
        tailwindcss('./tailwind.config.js'),
        ...[cssnano]
    ]
}
