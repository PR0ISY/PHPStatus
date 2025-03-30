/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './templates/**/*.{html,twig}',
        './assets/**/*.{js,jsx,ts,tsx}',
    ],
    theme: {extend: {
            colors: {
                dark: {
                    950: 'var(--color-dark-950)',
                    900: 'var(--color-dark-900)',
                    800: 'var(--color-dark-800)',
                    700: 'var(--color-dark-700)',
                    600: 'var(--color-dark-600)',
                    500: 'var(--color-dark-500)',
                    400: 'var(--color-dark-400)',
                    300: 'var(--color-dark-300)',
                    200: 'var(--color-dark-200)',
                    100: 'var(--color-dark-100)',
                },
                accent: {
                    500: 'var(--color-accent-500)',
                    400: 'var(--color-accent-400)',
                    300: 'var(--color-accent-300)',
                },
                success: {
                    500: 'var(--color-success-500)',
                    400: 'var(--color-success-400)',
                },
                warning: {
                    500: 'var(--color-warning-500)',
                    400: 'var(--color-warning-400)',
                },
                error: {
                    500: 'var(--color-error-500)',
                    400: 'var(--color-error-400)',
                },
            }}},
    plugins: [],
}
