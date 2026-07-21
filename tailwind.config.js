import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                brand: {
                    50: '#eef7f7',
                    100: '#d7ecec',
                    200: '#b3deda',
                    300: '#85cbc4',
                    400: '#56b5ab',
                    500: '#0f766e',
                    600: '#0d5f59',
                    700: '#0a4b46',
                    800: '#083836',
                    900: '#062d2b',
                    950: '#031a19',
                },
                ink: {
                    DEFAULT: '#0f2027',
                    secondary: '#33454b',
                    muted: '#64757b',
                    faint: '#94a3ab',
                },
                surface: {
                    DEFAULT: '#ffffff',
                    muted: '#f4f8f7',
                    elevated: '#ffffff',
                },
                line: {
                    DEFAULT: '#dfe7e6',
                    strong: '#c5d4d2',
                },
            },
            boxShadow: {
                soft: '0 1px 2px rgba(15, 32, 39, 0.04), 0 8px 24px -8px rgba(15, 32, 39, 0.12)',
                glow: '0 0 0 1px rgba(15, 118, 110, 0.08), 0 8px 32px -12px rgba(15, 118, 110, 0.35)',
            },
            letterSpacing: {
                display: '-0.025em',
            },
        },
    },

    plugins: [forms],
};
