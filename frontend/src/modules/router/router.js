import { createWebHistory, createRouter } from 'vue-router'
import authRouteList from '@/modules/router/auth/routeList.js'
import pageName from '@/constants/pageName.js'
import Layout from '@/components/layout/Layout.vue'
import PageNotFound from '@/pages/page-not-found/PageNotFound.vue'
import Home from '@/pages/home/Home.vue'

const routes = [
  {
    component: Layout,
    meta: {
      auth: true,
    },
    redirect: {
      name: pageName.HOME,
    },
    children: [
      {
        path: '/',
        name: pageName.HOME,
        component: Home,
      },
    ],
  },
  ...authRouteList,
  {
    path: '/:pathMatch(.*)*',
    name: pageName.PAGE_NOT_FOUND,
    component: PageNotFound,
  },
]

const createCustomRouter = store => {
  const router = createRouter({
    history: createWebHistory(),
    routes,
  })

  router.beforeEach(async (to) => {
    await store.dispatch('Auth/loadUser')
    const user = store.getters['Auth/getUser']
    const userLoggedIn = user !== null

    // User logged in and want to access "guest" route.
    if (userLoggedIn && to.meta !== undefined && to.meta.guest) {
      return {
        name: pageName.HOME,
      }
    }

    // User not logged in and want to access "auth" route.
    if (!userLoggedIn && to.meta !== undefined && to.meta.auth) {
      return {
        name: pageName.LOGIN,
        query: {
          redirect: to.fullPath,
        },
      }
    }

    // User logged in and want to access route with permission.
    if (userLoggedIn && to.meta !== undefined && to.meta.permission !== undefined &&
      !store.getters['Auth/hasPermission'](to.meta.permission)) {
      return {
        name: pageName.HOME,
      }
    }

    return true
  })

  return router
}

export default store => createCustomRouter(store)
