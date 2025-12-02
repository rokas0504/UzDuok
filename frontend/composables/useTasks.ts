import type { TaskFormData, TasksResponse, TaskResponse, TaskStatus, PeriodicTaskResponse } from '~/types/task'

export const useFetchTasks = () => {
  const { $api } = useNuxtApp()
  return $api<TasksResponse>('/api/tasks', {
    method: 'GET',
  })
}

export const useCreateTask = (body: TaskFormData) => {
  const { $api } = useNuxtApp()
  return $api<TaskResponse | PeriodicTaskResponse>('/api/tasks', {
    method: 'POST',
    body,
  })
}

export const useUpdateTask = (id: number, body: Partial<TaskFormData>) => {
  const { $api } = useNuxtApp()
  return $api<TaskResponse>(`/api/tasks/${id}`, {
    method: 'PUT',
    body,
  })
}

export const useUpdateTaskStatus = (id: number, status: TaskStatus) => {
  const { $api } = useNuxtApp()
  return $api<TaskResponse>(`/api/tasks/${id}/status`, {
    method: 'PATCH',
    body: { status },
  })
}

export const useDeleteTask = (id: number) => {
  const { $api } = useNuxtApp()
  return $api(`/api/tasks/${id}`, {
    method: 'DELETE',
  })
}