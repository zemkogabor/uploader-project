import Axios from 'axios'
import Token from '@/services/token.js'

export default store => {
  // Request interceptor for API calls
  Axios.interceptors.request.use(
    function (config) {
      config.headers.Authorization = 'Bearer ' + Token.getAccessToken()

      // If that access token renewal is in progress, we block request for that time.
      if (store.getters['Auth/isAccessTokenRenewalInProgress']) {
        return new Promise(function (resolve) {
          const check = function () {
            // Renewal is complete, unblock request and overwrite Auth header.
            if (!store.getters['Auth/isAccessTokenRenewalInProgress']) {
              config.headers.Authorization = 'Bearer ' + Token.getAccessToken()
              resolve(config)
            } else {
              setTimeout(check, 50)
            }
          }
          check()
        })
      } else {
        return config
      }
    },
    function (error) {
      return Promise.reject(error)
    },
  )
}
