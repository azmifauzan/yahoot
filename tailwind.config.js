import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './vendor/laravel/jetstream/**/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#f3f1ff',
                    100: '#ebe5ff',
                    200: '#d9d0ff',
                    300: '#bfaeff',
                    400: '#a183ff',
                    500: '#6C5CE7',
                    600: '#6344e0',
                    700: '#5536cc',
                    800: '#462dab',
                    900: '#3a278c',
                    950: '#22165f',
                },
                accent: {
                    red: '#FF6B6B',
                    blue: '#4ECDC4',
                    yellow: '#FFE66D',
                    green: '#A8E6CF',
                },
            },
        },
    },

    plugins: [forms, typography],
};
