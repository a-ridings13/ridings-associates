import moment from 'moment'
import Vue from 'vue'

export default {
  namespaced: true,
  state: () => {
    return {
      access_token: '',
      expires: 0,
      expires_in: 0,
      refresh_token: '',
      token_type: 'Bearer'
    }
  },
  getters: {
    isLoggedIn(state) {
      if (state.expires === 0) {
        return false
      }

      return (moment(state.expires).diff(moment().unix()) > 0)
    },
    accessToken(state) {
      return state.access_token
    }
  },
  mutations: {
    setAccessToken: (state, tokenPayload) => {
      Vue.set(state, 'access_token', tokenPayload.access_token)
      Vue.set(state, 'expires', moment().add(tokenPayload.expires_in, 'seconds').unix())
      Vue.set(state, 'expires_in', tokenPayload.expires_in)
      Vue.set(state, 'refresh_token', tokenPayload.refresh_token)
      Vue.set(state, 'token_type', tokenPayload.token_type)
    },
    setSession: (state, session) => {
      Vue.set(state, 'access_token', session.access_token)
      Vue.set(state, 'expires', session.expires)
      Vue.set(state, 'expires_in', session.expires_in)
      Vue.set(state, 'refresh_token', session.refresh_token)
      Vue.set(state, 'token_type', session.token_type)
    }
  },
  actions: {
    logout({ commit }) {
      commit('setSession', { access_token: '',
        expires: 0,
        expires_in: 0,
        refresh_token: '',
        token_type: 'Bearer'
      })
    }
  }
}
