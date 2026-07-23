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
                coral: {
                    50:  '#FFF0EC',
                    100: '#FFE0D9',
                    200: '#FFC7BA',
                    300: '#FFB6A6',
                    400: '#E8A594',
                    500: '#D4947F',
                    600: '#B87A65',
                },
                cream: {
                    50:  '#FFF9F4',
                    100: '#FFEBD3',
                    200: '#FFDCC0',
                    300: '#FFCBA8',
                },
                mint: {
                    50:  '#F0FAF7',
                    100: '#D4F0EA',
                    200: '#B8E6DD',
                    300: '#9BCEC1',
                    400: '#7FBDB0',
                    500: '#66AC9F',
                },
                sky: {
                    50:  '#EFF6FB',
                    100: '#D5E8F4',
                    200: '#B3D8EC',
                    300: '#8FC5E2',
                    400: '#74B5D8',
                    500: '#67A2C5',
                    600: '#4E8AAE',
                    700: '#3A6F91',
                    800: '#2A5270',
                },
                brand: {
                    50:  '#FFF9F4',
                    100: '#FFEBD3',
                    200: '#FFDCC0',
                    300: '#FFB6A6',
                    400: '#E8A594',
                    500: '#9BCEC1',
                    600: '#67A2C5',
                    700: '#4E8AAE',
                    800: '#3A6F91',
                    900: '#2A5270',
                },
            },
        },
    },

    plugins: [forms],
};
