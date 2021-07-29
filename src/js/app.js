import store from './store/index'
import Vue from 'vue'

const Index = () => import(/* webpackChunkName: "/Pages/Index" */ './Pages/Index.vue')
const Login = () => import(/* webpackChunkName: "/Pages/Login" */ './Pages/Login.vue')

new Vue({
  el: '#page-wrapper',
  store,
  components: {
    Index,
    Login
  }
})
