/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './public/js/**/*.js',
    ],
    theme: {
        extend: {
            fontFamily: {
                cinzel: ['Cinzel', 'serif'],
                crimson: ['Crimson Pro', 'serif'],
                mono: ['Space Mono', 'monospace'],
            },
        },
    },
    plugins: [],
};
