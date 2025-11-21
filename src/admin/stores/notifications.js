import { defineStore } from 'pinia'
import { ref } from 'vue'

export const useNotificationsStore = defineStore('notifications', () => {
  // State
  const notifications = ref([])
  let nextId = 1

  // Actions
  function addNotification({ type = 'info', message, duration = 5000 }) {
    const id = nextId++
    const notification = {
      id,
      type,
      message,
      visible: true
    }

    notifications.value.push(notification)

    if (duration > 0) {
      setTimeout(() => {
        removeNotification(id)
      }, duration)
    }

    return id
  }

  function removeNotification(id) {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
  }

  function success(message, duration) {
    return addNotification({ type: 'success', message, duration })
  }

  function error(message, duration) {
    return addNotification({ type: 'error', message, duration })
  }

  function warning(message, duration) {
    return addNotification({ type: 'warning', message, duration })
  }

  function info(message, duration) {
    return addNotification({ type: 'info', message, duration })
  }

  function clear() {
    notifications.value = []
  }

  return {
    notifications,
    addNotification,
    removeNotification,
    success,
    error,
    warning,
    info,
    clear
  }
})
