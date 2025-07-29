/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            colors: {
                primary: {
                    900: "#0d3858",
                    700: "#0f5490",
                    300: "#5a9ccf",
                },
                accent: "#2c8bc6",
                muted: "#bfd8ea",
                "bg-light": "#f4f9fc",
                "text-dark": "#0a2436",
                "text-medium": "#3e5a72",
                "text-light": "#6e8ba5",
            },
        },
    },
    plugins: [],
};
