module.exports = {
    plugins: [
        require('tailwindcss'),
        require("postcss-prefixwrap")('[id^=kudos\\-donations\\-]', {
            whitelist: ['kudos-public.css'],
            ignoredSelectors: [':root'],
        }),
    ]
}