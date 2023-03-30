import BaseList from '@/store/list-base/list-base.js'
import env from '@/vite/env.js'

export default {
  namespaced: true,
  state: {
    ...BaseList.state,
    requestUrl: env.BACKEND_URL + '/file/list',
    orderBy: 'createdAt',
    sortDesc: true,
  },
  mutations: {
    ...BaseList.mutations,
  },
  actions: {
    ...BaseList.actions,
    /**
     * Reset user inputs
     *
     * @param state
     * @param commit
     */
    resetUserInputs({ state, commit }) {
      state.orderBy = 'createdAt'
      state.sortDesc = true
      state.currentPage = 1
    },
  },
  getters: {
    ...BaseList.getters,
  },
}
