<template>
  <div class="position-fixed bottom-0 end-0 p-3 toast-container">
    <bs-toast
      v-for="(notification, index) in alerts"
      :key="index"
      :class="getToastClass(notification.type)"
    >
      <div class="d-flex">
        <div class="toast-body" v-text="notification.message" />
        <button
          type="button"
          class="btn-close me-2 m-auto"
          :class="getToastCloseBtnClass(notification.type)"
          data-bs-dismiss="toast"
          :aria-label="$t('generic.close')"
        />
      </div>
    </bs-toast>
  </div>
</template>

<script>
import { BsToast } from 'bootstrap-vue-wrapper'

export default {
  name: 'Alerts',
  components: {
    BsToast,
  },
  props: {
    /**
     * Alert items
     */
    alerts: {
      type: Array,
      default: () => {},
    },
  },
  methods: {
    /**
     * Toast css class
     *
     * @param type
     * @returns {string}
     */
    getToastClass(type) {
      switch (type) {
        case 'info':
          return 'bg-primary text-white'
        case 'warning':
          return 'bg-warning'
        case 'error':
          return 'bg-danger text-white'
        default:
          console.warn('Not supported notification type: "' + type + '"')
      }
    },
    /**
     * Toast close button css class
     *
     * @param type
     * @returns {string}
     */
    getToastCloseBtnClass(type) {
      switch (type) {
        case 'info':
          return 'btn-close-white'
        case 'warning':
          return ''
        case 'error':
          return 'btn-close-white'
        default:
          console.warn('Not supported notification type: "' + type + '"')
      }
    },
  },
}
</script>
