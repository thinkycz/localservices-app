import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Manrope Variable', 'Manrope', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#edf8f6',
                    100: '#d4eee9',
                    200: '#abddd4',
                    300: '#76c4b8',
                    400: '#43a89c',
                    500: '#208b81',
                    600: '#0F766E',
                    700: '#115E59',
                    800: '#124c49',
                    900: '#123f3d',
                    950: '#062625',
                },
                canvas: '#F7F6F2',
                ink: '#17211F',
                muted: '#66736F',
                line: '#DDE3DF',
                accent: '#F59E0B',
                success: '#2F855A',
                danger: '#C2413B',
            },
            borderRadius: {
                '2xl': '1rem',
            },
            boxShadow: {
                soft: '0 12px 32px -18px rgba(23, 33, 31, 0.28)',
                lift: '0 18px 44px -24px rgba(23, 33, 31, 0.35)',
            },
        },
    },

    plugins: [forms],
};
