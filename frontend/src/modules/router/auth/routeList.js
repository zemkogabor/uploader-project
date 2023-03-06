import Login from '@/pages/auth/Login.vue'
import pageName from '@/constants/pageName.js'

export default [
  {
    path: '/login',
    name: pageName.LOGIN,
    component: Login,
    meta: {
      guest: true,
    },
  },
]
