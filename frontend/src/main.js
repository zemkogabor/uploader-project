import { createApp } from 'vue'
import App from './App.vue'
import createRouter from './modules/router/router.js'
import './assets/scss/main.scss'
import 'bootstrap'
import store from './store/store.js'
import axiosAuthConfig from '@/config/axiosAuthConfig.js'
import { createI18n } from 'vue-i18n'
import messages from './messages/index.js'
import { createEmitter } from '@/modules/emitter/emitter.js'

const app = createApp(App)

app.use(createRouter(store))
app.use(store)
app.use(createI18n({
  locale: 'en',
  fallbackLocale: 'en',
  messages,
}))
app.use(createEmitter())

store.dispatch('Auth/scheduleRenewAccessToken')

axiosAuthConfig(store)

app.mount('#app')
