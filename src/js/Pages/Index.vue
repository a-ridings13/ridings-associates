<template>
  <div>
    <HelloWorld />
    <a
      v-if="isLoggedIn === false"
      :href="loginUri"
    >Login</a>
    <a
      v-else
      href="#logout"
      @click="logout"
    >Logout</a>

    <div v-if="isLoggedIn">
      {{ user.id }}
      {{ user.username }}
      {{ user.email }}
    </div>
  </div>
</template>
<script>
/* global process */
import { mapActions, mapGetters } from 'vuex'
import axios from '../library/axios'

const HelloWorld = () => import(/* webpackChunkName: "/Components/HelloWorld" */ '../Components/HelloWorld.vue')

export default {
  components: { HelloWorld },
  data() {
    return {
      user: ''
    }
  },
  computed: {
    ...mapGetters({
      isLoggedIn: 'session/isLoggedIn'
    }),
    loginUri() {
      return `/api/oauth/authorize?response_type=code&client_id=${process.env.CLIENT_ID}&scope=&redirect_uri=http://vagrant.local/api/oauth/authorize`
    }
  },
  mounted() {
    if (this.isLoggedIn) {
      axios.get('/api/client')
        .then(r => {
          this.user = r.data.payload
        })
    }
  },
  methods: {
    ...mapActions({
      'doLogout': 'session/logout'
    }),
    logout() {
      this.doLogout().then(() => {
        window.location.href = '/'
      })
    }
  }
}
</script>
