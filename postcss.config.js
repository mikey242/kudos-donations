module.exports = {
    plugins: [
        require('tailwindcss'),
        require('postcss-add-root-selector')({
            rootSelector: '[id^=kudos-donations-]',
            include: ['kudos-public.scss']
        }),
        require('autoprefixer'),
        require('cssnano'),
    ]
}