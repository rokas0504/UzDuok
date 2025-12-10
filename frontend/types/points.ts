export type PointTransactionReason = 'task_completed' | 'task_cancelled' | 'shop_purchase'

export interface PointTransaction {
  id: number
  user_id: number
  task_id: number | null
  shop_item_id: number | null
  amount: string
  previous_balance: string
  new_balance: string
  reason: PointTransactionReason
  created_at: string
  updated_at: string
  user?: {
    id: number
    name: string
  }
}

export interface PointTransactionsResponse {
  transactions: PointTransaction[]
}

export interface PointTransactionFilters {
  child_id?: number | null
  reason?: PointTransactionReason | null
  start_date?: string
  end_date?: string
}

export interface DeductPointsRequest {
  child_id: number
  points: number
  reason: string
}

export interface DeductPointsResponse {
  message: string
}
