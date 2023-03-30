<template>
  <div class="d-flex justify-content-between">
    <h3 v-text="'Uploads'" />
    <button
      class="btn btn-success"
      @click="onUploadClicked"
      v-text="'New Upload'"
    />
  </div>
  <upload-modal v-if="uploadModalShown" @hidden="onUploadModalHidden" />
</template>

<script>
import UploadModal from '@/components/upload-modal/UploadModal.vue'

export default {
  name: 'Home',
  components: {
    UploadModal,
  },
  data() {
    return {
      uploadModalShown: false,
    }
  },
  methods: {
    onUploadClicked() {
      this.uploadModalShown = true
    },
    onUploadModalHidden() {
      this.uploadModalShown = false
    },
  },
}
</script>
