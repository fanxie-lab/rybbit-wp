import { createRouter, createWebHashHistory } from 'vue-router'
import Dashboard from './views/Dashboard.vue'
import Settings from './views/Settings.vue'
import Exclusions from './views/Exclusions.vue'
import WooCommerce from './views/WooCommerce.vue'
import SetupWizard from './views/SetupWizard.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard
  },
  {
    path: '/settings',
    name: 'Settings',
    component: Settings
  },
  {
    path: '/exclusions',
    name: 'Exclusions',
    component: Exclusions
  },
  {
    path: '/woocommerce',
    name: 'WooCommerce',
    component: WooCommerce
  },
  {
    path: '/setup',
    name: 'SetupWizard',
    component: SetupWizard
  }
]

const router = createRouter({
  // Use hash history for WordPress admin compatibility
  history: createWebHashHistory(),
  routes
})

export default router
