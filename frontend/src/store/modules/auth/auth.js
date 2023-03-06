import Axios from 'axios'
import Token from '@/services/token.js'
import env from '@/vite/env.js'

const state = {
  user: null,
  loadingLogin: false,
  loadingLogout: false,
  renewAccessTokenTimeoutId: null,
  accessTokenRenewalInProgress: false,
}

const getters = {
  /**
   * @param state
   * @return {*}
   */
  getUser: state => {
    return state.user
  },
  /**
   * @param state
   * @return {*}
   */
  isLoginLoading: state => {
    return state.loadingLogin
  },
  /**
   * @param state
   * @return {*}
   */
  isLogoutLoading: state => {
    return state.loadingLogout
  },
  /**
   * @param state
   * @return {*}
   */
  isAccessTokenRenewalInProgress: state => {
    return state.accessTokenRenewalInProgress
  },
}

const mutations = {
  /**
   * @param state
   * @param payload
   */
  setUser(state, payload) {
    state.user = payload
  },
  /**
   * @param state
   * @param payload
   */
  setLoadingLogin(state, payload) {
    state.loadingLogin = payload
  },
  /**
   * @param state
   * @param payload
   */
  setLoadingLogout(state, payload) {
    state.loadingLogout = payload
  },
  /**
   * @param state
   * @param payload
   */
  setRenewAccessTokenTimeoutId(state, payload) {
    state.renewAccessTokenTimeoutId = payload
  },
  /**
   * @param state
   * @param payload
   */
  setAccessTokenRenewalInProgress(state, payload) {
    state.accessTokenRenewalInProgress = payload
  },
}

const actions = {
  /**
   * Load active user.
   *
   * @param commit
   * @param state
   * @param dispatch
   * @param retry
   * @returns {Promise<void>|*}
   */
  loadUser({ commit, state, dispatch }, retry = false) {
    if (state.user !== null) {
      return Promise.resolve()
    }

    return Axios.get('http://oauth.127.0.0.1.nip.io/user').then(response => {
      commit('setUser', response.data)
    }).catch(async error => {
      if (retry) {
        // Not a normal case.
        console.error('The api call failed even after retrying.', error)
        return Promise.resolve()
      }

      if (error.response !== undefined && error.response.status === 401) {
        // Normal case: means that the access token expired.
        try {
          await dispatch('renewAccessToken').then(() => {
            // The accessToken has been updated, now retry load the user.
            dispatch('loadUser', true)
          })

          return Promise.resolve()
        } catch (renewError) {
          if (renewError.response !== undefined && renewError.response.status === 401) {
            // Normal case: means that the refresh token has also expired.
            return Promise.resolve()
          }

          console.error(renewError.message)
        }
      }

      console.error(error.message)
    })
  },

  /**
   * @param commit
   * @param dispatch
   * @param email
   * @param password
   * @returns {Promise<unknown>}
   */
  login({ commit, dispatch }, { email, password }) {
    commit('setLoadingLogin', true)

    return Axios.post(env.AUTH_ACCESS_TOKEN_URL, {
      grant_type: 'password',
      client_id: env.AUTH_CLIENT_ID,
      client_secret: env.AUTH_CLIENT_SECRET,
      scope: 'email basic name',
      username: email,
      password: password,
    }).then(async response => {
      Token.updateTokens(response.data.access_token, response.data.expires_in, response.data.refresh_token)
      // After login, the access token renewal must be scheduled.
      dispatch('scheduleRenewAccessToken')
      dispatch('loadUser')
    }).finally(() => {
      commit('setLoadingLogin', false)
    })
  },
  /**
   * @param state
   * @param dispatch
   * @param commit
   * @returns {Promise<void>}
   */
  scheduleRenewAccessToken({ state, dispatch, commit }) {
    clearInterval(state.renewAccessTokenTimeoutId)

    if (Token.isAccessTokenExpired()) {
      // Access token already expired. (normal case)
      return Promise.resolve()
    }

    // Token renewal will be scheduled based on accessToken expiration.
    const renewAccessTokenTimeoutId = setTimeout(() => {
      dispatch('renewAccessToken').catch(error => {
        if (error.response !== undefined && error.response.status === 401) {
          // Normal case: means that the refresh token has expired.
          return Promise.resolve()
        }

        console.error(error)
      })
    }, Token.getAccessTokenExpireAt().getTime() - (new Date()).getTime())

    commit('setRenewAccessTokenTimeoutId', renewAccessTokenTimeoutId)
  },
  /**
   * @param commit
   * @param dispatch
   * @returns {Promise<Axios.AxiosResponse<any>>}
   */
  renewAccessToken({ commit, dispatch }) {
    const refreshToken = Token.getRefreshToken()
    if (refreshToken === null) {
      // Normal case
      return Promise.resolve()
    }

    commit('setAccessTokenRenewalInProgress', true)

    // New axios instance to disable interceptors for this request. (e.g.: request blocking interceptor)
    const notInterceptedAxiosInstance = Axios.create()
    return notInterceptedAxiosInstance.post(env.AUTH_ACCESS_TOKEN_URL, {
      grant_type: 'refresh_token',
      client_id: env.AUTH_CLIENT_ID,
      client_secret: env.AUTH_CLIENT_SECRET,
      refresh_token: refreshToken,
    }).then(response => {
      Token.updateTokens(response.data.access_token, response.data.expires_in, response.data.refresh_token)
      // After renewal, the next access token renewal must be scheduled.
      dispatch('scheduleRenewAccessToken')
    }).finally(() => {
      commit('setAccessTokenRenewalInProgress', false)
    })
  },
  /**
   * * @param commit
   * @returns {Promise<unknown>}
   */
  logout({ commit }) {
    commit('setLoadingLogout', true)

    return Axios.post('/user/logout').then(() => {
      commit('setUser', null)
    }).catch(error => {
      console.error(error)
    }).finally(() => {
      commit('setLoadingLogout', false)
    })
  },
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
}
