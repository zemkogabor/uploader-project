<template>
  <dashboard
    :uppy="uppy"
    :props="{
      proudlyDisplayPoweredByUppy: false,
      disableThumbnailGenerator: true,
      showProgressDetails: true,
    }"
  />
</template>

<script>
import { Dashboard } from '@uppy/vue'
import Tus from '@uppy/tus'
import Uppy from '@uppy/core'

import '@uppy/core/dist/style.css'
import '@uppy/dashboard/dist/style.css'
import Token from '@/services/token.js'

const uppy = new Uppy().use(Tus, {
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

export default {
  name: 'Upload',
  components: {
    Dashboard,
  },
  data() {
    return {
      uppy: uppy,
    }
  },
}
</script>
