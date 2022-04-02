module.exports = {
  important: true,
  content: [
    './src/admin/**/*.{js,jsx}',
    './src/blocks/**/*.{js,jsx}',
    './src/common/**/*.{js,jsx}'
  ],
  theme: {
    fontFamily: {
      sans: ['montserratregular', 'Century Gothic', 'sans-serif'],
      serif: ['libre_baskervillebold', 'Times New Roman', 'serif'],
      mono: ['ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace']
    },
    zIndex: {
      '-1': -1,
      1: 1,
      1050: 1050
    },
    extend: {
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
        primary: 'var(--kudos-theme-primary)',
        'primary-dark': 'var(--kudos-theme-primary-dark)',
        'primary-darker': 'var(--kudos-theme-primary-darker)',
        secondary: 'var(--kudos-theme-secondary)'
      },
      borderWidth: {
        DEFAULT: '2px',
        0: '0',
        1: '1px',
        2: '2px'
      }
    },
    corePlugins: {
      preflight: true
    }
  }
}
