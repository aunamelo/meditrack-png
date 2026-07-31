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
                    50: '#eef2f7',
                    100: '#e8eef4',
                    200: '#c5d0de',
                    300: '#94a8be',
                    400: '#5a7494',
                    500: '#3d5a7a',
                    600: '#1e3a5f',
                    700: '#163052',
                    800: '#0f2438',
                    900: '#0a1828',
                    950: '#050d14',
                },
                /* Clinical teal — health accent that pairs with NDoH navy */
                health: {
                    50: '#f0fdfa',
                    100: '#ccfbf1',
                    200: '#99f6e4',
                    300: '#5eead4',
                    400: '#2dd4bf',
                    500: '#14b8a6',
                    600: '#0d9488',
                    700: '#0f766e',
                    800: '#115e59',
                    900: '#134e4a',
                },
                accent: {
                    DEFAULT: '#fcd116',
                    light: '#fde68a',
                    dark: '#d4a012',
                },
                ink: {
                    DEFAULT: '#1e3a5f',
                    secondary: '#334155',
                    muted: '#64748b',
                    faint: '#94a3b8',
                },
                surface: {
                    DEFAULT: '#ffffff',
                    muted: '#e8eef4',
                    elevated: '#ffffff',
                },
                canvas: {
                    DEFAULT: '#ffffff',
                    dark: '#242731',
                },
                /* Dark-mode charcoal surfaces */
                night: {
                    DEFAULT: '#242731',
                    elevated: '#2e3341',
                    muted: '#1c1e26',
                },
                line: {
                    DEFAULT: '#e2e8f0',
                    strong: '#94a3b8',
                },
            },
            borderRadius: {
                '4xl': '1.75rem',
            },
            boxShadow: {
                soft: '0 2px 8px rgba(30, 58, 95, 0.06), 0 12px 32px -12px rgba(30, 58, 95, 0.12)',
                glow: '0 4px 20px -4px rgba(30, 58, 95, 0.2)',
                card: '0 4px 20px rgba(30, 58, 95, 0.08)',
                sidebar: '4px 0 24px rgba(30, 58, 95, 0.18)',
            },
            letterSpacing: {
                display: '-0.025em',
            },
        },
    },

    plugins: [forms],
};
