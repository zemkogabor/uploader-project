<template>
  <div>
    <div class="table-responsive">
      <bs-table
        class="table-hover table-nowrap"
        :fields="fields"
        :items="items"
        :order-by="orderBy"
        :is-loading="!isLoaded"
        @order-changed="onOrderChanged"
      >
        <template #td="data">
          <div v-if="data.field === 'actions'">
            <i class="bi bi-download download-icon" @click="onDownloadClicked(items[data.key].uuid)" />
          </div>
          <template v-else>
            {{ data.item }}
          </template>
        </template>
      </bs-table>
    </div>
    <bs-paginator
      v-if="totalCount > pageSize"
      :current-page="currentPage"
      :page-size="pageSize"
      :total-count="totalCount"
      @page-changed="onPageChanged"
    />
  </div>
</template>

<script>
import { BsTable, BsPaginator } from 'bootstrap-vue-wrapper'
import { mapActions, mapGetters } from 'vuex'
import env from '@/vite/env.js'
import Token from '@/services/token.js'

export default {
  name: 'FileList',
  components: {
    BsTable,
    BsPaginator,
  },
  data() {
    return {
      /**
       * List fields
       */
      fields: [
        {
          key: 'name',
          label: this.$t('generic.name'),
        },
        {
          key: 'createdAt',
          label: this.$t('generic.created_at'),
        },
        {
          key: 'actions',
          label: null,
          sort: false,
        },
      ],
    }
  },
  computed: {
    ...mapGetters({
      items: 'FileList/getItems',
      isLoaded: 'FileList/isLoaded',
      orderBy: 'FileList/getOrderBy',
      currentPage: 'FileList/getCurrentPage',
      pageSize: 'FileList/getPageSize',
      totalCount: 'FileList/getTotalCount',
    }),
  },
  mounted() {
    this.load()
  },
  unmounted() {
    this.resetLoadedItems()
    this.resetUserInputs()
  },
  methods: {
    ...mapActions({
      load: 'FileList/load',
      resetLoadedItems: 'FileList/resetLoadedItems',
      resetUserInputs: 'FileList/resetUserInputs',
      onOrderChanged: 'FileList/onOrderChanged',
      onPageChanged: 'FileList/onPageChanged',
    }),
    /**
     * @param uuid
     */
    onDownloadClicked(uuid) {
      window.open(env.BACKEND_URL + '/file/download/' + uuid + '?accessToken=' + Token.getAccessToken())
    },
  },
}
</script>

<style lang="scss" scoped>
.download-icon {
  cursor: pointer;
}
</style>
