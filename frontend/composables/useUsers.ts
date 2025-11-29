import type { User } from '~/types/auth'

interface UsersResponse {
  users: User[]
}

export const useFetchChildren = () => {
  const { $api } = useNuxtApp()
  return $api<UsersResponse>('/api/users/children', {
    method: 'GET',
  })
}