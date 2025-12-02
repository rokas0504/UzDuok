export type TaskStatus = 'pending' | 'in_progress' | 'completed' | 'cancelled'

export interface Task {
  id: number
  title: string
  description: string | null
  status: TaskStatus
  status_label: string
  price: string
  is_repeated: boolean
  start_date: string
  end_date: string
  user_id: number
  created_at: string
  updated_at: string
}

export interface TaskFormData {
  title: string
  description?: string
  price: number
  is_repeated?: boolean
  start_date: string
  end_date: string
  user_id: number
  // Periodic task fields
  selected_weekdays?: number[] // 0 = Monday, 6 = Sunday
  weeks_count?: number
}

export interface TasksResponse {
  tasks: Task[]
}

export interface TaskResponse {
  task: Task
  message?: string
}

export interface PeriodicTaskResponse {
  tasks: Task[]
  count: number
  message?: string
}