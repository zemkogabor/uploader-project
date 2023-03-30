<template>
  <upload />
</template>

<script>
import { mapGetters } from 'vuex'
import Upload from '@/components/upload2/Upload.vue'

export default {
  name: 'Home',
  components: {
    Upload,
  },
  computed: {
    ...mapGetters({
      user: 'Auth/getUser',
    }),
  },
}
</script>
