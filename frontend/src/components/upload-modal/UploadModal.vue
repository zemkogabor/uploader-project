<template>
  <bs-modal
    ref="modalUpload"
    title="Upload"
    class="modal-lg"
    :hide-footer="true"
    @hidden="$emit('hidden')"
  >
    <template #body>
      <dashboard
        :uppy="uppy"
        :props="{
          width: '100%',
          proudlyDisplayPoweredByUppy: false,
          disableThumbnailGenerator: true,
          showProgressDetails: true,
          doneButtonHandler: doneButtonHandler,
        }"
      />
    </template>
  </bs-modal>
</template>

<script>
import { BsModal } from 'bootstrap-vue-wrapper'
import { Dashboard } from '@uppy/vue'
import Tus from '@uppy/tus'
import Uppy from '@uppy/core'
import Token from '@/services/token.js'

export default {
  name: 'UploadModal',
  components: {
    Dashboard,
    BsModal,
  },
  emits: [
    'hidden',
  ],
  data() {
    return {
      uppy: null,
    }
  },
  created() {
    this.uppy = new Uppy().use(Tus, {
      endpoint: 'http://file.127.0.0.1.nip.io/files/',
      onBeforeRequest(req) {
        // It is important to set the latest access token before each request, because it can change from time to time.
        const token = Token.getAccessToken()
        req.setHeader('Authorization', `Bearer ${token}`)
      },
      // I want it to be created again if I upload the same file again.
      // https://sme-uploader.web.app/docs/tus/#removeFingerprintOnSuccess-false
      removeFingerprintOnSuccess: true,
      limit: 20,
    })
  },
  beforeUnmount() {
    this.uppy.cancelAll()
  },
  methods: {
    /**
     * Hide modal
     */
    hideModal() {
      this.$refs.modalUpload.hide()
    },
    doneButtonHandler() {
      this.hideModal()
    },
  },
}
</script>

<style lang="scss">
@import '~@uppy/core/dist/style.css';
@import '~@uppy/dashboard/dist/style.css';

.uppy-Root {
  // Unset uppy font family to use the default.
  font-family: unset;
}
</style>
