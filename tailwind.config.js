/** @type {import('tailwindcss').Config} */
module.exports = {
    darkMode: 'class',
    content: [
        './resources/**/*.vue',
        './resources/**/*.js',
        './resources/**/*.ts',
        './resources/**/*.jsx',
        './resources/**/*.tsx',
    ],
    theme: {
        extend: {},
    },
    plugins: [],
    corePlugins: {
        preflight: false,
    },
    important: '.papertrail-tool',

}
