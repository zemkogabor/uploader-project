<template>
  <router-view />
  <alerts :alerts="alerts" />
</template>

<script>
import Alerts from '@/components/alerts/Alerts.vue'

export default {
  name: 'App',
  components: {
    Alerts,
  },
  data() {
    return {
      /**
       * Alert items
       */
      alerts: [],
    }
  },
  mounted() {
    this.$emitter.on('notify', this.onNotify)
  },
  methods: {
    /**
     * Notify event
     *
     * @param data
     */
    onNotify(data) {
      this.alerts.push(data)
    },
  },
}
</script>
