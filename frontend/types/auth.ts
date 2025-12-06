export interface User {
  id: number
  name: string
  email: string
  role: string
  points: string
  email_verified_at?: string | null
}

export interface LoginCredentials {
  email: string
  password: string
}

export interface RegisterData {
  name: string
  email: string
  password: string
  password_confirmation: string
  role: string
}

export interface AuthResponse {
  message: string
  user: User
  access_token: string
  token_type: string
}

export interface ValidationErrors {
  [key: string]: string | string[]
}
