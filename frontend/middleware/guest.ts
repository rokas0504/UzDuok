export default defineNuxtRouteMiddleware(async (to, from) => {
  // Skip middleware during SSR - only run on client side
  if (import.meta.server) {
    return
  }

  try {
    const { isAuthenticated } = useSanctumAuth()

    if (isAuthenticated.value) {
      return navigateTo('/')
    }
  } catch (error) {
    // Allow navigation if auth check fails
    console.error('Guest middleware error:', error)
  }
})
