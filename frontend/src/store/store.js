import { createStore } from 'vuex'
import Auth from './modules/auth/auth.js'
import FileList from '@/store/modules/file-list/file-list.js'

export default createStore({
  modules: {
    Auth: Auth,
    FileList: FileList,
  },
})
