import { defineStore } from 'pinia'
import { ref } from 'vue'
import api from '../services/api'

export const useSettingsStore = defineStore('settings', () => {
  // State
  const settings = ref({
    site_id: '',
    script_url: 'https://app.rybbit.io/api/script.js',
    connected: false,
    skip_patterns: ['/wp-admin/**', '/wp-login.php'],
    mask_patterns: [],
    replay_mask_selectors: [],
    debounce_delay: 500,
    woocommerce: {
      enabled: false,
      events: {
        view_item: true,
        add_to_cart: true,
        begin_checkout: true,
        purchase: true
      }
    },
    dashboard_features: {
      spa_tracking: true,
      outbound_links: true,
      error_tracking: false,
      session_replay: false,
      web_vitals: false
    },
    user_identification: {
      enabled: false,
      identifier_type: 'user_id',
      identify_on: 'login',
      clear_on_logout: true
    },
    exclusions: []
  })

  const loading = ref(false)
  const error = ref(null)

  // Actions
  async function fetchSettings() {
    loading.value = true
    error.value = null

    try {
      const data = await api.getSettings()
      settings.value = data
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function updateSettings(newSettings) {
    loading.value = true
    error.value = null

    try {
      const data = await api.updateSettings(newSettings)
      settings.value = data.settings
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  async function testConnection(siteId) {
    loading.value = true
    error.value = null

    try {
      const data = await api.testConnection(siteId)
      return data
    } catch (err) {
      error.value = err.message
      throw err
    } finally {
      loading.value = false
    }
  }

  function updateSetting(key, value) {
    // Support dot notation for nested keys
    if (key.includes('.')) {
      const keys = key.split('.')
      let obj = settings.value

      for (let i = 0; i < keys.length - 1; i++) {
        if (!obj[keys[i]]) {
          obj[keys[i]] = {}
        }
        obj = obj[keys[i]]
      }

      obj[keys[keys.length - 1]] = value
    } else {
      settings.value[key] = value
    }
  }

  return {
    settings,
    loading,
    error,
    fetchSettings,
    updateSettings,
    testConnection,
    updateSetting
  }
})
