module.exports = {
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
      'base': '16px',
      'lg': '1.125em', //'18px'
      '2xl': '1.5em',
      '4xl': '2.25em', //'36px'
    },
    fontFamily: {
      'sans': ['Merriweather Sans Light', 'Century Gothic', 'sans-serif'],
      'serif': ['Elephant Regular', 'Times New Roman', 'serif']
    },
    maxWidth: {
      'lg': '32em', //'512px'
    },
    spacing: {
      '8': '2em', //'32px'
      '5': '1.25em', //'24px'
      '4': '1em', //'20px'
      '3': '0.75em', //'12px'
      '2':  '0.5em', //'8px'
      '1': '0.25em',
      '0': '0'
    },
    inset: {
      '0': '0',
      'auto': 'auto',
      '1/2': '50%'
    },
    borderRadius: {
      'none': '0',
      'default': '0.25em', //'4px'
      'lg': '0.5em', //'8px'
      'full': '9999px'
    },
    zIndex: {
      '1' : 1,
      '1050': 1050
    },
    extend: {
      colors: {
        orange: {
          '200': '#ffd59c',
          '500': '#ff9f1c',
          '700': '#f58d00',
        },
        green: {
          '500': '#2ec4b6',
          '700': '#2bb9ac',
        },
        modal: '#1a202ccc'
      },
    },
  },
  plugins: [],
  corePlugins: {
    preflight: false
  },
  future: {
    removeDeprecatedGapUtilities: true,
  }
}
