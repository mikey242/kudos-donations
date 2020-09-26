module.exports = {
  prefix: 'kd-',
  important: true,
  purge: {
    enabled: process.env.NODE_ENV === 'prod',
    content: [
      './src/js/**/*.js',
      './src/js/**/*.jsx',
      './public/**/*.php',
      './templates/**/*.twig'
    ]
  },
  theme: {
    fontSize: {
      base: '16px',
      sm: '0.875em',
      lg: '1.125em', //'18px'
      '2xl': '1.5em',
      '4xl': '2.25em', //'36px'
    },
    fontFamily: {
      sans: ['montserratregular', 'Century Gothic', 'sans-serif'],
      serif: ['libre_baskervillebold', 'Times New Roman', 'serif']
    },
    boxShadow: {
      none: 'none',
      focus: '0 0 0 1px #fff, 0 0 0 3px var(--kudos-theme-color)'
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
      5: '1.25em',
      4: '1em',
      3: '0.75em',
      2:  '0.5em',
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
      default: '0.25em', //'4px'
      lg: '0.5em', //'8px'
      full: '9999px'
    },
    zIndex: {
      '-1': -1,
      1 : 1,
      1050: 1050
    },
    extend: {
      backgroundImage: theme => ({
        'logo-black': 'url("../img/logo-black.svg")',
        'logo-white': 'url("../img/logo-white.svg")',
        'logo-color': 'url("../img/logo-colour.svg")',
        'back-icon': 'url("../img/back-icon.svg")'
      }),
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
        theme: "var(--kudos-theme-color)",
        'theme-dark': "var(--kudos-theme-color-dark)",
        'theme-darker': "var(--kudos-theme-color-darker)"
      },
      boxShadow: {
        focus: '0 0 0 2px #fff, 0 0 0 3.5px var(--kudos-theme-color)'
      },
      keyframes: {
        spin: {
          '0%': { transform: 'rotate(0)', 'animation-timing-function': 'cubic-bezier(0.55, 0.055, 0.675, 0.19)' },
          '50%': { transform: 'rotate(900deg)', 'animation-timing-function': 'cubic-bezier(0.215, 0.61, 0.355, 1)' },
          '100%': { transform: 'rotate(1800deg)' }
        },
        fadeIn: {
          '0%': { opacity: 0 },
          '100%': { opacity: 1 }
        },
        fadeOut: {
          '0%': { opacity: 1 },
          '100%': { opacity: 0 }
        },
        slideIn: {
          '0%': { transform: 'translateY(15%)' },
          '100%': { transform: 'translateY(0)' }
        },
        slideOut: {
          '0%': { transform: 'translateY(0)' },
          '100%': { transform: 'translateY(-10%)' }
        }
      },
      animation: {
        spin: 'spin 2s infinite',
        'fade-in': 'fadeIn 0.3s cubic-bezier(0, 0, 0.2, 1)',
        'fade-out': 'fadeOut 0.3s cubic-bezier(0, 0, 0.2, 1)',
        'slide-in': 'slideIn 0.3s cubic-bezier(0, 0, 0.2, 1)',
        'slide-out': 'slideOut 0.3s cubic-bezier(0, 0, 0.2, 1)'
      }
    },
  },
  variants: {
    backgroundColor: ['hover', 'checked'],
    borderColor: ['focus'],
    textColor: ['hover'],
    boxShadow: ['focus'],
    outline: ['focus']
  },
  plugins: [],
  corePlugins: {
    preflight: false
  },
  future: {
    removeDeprecatedGapUtilities: true,
    purgeLayersByDefault: true,
  }
}
