export const useUserRole = () => {
  const { user } = useAuth()

  const isParent = computed(() => user.value?.role === 'parent')
  const isChild = computed(() => user.value?.role === 'child')
  const userRole = computed(() => user.value?.role || null)

  return {
    isParent,
    isChild,
    userRole,
  }
}