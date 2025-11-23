/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      screens: {
        'xs': '475px',
        // Adds a custom breakpoint for extra small screens
      },
    },
  },
  plugins: [],
};
