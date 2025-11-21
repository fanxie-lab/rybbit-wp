import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import '../styles/admin.css'

// Create Vue app
const app = createApp(App)

// Use Pinia for state management
app.use(createPinia())

// Use Vue Router
app.use(router)

// Mount the app
app.mount('#rybbit-admin-app')

// Make app instance available globally for debugging
if (import.meta.env.DEV) {
  window.__RYBBIT_APP__ = app
}
