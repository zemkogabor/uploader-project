<template>
  <div class="container d-flex justify-content-center p-3">
    <div class="card">
      <div class="card-header h3">
        {{ $t('login.title') }}
      </div>
      <bs-form @submit="onSubmit">
        <div class="card-body">
          <div class="mb-3">
            <bs-input
              id="emailInput"
              v-model="email"
              type="email"
              :label="$t('generic.email')"
              required
            />
          </div>
          <div class="mb-3">
            <bs-input
              id="passwordInput"
              v-model="password"
              type="password"
              :label="$t('generic.password')"
              required
            />
          </div>
          <div class="text-center">
            <button
              type="submit"
              class="btn btn-primary"
              :disabled="isLoading"
            >
              {{ $t('login.submit') }}
              <span
                v-if="isLoading"
                class="spinner-border spinner-border-sm"
                role="status"
              />
            </button>
          </div>
        </div>
      </bs-form>
    </div>
  </div>
</template>

<script>
import pageName from '@/constants/pageName.js'
import { mapActions, mapGetters } from 'vuex'
import { BsForm, BsInput } from 'bootstrap-vue-wrapper'
import { useApiErrorHandler } from '@/components/api-error-handler/ApiErrorHandler.js'

export default {
  name: 'Login',
  components: {
    BsForm,
    BsInput,
  },
  setup() {
    return useApiErrorHandler()
  },
  data() {
    return {
      /**
       * User email.
       */
      email: null,
      /**
       * User password.
       */
      password: null,
      /**
       * Page names.
       */
      pageName,
    }
  },
  computed: {
    ...mapGetters({
      isLoading: 'Auth/isLoginLoading',
    }),
  },
  methods: {
    ...mapActions({
      login: 'Auth/login',
    }),
    /**
     * Login user.
     *
     * @param event
     * @returns {*}
     */
    onSubmit(event) {
      if (!event.target.checkValidity()) {
        return
      }

      return this.login({
        email: this.email,
        password: this.password,
      }).then(() => {
        if (this.$route.query.redirect !== undefined) {
          // Push full path (path + query)
          this.$router.push(this.$route.query.redirect)
        } else {
          this.$router.push({
            name: pageName.HOME,
          })
        }
      }).catch((error) => {
        this.apiErrorHandler(error)
      })
    },
  },
}
</script>

<style lang="scss" scoped>
.card {
  max-width: 18rem;
}
</style>
