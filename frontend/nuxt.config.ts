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
      watch: {
        usePolling: true,
        interval: 1000,
      },
      hmr: false, // Disable HMR to avoid WebSocket connection errors
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
    baseUrl: process.env.NUXT_PUBLIC_SANCTUM_BASE_URL || 'http://localhost',
    origin: process.env.NUXT_PUBLIC_SANCTUM_ORIGIN || 'http://localhost:3000',
    endpoints: {
      login: '/api/auth/login',
      logout: '/api/auth/logout',
      user: '/api/auth/me',
    },
    redirect: {
      onLogin: '/',
      onLogout: '/login',
      onAuthOnly: '/login',
      onGuestOnly: '/',
    },
    globalMiddleware: {
      enabled: false,
    },
    client: {
      retry: false,
    },
    csrf: {
      cookie: 'XSRF-TOKEN',
      header: 'X-XSRF-TOKEN',
    },
    logLevel: 0,
    mode: 'cookie',
    redirectIfAuthenticated: false,
    redirectIfUnauthenticated: false,
  },
})
