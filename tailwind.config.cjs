/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./templates/**/*.twig"],
  theme: {
    extend: {

    },
    fontSize: {

    }
  },
  plugins: [
    require("tailwindcss-autofill"),
    require("tailwindcss-shadow-fill"),
    require("tailwindcss-text-fill"),
    require("tailwind-scrollbar")
  ],
}
