module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'eslint:recommended',
    'plugin:vue/vue3-essential', // or vue2 if using Vue 2
    'plugin:prettier/recommended'
  ],
  rules: {
    // Customize or add your rules here
    'vue/multi-word-component-names': 'off', // Disable the multi-word component rule
  }
}
