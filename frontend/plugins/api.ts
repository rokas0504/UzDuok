export default defineNuxtPlugin(() => {
  const runtimeConfig = useSanctumConfig()
  const baseURL = runtimeConfig.baseUrl

  const api = $fetch.create({
    baseURL: baseURL,
    async onRequest({ options }) {
      options.headers.set('ACCEPT', 'application/json')

      if (options.method && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(options.method)) {
        await $fetch(`${baseURL}/sanctum/csrf-cookie`, {
          credentials: 'include',
        })
        const token = useCookie('XSRF-TOKEN').value

        if (token) {
          options.headers.set('X-XSRF-TOKEN', token)
        }
      }
    },
    credentials: 'include',
  })

  return {
    provide: {
      api,
    },
  }
})
