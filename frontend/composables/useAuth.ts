import type { User, LoginCredentials, RegisterData } from '~/types/auth'

export const useAuth = () => {
  let sanctumAuth
  try {
    sanctumAuth = useSanctumAuth()
  } catch (error: unknown) {
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
      const userData = (rawUser as Record<string, unknown>).user || rawUser
      return userData as User
    } catch (error: unknown) {
      console.error('Error getting user:', error)
      return null
    }
  })

  // Typed login function - removed useless try-catch
  const login = async (credentials: LoginCredentials) => {
    await sanctumAuth.login(credentials as unknown as Record<string, unknown>)
  }

  // Typed register function - removed dead code (config), useless try-catch
  const register = async (data: RegisterData) => {
    const client = useSanctumClient()

    // First, get CSRF token
    await client('/sanctum/csrf-cookie', {
      credentials: 'include',
    })

    // Then make the registration request with credentials
    const response = await client('/api/auth/register', {
      method: 'POST',
      body: data,
      credentials: 'include',
    })

    // Refresh user data to update auth state
    try {
      await refreshUser()
    } catch (refreshError: unknown) {
      // If refresh fails, still continue - user is registered
      console.warn('Failed to refresh user after registration:', refreshError)
    }

    return response
  }

  // Typed logout function
  const logout = async () => {
    try {
      await sanctumAuth.logout()
    } catch (error: unknown) {
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
    } catch (error: unknown) {
      // Silently handle 401 errors (user not authenticated)
      const err = error as { response?: { status: number }; statusCode?: number }
      if (err?.response?.status === 401 || err?.statusCode === 401) {
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
