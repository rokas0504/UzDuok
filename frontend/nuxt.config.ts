// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  modules: [
    '@nuxt/eslint',
    '@nuxtjs/i18n',
    'nuxt-auth-sanctum',
    'nuxt-pages-plus',
    '@nuxtjs/tailwindcss',
    '@nuxtjs/robots',
  ],
  devtools: { enabled: true },
  app: {
    head: {
      meta: [
        { name: 'viewport', content: 'width=device-width, initial-scale=1' },
      ],
    },
  },
  css: [
    '~/assets/css/main.css',
  ],
  site: {
    env: '',
  },
  compatibilityDate: '2025-05-15',
  vite: {
    server: {
      allowedHosts: ['local.test'],
    },
  },
  typescript: {
    typeCheck: true,
    tsConfig: {
      include: [
        './types/*',
      ],
    },
  },
  eslint: {
    config: {
      stylistic: true,
    },
  },
  i18n: {
    defaultLocale: 'lt',
    locales: [
      {
        code: 'lt',
        file: 'lt-LT.json',
      },
    ],
  },
  sanctum: {
    baseUrl: '',
    origin: '',
    endpoints: {
      login: '/api/login',
      logout: '/api/logout',
      user: '/api/check-auth',
    },
    redirectIfAuthenticated: true,
  },
})
