import Axios from 'axios'

export default {
  namespaced: true,
  state: {
    /**
     * Request url
     */
    requestUrl: null,
    /**
     * List of items
     */
    items: [],
    /**
     * List is loaded
     */
    loaded: false,
    /**
     * Total count of items
     */
    totalCount: 0,
    /**
     * Pag size
     */
    pageSize: 30,
    /**
     * Actual page number
     */
    currentPage: 1,
    /**
     * Order by field name
     */
    orderBy: null,
    /**
     * Sort is desc (inverted)
     */
    sortDesc: false,
  },
  mutations: {
    /**
     * Set current page
     *
     * @param state
     * @param payload
     */
    setCurrentPage(state, payload) {
      state.currentPage = payload
    },
    /**
     * Set order by field
     *
     * @param state
     * @param payload
     */
    setOrderBy(state, payload) {
      state.orderBy = payload
    },
    /**
     * Set sort desc
     *
     * @param state
     * @param payload
     */
    setSortDesc(state, payload) {
      state.sortDesc = payload
    },
  },
  actions: {
    /**
     * Load items
     *
     * @param state
     * @param commit
     * @param getters
     * @return {Promise<unknown>}
     */
    load({ state, commit, getters }) {
      return Axios.get(state.requestUrl, {
        params: {
          currentPage: state.currentPage,
          pageSize: state.pageSize,
          orderBy: state.orderBy,
          sortDesc: state.sortDesc ? 1 : 0,
          ...getters.getExtraRequestParams,
        },
      })
        .then(({ data }) => {
          state.items = data.items
          state.totalCount = data.totalCount
        })
        .catch((e) => {
          state.items = []
          state.totalCount = 0

          console.error(e)
        })
        .finally(() => {
          state.loaded = true
        })
    },
    /**
     * Reset user inputs
     *
     * @param state
     * @param commit
     */
    resetUserInputs({ state, commit }) {
      state.orderBy = null
      state.sortDesc = false
      state.currentPage = 1
    },
    /**
     * Reset loaded items
     *
     * @param state
     * @param commit
     */
    resetLoadedItems({ state, commit }) {
      state.loaded = false
      state.items = []
    },
    /**
     * Order changed
     *
     * @param dispatch
     * @param commit
     * @param sortDesc
     * @param orderBy
     */
    onOrderChanged({ dispatch, commit }, { sortDesc, orderBy }) {
      commit('setSortDesc', sortDesc)
      commit('setOrderBy', orderBy)
      commit('setCurrentPage', 1)
      dispatch('resetLoadedItems')
      dispatch('load')
    },
    /**
     * Page changed
     *
     * @param dispatch
     * @param commit
     * @param data
     */
    onPageChanged({ dispatch, commit }, data) {
      commit('setCurrentPage', data)
      dispatch('resetLoadedItems')
      dispatch('load')
    },
  },
  getters: {
    /**
     * Extra request params
     *
     * @return {{}}
     */
    getExtraRequestParams() {
      return {}
    },
    /**
     * Item list
     *
     * @param state
     * @returns Array
     */
    getItems: state => {
      return state.items
    },
    /**
     * Items is loaded
     *
     * @param state
     * @returns {boolean}
     */
    isLoaded: state => {
      return state.loaded
    },
    /**
     * Total count of items
     *
     * @param state
     * @returns {number}
     */
    getTotalCount: state => {
      return state.totalCount
    },
    /**
     * Page size
     *
     * @param state
     * @returns {number}
     */
    getPageSize: state => {
      return state.pageSize
    },
    /**
     * Current page
     *
     * @param state
     * @returns {number}
     */
    getCurrentPage: state => {
      return state.currentPage
    },
    /**
     * Order by field name
     *
     * @param state
     * @returns {null}
     */
    getOrderBy: state => {
      return state.orderBy
    },
  },
}
