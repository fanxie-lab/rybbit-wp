import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import { resolve } from 'path'

// https://vitejs.dev/config/
export default defineConfig({
  plugins: [vue()],

  resolve: {
    alias: {
      '@': resolve(__dirname, 'src'),
      '@admin': resolve(__dirname, 'src/admin'),
      '@blocks': resolve(__dirname, 'src/blocks'),
      '@components': resolve(__dirname, 'src/admin/components'),
      '@views': resolve(__dirname, 'src/admin/views'),
      '@stores': resolve(__dirname, 'src/admin/stores'),
      '@services': resolve(__dirname, 'src/admin/services')
    }
  },

  build: {
    // Output directory
    outDir: 'assets',

    // Generate manifest for PHP to reference
    manifest: true,

    // Create separate chunks for better caching
    rollupOptions: {
      input: {
        admin: resolve(__dirname, 'src/admin/main.js'),
        blocks: resolve(__dirname, 'src/blocks/index.js')
      },
      output: {
        // Output structure
        entryFileNames: 'js/[name].js',
        chunkFileNames: 'js/[name]-[hash].js',
        assetFileNames: (assetInfo) => {
          if (assetInfo.name.endsWith('.css')) {
            return 'css/[name][extname]'
          }
          return 'images/[name][extname]'
        }
      }
    },

    // Minification
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true
      }
    },

    // Source maps for development
    sourcemap: process.env.NODE_ENV === 'development'
  },

  // Development server config
  server: {
    port: 3000,
    strictPort: true,
    hmr: {
      host: 'localhost',
      protocol: 'ws'
    }
  },

  // CSS config
  css: {
    postcss: {
      plugins: [
        require('tailwindcss'),
        require('autoprefixer')
      ]
    }
  },

  // Test configuration
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: ['./src/admin/__tests__/setup.js'],
    coverage: {
      provider: 'v8',
      reporter: ['text', 'json', 'html'],
      exclude: [
        'node_modules/',
        'src/admin/__tests__/',
        '**/*.spec.js',
        '**/*.test.js'
      ]
    }
  }
})
