export default defineNuxtRouteMiddleware(async (to, from) => {
  // Skip middleware during SSR - only run on client side
  if (import.meta.server) {
    return
  }

  try {
    const { isAuthenticated } = useSanctumAuth()

    if (!isAuthenticated.value) {
      return navigateTo('/login')
    }
  } catch (error) {
    // Redirect to login if auth check fails
    console.error('Auth middleware error:', error)
    return navigateTo('/login')
  }
})
