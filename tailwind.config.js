const defaultTheme = require('tailwindcss/defaultTheme')
const svgToDataUri = require('mini-svg-data-uri')
const {colors} = defaultTheme
const plugin = require('tailwindcss/plugin')

module.exports = {
    mode: 'jit',
    prefix: 'kd-',
    important: true,
    purge: {
        enabled: true,
        content: [
            './src/admin/**/*.{js,jsx}',
            './src/blocks/**/*.{js,jsx}',
            './src/public/**/*.{js,jsx}',
            './src/helpers/**/*.{js,jsx}',
            './templates/**/*.twig',
            './safelist.txt'
        ],
    },
    theme: {
        screens: {
            'xs': '475px',
            ...defaultTheme.screens
        },
        fontSize: {
            base: '16px',
            xs: '0.75em',
            sm: '0.875em',
            lg: '1.125em', //'18px'
            '2xl': '1.5em',
            '4xl': '2.25em', //'36px'
        },
        fontFamily: {
            sans: ['montserratregular', 'Century Gothic', 'sans-serif'],
            serif: ['libre_baskervillebold', 'Times New Roman', 'serif']
        },
        maxWidth: {
            lg: '32em',
        },
        minWidth: {
            0: '0',
            1: '1em',
            full: '100%',
        },
        spacing: {
            8: '2em',
            6: '1.5em',
            5: '1.25em',
            4: '1em',
            3: '0.75em',
            2: '0.5em',
            1: '0.25em',
            0: '0'
        },
        inset: {
            0: '0',
            auto: 'auto',
            '1/2': '50%'
        },
        backgroundSize: {
            'contain': 'contain',
            '8': '2em'
        },
        borderRadius: {
            none: '0',
            DEFAULT: '0.25em',
            sm: '2px',
            lg: '0.5em',
            full: '9999px'
        },
        borderWidth: {
            DEFAULT: '2px',
            '0': '0',
            '1': '1px',
            '2': '2px',
        },
        zIndex: {
            '-1': -1,
            0: 0,
            1: 1,
            1050: 1050
        },
        extend: {
            colors: {
                orange: {
                    200: '#ffd59c',
                    500: '#ff9f1c',
                    700: '#f58d00',
                },
                green: {
                    500: '#2ec4b6',
                    700: '#2bb9ac',
                },
                modal: '#1a202ccc',
                primary: "var(--kudos-theme-primary)",
                'primary-dark': "var(--kudos-theme-primary-dark)",
                'primary-darker': "var(--kudos-theme-primary-darker)",
                secondary: "var(--kudos-theme-secondary)"
            },
            boxShadow: {
                'button-group': 'inset 0 0 0 1px var(--wp-admin-theme-color);'
            },
            backgroundImage: theme => ({
                'back-button': `url("${svgToDataUri(
                    `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="${theme(
                        'colors.gray.500',
                        colors.gray[500]
                    )}" fill="none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                            </svg>`
                )}")`,
                'next': `url("${svgToDataUri(
                    `<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 256 512"><defs/><path fill="white" d="M224.3 273l-136 136a23.9 23.9 0 01-33.9 0l-22.6-22.6a23.9 23.9 0 010-33.9l96.4-96.4-96.4-96.4a23.9 23.9 0 010-33.9L54.3 103a23.9 23.9 0 0133.9 0l136 136a24 24 0 01.1 34z"/></svg>`
                )}")`,
                'close-button': `url("${svgToDataUri(
                    `<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" stroke="${theme(
                        'colors.gray.500',
                        colors.gray[500]
                    )}" fill="none">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>`
                )}")`,
                'radio-checked': `url("${svgToDataUri(
                    `<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg"><circle cx="8" cy="8" r="3"/></svg>`
                )}")`,
                'checkbox-checked': `url("${svgToDataUri(
                    `<svg viewBox="0 0 16 16" fill="white" xmlns="http://www.w3.org/2000/svg"><path d="M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z"/></svg>`
                )}")`,
                'select': `url("${svgToDataUri(
                    `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path stroke="${theme(
                        'colors.gray.500',
                        colors.gray[500]
                    )}" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 8l4 4 4-4"/></svg>`
                )}")`,
                'vendor-mollie': `url("${svgToDataUri(
                    `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 677 200"><defs/><g fill="#000"><path fill-rule="evenodd" d="M286.3 65.3a67.4 67.4 0 10.2 134.9 67.4 67.4 0 00-.2-134.9zm0 102.8a35.5 35.5 0 11.1-70.9 35.5 35.5 0 010 71z" clip-rule="evenodd"/><path d="M510.4 42a21 21 0 100-42 21 21 0 000 42z"/><path fill-rule="evenodd" d="M148.8 65.4c-1.7-.2-3.4-.2-5.1-.2A58.5 58.5 0 00101 83.6a58.5 58.5 0 00-101 40v73.7h31.5v-72.8a27.9 27.9 0 0126.6-27.2 26.6 26.6 0 0126.5 26.5v73.5h32.2v-73a27.8 27.8 0 0126.7-27 26.6 26.6 0 0126.6 26.3v73.7h32.2v-72.8a59.8 59.8 0 00-15.4-40 57.4 57.4 0 00-38-19.1z" clip-rule="evenodd"/><path d="M403.3 3.1H371v194.3h32.2zm61.6 0h-32.2v194.3h32.2zm61.6 65.4h-32.2v128.8h32.2z"/><path fill-rule="evenodd" d="M677 129.6a64.4 64.4 0 00-63.8-64.4h-.8a67.2 67.2 0 00-47 114.7c12.9 12.9 29.8 20 47.9 20a67.8 67.8 0 0058-33l1.6-2.6-26.6-13-1.4 2a36.2 36.2 0 01-65.9-9h98zm-65-35.2c14.7 0 27.8 9.7 32.4 23.4h-64.9A34.4 34.4 0 01612 94.4z" clip-rule="evenodd"/></g></svg>`
                )}")`,
                'lock':`url("${svgToDataUri(
                    `<svg width="100" height="100" viewBox="0 0 448 512" xmlns="http://www.w3.org/2000/svg" stroke-linejoin="round"><path d="M313.5 228L300.8 228 300.8 189.8C300.8 145.2 264.5 109 220 109 175.5 109 139.3 145.2 139.3 189.8L139.3 228 126.5 228C112.4 228 101 239.4 101 253.5L101 355.5C101 369.6 112.4 381 126.5 381L313.5 381C327.6 381 339 369.6 339 355.5L339 253.5C339 239.4 327.6 228 313.5 228ZM258.3 228L181.8 228 181.8 189.8C181.8 168.7 198.9 151.5 220 151.5 241.1 151.5 258.3 168.7 258.3 189.8L258.3 228Z" fill="white"/></svg>`
                )}")`,
            }),
            backgroundPosition: {
                'right-2': `right 0.5em center`,
                'left-2': `left 0.5em center`
            },
            ringWidth: {
                'DEFAULT': '2px',
            },
            keyframes: {
                loaderSpin: {
                    '0%': {
                        transform: 'rotate(0)',
                        'animation-timing-function': 'cubic-bezier(0.55, 0.055, 0.675, 0.19)'
                    },
                    '50%': {
                        transform: 'rotate(900deg)',
                        'animation-timing-function': 'cubic-bezier(0.215, 0.61, 0.355, 1)'
                    },
                    '100%': {transform: 'rotate(1800deg)'}
                },
                fadeIn: {
                    '0%': {opacity: 0},
                    '100%': {opacity: 1}
                },
                fadeOut: {
                    '0%': {opacity: 1},
                    '100%': {opacity: 0}
                },
                slideIn: {
                    '0%': {transform: 'rotate(-0.5deg) translate(2%, 2%)'},
                    '100%': {transform: 'rotate(0) translate(0)'}
                },
                slideOut: {
                    '0%': {transform: 'translateY(0)'},
                    '100%': {transform: 'rotate(-0.5deg) translate(2%, 2%)'}
                }
            },
            animation: {
                'loader-spin': 'loaderSpin 2s infinite',
                'fade-in': 'fadeIn 0.3s cubic-bezier(0, 0, 0.2, 1)',
                'fade-out': 'fadeOut 0.3s cubic-bezier(0, 0, 0.2, 1)',
                'slide-in': 'slideIn 0.3s cubic-bezier(0, 0, 0.2, 1)',
                'slide-out': 'slideOut 0.3s cubic-bezier(0, 0, 0.2, 1)'
            },
        },
    },
    corePlugins: {
        preflight: false
    },
    plugins: [
        plugin(function ({ addUtilities }) {
            addUtilities({
                '.rotate-x-180': {
                    transform: 'rotateX(180deg)',
                },
            })
        })
    ]
}
