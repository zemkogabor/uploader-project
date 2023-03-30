<template>
  <div class="d-flex justify-content-between mb-3">
    <h3 v-text="'Uploads'" />
    <button
      class="btn btn-success"
      @click="onUploadClicked"
      v-text="'New Upload'"
    />
  </div>
  <file-list />
  <upload-modal v-if="uploadModalShown" @hidden="onUploadModalHidden" @complete="onComplete" />
</template>

<script>
import UploadModal from '@/components/upload-modal/UploadModal.vue'
import FileList from '@/components/file-list/FileList.vue'
import { mapActions } from 'vuex'

export default {
  name: 'Home',
  components: {
    FileList,
    UploadModal,
  },
  data() {
    return {
      uploadModalShown: false,
    }
  },
  methods: {
    ...mapActions({
      loadFiles: 'FileList/load',
    }),
    onUploadClicked() {
      this.uploadModalShown = true
    },
    onUploadModalHidden() {
      this.uploadModalShown = false
    },
    onComplete() {
      this.loadFiles()
    },
  },
}
</script>
