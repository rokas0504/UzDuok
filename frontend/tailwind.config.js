module.exports = {
  purge: [],
  content: [
    './components/**/*.{js,vue,ts}',
    './layouts/**/*.vue',
    './pages/**/*.vue',
    './plugins/**/*.{js,ts}',
    './nuxt.config.{js,ts}',
  ],
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {
      colors: {
        primary: '',
        secondary: '',
        success: '',
        error: '',
        warning: '',
        info: '',
        background: '',
        surface: '',
      }
    },
  },
  variants: {
    extend: {},
  },
  plugins: [],
}
