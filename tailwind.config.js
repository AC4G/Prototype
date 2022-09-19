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
        '12': '12',
        '100': '100',
        '110': '100',
      }
    },
    fontFamily: {
      'inter': ['Inter', 'sans-serif'],
    },
  },
  plugins: [
    require("tailwindcss-autofill"),
    require('tailwind-scrollbar-hide')
  ],
}
