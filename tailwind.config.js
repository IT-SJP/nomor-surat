/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        sjp: {
          50: '#f0fdfa',
          100: '#ccfbf1',
          200: '#99f6e4',
          300: '#5eead4',
          400: '#2dd4bf',
          500: '#14b8a6',
          600: '#0d9488',
          700: '#0f766e',
          800: '#115e59',
          900: '#134e4a',
          teal: '#1b807a',
          dark: '#135c58',
          light: '#2bb3ab',
        }
      }
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["corporate", "emerald", "dark"],
    defaultTheme: "corporate"
  }
}
