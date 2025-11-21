/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './src/**/*.{vue,js,ts,jsx,tsx}',
    './admin/views/**/*.php'
  ],
  theme: {
    extend: {
      colors: {
        // Rybbit brand colors
        rybbit: {
          primary: '#10b981',    // Emerald-500
          secondary: '#059669',  // Emerald-600
          dark: '#047857',       // Emerald-700
          light: '#6ee7b7',      // Emerald-300
          lighter: '#d1fae5'     // Emerald-100
        },
        // WordPress admin colors
        wp: {
          blue: '#2271b1',
          darkblue: '#135e96'
        }
      },
      fontFamily: {
        sans: [
          '-apple-system',
          'BlinkMacSystemFont',
          '"Segoe UI"',
          'Roboto',
          'Oxygen-Sans',
          'Ubuntu',
          'Cantarell',
          '"Helvetica Neue"',
          'sans-serif'
        ]
      },
      spacing: {
        'wp-admin': '32px' // Standard WordPress admin spacing
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms')({
      strategy: 'class' // Use class-based form styling
    })
  ],
  // Important for WordPress admin compatibility
  important: '#rybbit-admin-app',
  // Prefix classes to avoid conflicts (optional)
  // prefix: 'rybbit-'
}
