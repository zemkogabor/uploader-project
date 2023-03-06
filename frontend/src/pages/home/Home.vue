<template>
  {{ user }}
</template>

<script>
import { mapGetters } from 'vuex'

export default {
  name: 'Home',
  computed: {
    ...mapGetters({
      user: 'Auth/getUser',
    }),
  },
}
</script>
