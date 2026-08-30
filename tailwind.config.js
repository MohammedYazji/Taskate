import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.jsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Public Sans"', ...defaultTheme.fontFamily.sans],
                serif: ['"Fraunces"', ...defaultTheme.fontFamily.serif],
                mono: ['"Space Mono"', ...defaultTheme.fontFamily.mono],
            },
            colors: {
                brand: {
                    50:  '#F0FDFA',
                    100: '#CCFBF1',
                    200: '#99F6E4',
                    300: '#5EEAD4',
                    400: '#2DD4BF',
                    500: '#14B8A6',
                    600: '#0D9488',
                    700: '#0F766E',
                    800: '#115E59',
                    900: '#134E4A',
                },
                paper: '#FBF9F4',
                ink: '#1A1A1A',
                stone: '#EAE7E0',
                ochre: '#D4A373',
                sage: '#CCD5AE',
                terracotta: '#BC6C25',
                textMain: '#2D2D2D',
                textMuted: '#707070',
            },
            boxShadow: {
                tactile: '0 4px 20px -2px rgba(26, 26, 26, 0.05), 0 2px 10px -2px rgba(26, 26, 26, 0.03)',
                floating: '0 20px 40px -10px rgba(26, 26, 26, 0.08)',
            },
        },
    },

    plugins: [forms],
};
