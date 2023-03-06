import pageName from '@/constants/pageName.js'

export function useApiErrorHandler() {
  /**
   * Api error handler
   */
  function apiErrorHandler(error) {
    // Notify user on bad request.
    if (error.response !== undefined && error.response.status === 400 && error.response.data.message !== undefined) {
      this.$emitter.emit('notify', {
        message: error.response.data.message,
        type: 'error',
      })
    } else if (error.response !== undefined && error.response.status === 404) {
      this.$router.replace({ name: pageName.PAGE_NOT_FOUND })
    } else {
      this.$emitter.emit('notify', {
        message: this.$t('generic.api_error'),
        type: 'error',
      })
      console.error(error)
    }
  }

  return {
    apiErrorHandler,
  }
}
