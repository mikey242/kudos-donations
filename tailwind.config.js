module.exports = {
  purge: [
    './src/js/**/*.js',
    './src/js/**/*.jsx',
    './public/**/*.php',
    './templates/**/*.twig'
  ],
  theme: {
    fontSize: {
      'base': '16px',
      'lg': '1.125em', //'18px'
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
    borderRadius: {
      'default': '0.25em', //'4px'
      'lg': '0.5em', //'8px'
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
  }
}
