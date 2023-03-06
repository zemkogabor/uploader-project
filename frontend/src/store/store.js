import { createStore } from 'vuex'
import Auth from './modules/auth/auth.js'

export default createStore({
  modules: {
    Auth: Auth,
  },
})
