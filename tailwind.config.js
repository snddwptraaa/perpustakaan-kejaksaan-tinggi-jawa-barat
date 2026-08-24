import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],
    theme: {
        extend: {
            colors: {
                kejati: {
                    DEFAULT: '#166534',
                    dark: '#064E3B',
                    green: '#15803D',
                    gold: '#F4C542',
                    'gold-dark': '#B89100',
                    ink: '#1F2937',
                    canvas: '#F7FAF8',
                    surface: '#FFFFFF',
                },
            },
            fontFamily: {
                sans: ['Inter', 'ui-sans-serif', 'system-ui', ...defaultTheme.fontFamily.sans],
                display: ['Georgia', ...defaultTheme.fontFamily.serif],
            },
            boxShadow: {
                'soft': '0 1px 2px rgba(15,23,42,.03), 0 18px 45px rgba(15,23,42,.06)',
            },
        },
    },
    plugins: [forms],
};
