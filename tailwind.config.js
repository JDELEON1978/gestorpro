import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      colors: {
        inde: {
          azul: "var(--inde-azul)",
          celeste: "var(--inde-celeste)",
          verde: "var(--inde-verde)",
          gris: "var(--inde-gris)",
          negro: "var(--inde-negro)",
        },
        appbg: "var(--bg)",
        card: "var(--card)",
        border: "var(--border)",
      },
    },
  },
  plugins: [],
}

