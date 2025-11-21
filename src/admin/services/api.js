import axios from 'axios'

// Get WordPress REST API configuration from global object
const wpData = window.rybbitAdmin || {}
const restUrl = wpData.restUrl || '/wp-json/'
const nonce = wpData.restNonce || ''

// Create axios instance with WordPress REST API config
const apiClient = axios.create({
  baseURL: `${restUrl}rybbit/v1`,
  headers: {
    'Content-Type': 'application/json',
    'X-WP-Nonce': nonce
  }
})

// Response interceptor for error handling
apiClient.interceptors.response.use(
  response => response.data,
  error => {
    const message = error.response?.data?.message || error.message || 'An error occurred'
    return Promise.reject(new Error(message))
  }
)

// API methods
const api = {
  /**
   * Get plugin settings
   * @returns {Promise<Object>}
   */
  async getSettings() {
    return apiClient.get('/settings')
  },

  /**
   * Update plugin settings
   * @param {Object} settings - Settings object
   * @returns {Promise<Object>}
   */
  async updateSettings(settings) {
    return apiClient.post('/settings', settings)
  },

  /**
   * Test connection to Rybbit
   * @param {string} siteId - Site ID to test
   * @returns {Promise<Object>}
   */
  async testConnection(siteId) {
    return apiClient.post('/test-connection', { site_id: siteId })
  },

  /**
   * Get posts for exclusion selector
   * @param {Object} params - Query parameters
   * @returns {Promise<Array>}
   */
  async getPosts(params = {}) {
    return apiClient.get('/posts', { params })
  },

  /**
   * Get sample WooCommerce events
   * @returns {Promise<Object>}
   */
  async getWooCommerceSampleEvents() {
    return apiClient.get('/woocommerce/sample-events')
  }
}

export default api
