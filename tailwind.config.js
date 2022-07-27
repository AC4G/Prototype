/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./templates/**/*.twig"
  ],
  variants: {

  },
  theme: {
    extend: {
      colors: {
        'grey': '#888888',
      },
      width: {

      },
      transitionProperty: {
        'opacity': 'opacity',
      },
      zIndex: {
        '[-1]': '-1',
      }
    },
    fontFamily: {
      'inter': ['Inter', 'sans-serif'],
    },
  },
  plugins: [],
}
