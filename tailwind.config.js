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
                    50: '#edf8f4',
                    100: '#d4f0e8',
                    200: '#a8e0d0',
                    300: '#6ecfb5',
                    400: '#2bb381',
                    500: '#0d705c',
                    600: '#0a5c4a',
                    700: '#084c41',
                    800: '#063830',
                    900: '#042822',
                    950: '#021612',
                },
                accent: {
                    DEFAULT: '#2bb381',
                    light: '#5fd4a4',
                    dark: '#1f9968',
                },
                ink: {
                    DEFAULT: '#1a2e2b',
                    secondary: '#3d524e',
                    muted: '#6b7f7a',
                    faint: '#9aaba6',
                },
                surface: {
                    DEFAULT: '#ffffff',
                    muted: '#f4f7f6',
                    elevated: '#ffffff',
                },
                canvas: {
                    DEFAULT: '#f4f7f6',
                    dark: '#eef2f1',
                },
                line: {
                    DEFAULT: '#e8eeec',
                    strong: '#d1dcd9',
                },
            },
            borderRadius: {
                '4xl': '1.75rem',
            },
            boxShadow: {
                soft: '0 2px 8px rgba(8, 76, 65, 0.06), 0 12px 32px -12px rgba(8, 76, 65, 0.12)',
                glow: '0 4px 24px -4px rgba(43, 179, 129, 0.35)',
                card: '0 4px 20px rgba(8, 76, 65, 0.08)',
                sidebar: '4px 0 24px rgba(8, 76, 65, 0.15)',
            },
            letterSpacing: {
                display: '-0.025em',
            },
        },
    },

    plugins: [forms],
};
