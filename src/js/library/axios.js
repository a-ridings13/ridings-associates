import axios from "axios"
import store from '../store'

axios.ignoreErrors = false

axios.interceptors.request.use(function (config) {
  if (store.getters['session/isLoggedIn']) {
    config.headers.Authorization = 'Bearer ' + store.getters['session/accessToken']
  }
  return config
})

axios.interceptors.response.use(function (response) {
  return response
}, function (error) {

  if (axios.ignoreErrors) {
    return Promise.reject(error)
  }

  if (
    typeof error.response.data.payload !== 'undefined' &&
    typeof error.response.data.payload.error !== 'undefined') {
    return Promise.reject(error)
  }

  if (typeof error.response.data.error !== 'undefined') {
    return Promise.reject(error)
  }

  return Promise.reject(error)
})

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'

export default axios
