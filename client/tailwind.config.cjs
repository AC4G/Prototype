/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./src/templates/*.tsx"],
  theme: {
    extend: {

    },
    fontSize: {
      '10': '10px'
    }
  },
  plugins: [
    require("tailwindcss-autofill"),
    require("tailwindcss-shadow-fill"),
    require("tailwindcss-text-fill")
  ],
}
