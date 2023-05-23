/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './resources/**/*.blade.php',
        "./resources/**/*.js"
    ],
    theme: {
        extend: {
            backgroundImage: {
                'authPage': 'url(/public/images/joey-kyber-sFLVTqNzG2I-unsplash.jpg)',
            },
            fontFamily: {
                'logo': 'BroshkLime',
                'valera': 'Valera'
            },
            borderRadius: {
                'ml': '0 0.5rem 0.5rem',
                'mr': '0.5rem 0 0.5rem 0.5rem'
            }
        },
    },
    plugins: [
        require('@tailwindcss/forms'),
    ],
}

