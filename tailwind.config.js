import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                card: {
                    DEFAULT: '#111111',
                    hover: '#161616',
                },
                border: {
                    DEFAULT: '#1e1e1e',
                    hover: '#2a2a2a',
                },
                accent: {
                    DEFAULT: '#a3e635',
                    dim: 'rgba(163,230,53,0.1)',
                    hover: '#bef264',
                },
                skin: {
                    consumer: '#b0c3d9',
                    industrial: '#5e98d9',
                    mil_spec: '#4b69ff',
                    restricted: '#8847ff',
                    classified: '#d32ce6',
                    covert: '#eb4b4b',
                    extraordinary: '#e4ae39',
                },
            },
        },
    },

    plugins: [forms],
};
