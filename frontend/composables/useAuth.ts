import type { User, LoginCredentials, RegisterData } from '~/types/auth'

export const useAuth = () => {
  let sanctumAuth
  try {
    sanctumAuth = useSanctumAuth()
  } catch (error: any) {
    console.error('Error initializing Sanctum auth:', error)
    // Return a fallback auth object
    return {
      user: computed(() => null),
      isAuthenticated: computed(() => false),
      login: async () => { throw new Error('Auth not initialized') },
      register: async () => { throw new Error('Auth not initialized') },
      logout: async () => { throw new Error('Auth not initialized') },
      refreshUser: async () => {},
    }
  }

  // Cast user to proper type with computed to maintain reactivity
  const user = computed<User | null>(() => {
    try {
      const rawUser = sanctumAuth.user.value
      if (!rawUser || typeof rawUser !== 'object') {
        return null
      }
      // Extract nested user object if it exists (API returns { user: { ... } })
      const userData = (rawUser as any).user || rawUser
      return userData as User
    } catch (error: any) {
      console.error('Error getting user:', error)
      return null
    }
  })

  // Typed login function
  const login = async (credentials: LoginCredentials) => {
    try {
      await sanctumAuth.login(credentials as unknown as Record<string, unknown>)
    } catch (error) {
      throw error
    }
  }

  // Typed register function
  const register = async (data: RegisterData) => {
    try {
      const config = useRuntimeConfig()
      const client = useSanctumClient()

      // First, get CSRF token
      await client('/sanctum/csrf-cookie', {
        credentials: 'include',
      })

      // Then make the registration request with credentials
      // The backend automatically logs in the user
      const response = await client('/api/auth/register', {
        method: 'POST',
        body: data,
        credentials: 'include',
      })

      // Refresh user data to update auth state
      try {
        await refreshUser()
      } catch (refreshError: any) {
        // If refresh fails, still continue - user is registered
        console.warn('Failed to refresh user after registration:', refreshError)
      }

      return response
    } catch (error) {
      throw error
    }
  }

  // Typed logout function
  const logout = async () => {
    try {
      await sanctumAuth.logout()
    } catch (error: any) {
      // Log the error but don't throw - we want to logout client-side even if server fails
      console.error('Logout error:', error)
      // Clear client-side auth state even if server request fails
      sanctumAuth.user.value = null
    }
  }

  // Refresh user data
  const refreshUser = async () => {
    try {
      await sanctumAuth.refreshIdentity()
    } catch (error: any) {
      // Silently handle 401 errors (user not authenticated)
      if (error?.response?.status === 401 || error?.statusCode === 401) {
        return
      }
      // Log other errors for debugging
      console.error('Error refreshing user:', error)
      // Don't throw, just silently fail
    }
  }

  return {
    user,
    isAuthenticated: sanctumAuth.isAuthenticated,
    login,
    register,
    logout,
    refreshUser,
  }
}
