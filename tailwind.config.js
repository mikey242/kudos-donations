module.exports = {
    mode: 'jit',
    prefix: 'kd-',
    important: true,
    purge: {
        content: [
            './src/js/**/*.{js,jsx}',
            './templates/**/*.twig'
        ]
    },
    theme: {
        container: {
            center: true,
            screens: {
                sm: "100%",
                md: "100%",
                lg: "768px",
            }
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
            max: 'max-content'
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
        borderRadius: {
            none: '0',
            DEFAULT: '0.25em', //'4px'
            sm: '2px',
            lg: '0.5em', //'8px'
            full: '9999px'
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
                'primary-darker': "var(--kudos-theme-primary-darker)"
            },
            boxShadow: {
                'button-group': 'inset 0 0 0 1px var(--wp-admin-theme-color);'
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
            }
        },
    },
    plugins: [],
    corePlugins: {
        preflight: false
    }
}
