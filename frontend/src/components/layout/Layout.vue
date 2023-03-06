<template>
  <div
    v-if="user === null"
    class="d-flex justify-content-center align-items-center vh-100"
  >
    <div
      class="spinner-border text-primary"
      role="status"
    />
  </div>
  <template v-else>
    <admin-layout
      :brand-name="$t('generic.brand')"
      :left-items-by-groups="leftItemsByGroups"
      :top-items="topItems"
    >
      <router-view />
    </admin-layout>
  </template>
</template>

<script>
import { mapActions, mapGetters } from 'vuex'
import AdminLayout from 'admin-layout-vue'
import pageName from '@/constants/pageName.js'

export default {
  name: 'Home',
  components: {
    AdminLayout,
  },
  data() {
    return {
      /**
       * Sidebar menu items
       */
      leftItemsByGroups: [
        {
          items: [
            {
              label: this.$t('home.title'),
              iconClass: 'bi bi-house-door-fill',
              route: {
                name: pageName.HOME,
              },
            },
          ],
        },
      ],
    }
  },
  computed: {
    ...mapGetters({
      user: 'Auth/getUser',
    }),
    /**
     * Top navbar items
     */
    topItems() {
      return [
        {
          iconClass: 'bi bi-person-circle',
          subItems: [
            {
              label: this.$t('logout.submit'),
              callable: this.onLogoutClick,
            },
          ],
          showBadge: false,
        },
      ]
    },
  },
  methods: {
    ...mapActions({
      logout: 'Auth/logout',
    }),
    /**
     * Logout user.
     *
     * @returns {*}
     */
    onLogoutClick() {
      return this.logout().then(() => {
        this.$router.push({
          name: pageName.LOGIN,
        })
      })
    },
  },
}
</script>
