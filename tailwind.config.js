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
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50:  '#F5F5F7',
                    100: '#9CA3AF',
                    200: '#5EEAD4',
                    300: '#22C55E',
                    400: '#EF4444',
                    500: '#F59E0B',
                    600: '#14B8A6',
                    700: '#0F9488',
                    800: '#1A1A22',
                    900: '#111116',
                    950: '#0A0A0F',
                },
            },
        },
    },

    plugins: [forms],
};
