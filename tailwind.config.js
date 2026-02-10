module.exports = {
    content: [
        "node_modules/@frostui/tailwindcss/**/*.js",
        './storage/framework/views/*.php',
        './resources/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.jsx',
        './resources/**/*.vue',
    ],
    darkMode: ['class', '[data-mode="dark"]'],
    theme: {

        container: {
            center: true,
        },

        fontFamily: {
            'base': ['Inter', 'sans-serif'],
        },

        extend: {
            keyframes: {
                fadeIn: {
                    '0%': { opacity: 0 },
                    '100%': { opacity: 1 }
                },
                slideUp: {
                    '0%': { transform: 'translateY(20px)' },
                    '100%': { transform: 'translateY(0)' }
                }
            },
            colors: {
                'primary': '#3073F1',

                'secondary': '#68625D',

                'success': '#1CB454',

                'warning': '#E2A907',

                'info': '#0895D8',

                'danger': '#E63535',

                'light': '#eef2f7',
                'dark': '#313a46',
            },
        },
    },

    plugins: [
        require('@frostui/tailwindcss/plugin'),
        require('@tailwindcss/forms'),
        require('@tailwindcss/typography'),
        require('@tailwindcss/aspect-ratio'),

    ],

}
