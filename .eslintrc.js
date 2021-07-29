module.exports = {
  "parserOptions": {
    "parser": "babel-eslint",
    "ecmaVersion": 2017,
    "sourceType": "module"
  },
  "env": {
    "browser": true,
    "es6": true
  },
  "extends": [
    "eslint:recommended",
    "plugin:vue/essential",
    "plugin:vue/strongly-recommended",
    "plugin:vue/recommended"
  ],
  "globals": {
    "Atomics": "readonly",
    "SharedArrayBuffer": "readonly"
  },
  "plugins": [
    "vue"
  ],
  "rules": {
    "no-console": "warn",
    "semi": ["error", "never"],
    "no-trailing-spaces" : "error",
    "no-var": "error",
    "object-curly-spacing": ["error", "always"],
    "curly": "error",
    "eqeqeq": "error",
    "no-multi-spaces": "error",
    "arrow-spacing": "error",
    "no-duplicate-imports": "error",
    "vue/no-v-html": 0,
    "indent": ["error", 2],
    "sort-imports": ["error", {ignoreCase: true}]
  }
}
