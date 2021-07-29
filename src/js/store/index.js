import session from './session'
import Vue from 'vue'
import Vuex from 'vuex'
import VuexPersistence from 'vuex-persist'

const vuexLocal = new VuexPersistence({
  storage: window.localStorage
})

Vue.use(Vuex)

const index = new Vuex.Store({
  strict: true,
  modules: {
    session
  },
  plugins: [vuexLocal.plugin]
})

export default index
