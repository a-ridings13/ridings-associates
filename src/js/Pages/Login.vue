<template>
  <div>
    <div v-if="failed === '1'">
      Login Failed
      <a href="/">Try again</a>
    </div>
    <div v-else>
      <h1>Login</h1>
      <form
        action="/api/oauth/authorize"
        method="post"
      >
        <div>
          <label for="username">Username</label>
          <input
            id="username"
            v-model="username"
            name="username"
          >
        </div>
        <div>
          <label for="password">Password</label>
          <input
            id="password"
            v-model="password"
            name="password"
            type="password"
          >
        </div>
        <div>
          <button type="submit">
            Submit
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
<script>
import { mapMutations } from 'vuex'
export default {
  props: {
    failed: {
      type: String,
      default: ''
    },
    auth: {
      type: Object,
      default: () => {
        return {}
      }
    }
  },
  data: () => {
    return {
      username: '',
      password: ''
    }
  },
  created() {
    if (typeof this.auth.access_token !== 'undefined') {
      this.setAccessToken(this.auth)
      window.location.href = '/'
    }
  },
  methods: {
    ...mapMutations({
      setAccessToken: 'session/setAccessToken'
    })
  }
}
</script>
