const defaultTheme = require('tailwindcss/defaultTheme')
const plugin = require('tailwindcss/plugin')

module.exports = {
  important: false,
  content: [
    './src/admin/**/*.{js,jsx}',
    './src/blocks/**/*.{js,jsx}',
    './src/common/**/*.{js,jsx}',
    './src/public/**/*.{js,jsx}',
    './src/helpers/**/*.{js,jsx}',
    './templates/**/*.twig',
    './safelist.txt'
  ],
  theme: {
    screens: {
      xs: '475px',
      ...defaultTheme.screens
    },
    fontFamily: {
      sans: ['montserratregular', 'Century Gothic', 'sans-serif'],
      serif: ['libre_baskervillebold', 'Times New Roman', 'serif'],
      mono: ['ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace']
    },
    minWidth: {
      0: '0',
      1: '1rem',
      full: '100%'
    },
    backgroundSize: {
      contain: 'contain',
      8: '2rem'
    },
    borderRadius: {
      none: '0',
      DEFAULT: '0.25rem',
      sm: '2px',
      lg: '0.5rem',
      full: '9999px'
    },
    borderWidth: {
      DEFAULT: '2px',
      0: '0',
      1: '1px',
      2: '2px'
    },
    zIndex: {
      '-1': -1,
      0: 0,
      1: 1,
      1050: 1050
    },
    extend: {
      fontSize: {
        base: '16px',
        xs: '0.75rem',
        sm: '0.875rem',
        lg: '1.125rem', // '18px'
        '2xl': '1.5rem',
        '4xl': '2.25rem' // '36px'
      },
      inset: {
        0: '0',
        auto: 'auto',
        '1/2': '50%'
      },
      colors: {
        orange: {
          200: '#ffd59c',
          500: '#ff9f1c',
          700: '#f58d00'
        },
        green: {
          500: '#2ec4b6',
          700: '#2bb9ac'
        },
        modal: '#1a202ccc',
        primary: 'var(--kudos-theme-primary)',
        'primary-dark': 'var(--kudos-theme-primary-dark)',
        'primary-darker': 'var(--kudos-theme-primary-darker)',
        secondary: 'var(--kudos-theme-secondary)'
      },
      ringWidth: {
        DEFAULT: '2px'
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
          '100%': { transform: 'rotate(1800deg)' }
        }
      },
      animation: {
        'loader-spin': 'loaderSpin 2s infinite'
      }
    }
  },
  corePlugins: {
    preflight: true
  },
  plugins: [
    plugin(function ({ addUtilities }) {
      addUtilities({
        '.rotate-x-180': {
          transform: 'rotateX(180deg)'
        }
      })
    })
  ]
}
