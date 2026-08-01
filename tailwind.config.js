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
                sans: ['"IBM Plex Sans"', ...defaultTheme.fontFamily.sans],
                display: ['"Source Serif 4"', 'Georgia', ...defaultTheme.fontFamily.serif],
            },
            colors: {
                brand: {
                    50: '#eef2f6',
                    100: '#dde5ee',
                    200: '#c0cddc',
                    300: '#93a8c0',
                    400: '#647f9c',
                    500: '#4a6580',
                    600: '#1e3a5f',
                    700: '#163052',
                    800: '#0f2438',
                    900: '#0a1828',
                    950: '#050d14',
                },
                /* Clinical teal — single confident accent */
                health: {
                    50: '#eef8f7',
                    100: '#d5efec',
                    200: '#a9ddd8',
                    300: '#6fc4bc',
                    400: '#3aa59b',
                    500: '#1f8a80',
                    600: '#0f766e',
                    700: '#0c5f59',
                    800: '#0a4b47',
                    900: '#083c39',
                },
                accent: {
                    DEFAULT: '#c9a227',
                    light: '#e6d08a',
                    dark: '#9a7a14',
                },
                ink: {
                    DEFAULT: '#1a2b3c',
                    secondary: '#334155',
                    muted: '#5b6b7c',
                    faint: '#8b97a5',
                },
                surface: {
                    DEFAULT: '#ffffff',
                    muted: '#eef1f4',
                    elevated: '#ffffff',
                },
                canvas: {
                    DEFAULT: '#f3f5f7',
                    dark: '#242731',
                },
                night: {
                    DEFAULT: '#242731',
                    elevated: '#2e3341',
                    muted: '#1c1e26',
                },
                line: {
                    DEFAULT: '#dce2e8',
                    strong: '#9aa8b5',
                },
                status: {
                    pending: '#a16207',
                    approved: '#0f766e',
                    rejected: '#b42318',
                    transit: '#1e3a5f',
                    dispensed: '#0c5f59',
                },
            },
            borderRadius: {
                '4xl': '1.25rem',
            },
            boxShadow: {
                soft: '0 1px 2px rgba(26, 43, 60, 0.06)',
                card: '0 1px 2px rgba(26, 43, 60, 0.05)',
                sidebar: '1px 0 0 rgba(255, 255, 255, 0.08)',
            },
            letterSpacing: {
                display: '-0.02em',
            },
        },
    },

    plugins: [forms],
};
