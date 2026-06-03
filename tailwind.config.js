import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
                serif: ['Lora', ...defaultTheme.fontFamily.serif],
                mono: ['"JetBrains Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                paper: {
                    DEFAULT: '#faf7ee',
                    50: '#fdfbf5',
                    100: '#faf7ee',
                    200: '#f2ecdd',
                    300: '#e8dfc7',
                },
                ink: {
                    DEFAULT: '#0e1412',
                    50: '#f5f6f5',
                    100: '#e5e7e6',
                    200: '#c7cbc9',
                    700: '#1f2a27',
                    800: '#141b19',
                    900: '#0e1412',
                    950: '#070a09',
                },
                brand: {
                    50: '#ecfdf5',
                    100: '#d1fae5',
                    200: '#a7f3d0',
                    300: '#6ee7b7',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                    950: '#042f2e',
                },
            },
            boxShadow: {
                hard: '4px 4px 0 0 rgba(14, 20, 18, 1)',
                'hard-sm': '2px 2px 0 0 rgba(14, 20, 18, 1)',
                'hard-brand': '4px 4px 0 0 rgba(15, 118, 110, 1)',
            },
        },
    },

    plugins: [forms, typography],
};
