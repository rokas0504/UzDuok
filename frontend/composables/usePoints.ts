import type { PointTransactionsResponse, PointTransactionFilters } from '~/types/points'

export const useFetchPointTransactions = (filters: PointTransactionFilters) => {
  const { $api } = useNuxtApp()
  
  // Build query params
  const params = new URLSearchParams()
  
  if (filters.child_id) {
    params.append('child_id', filters.child_id.toString())
  }
  if (filters.reason) {
    params.append('reason', filters.reason)
  }
  if (filters.start_date) {
    params.append('start_date', filters.start_date)
  }
  if (filters.end_date) {
    params.append('end_date', filters.end_date)
  }
  
  const queryString = params.toString()
  const url = queryString ? `/api/point-transactions?${queryString}` : '/api/point-transactions'
  
  return $api<PointTransactionsResponse>(url, {
    method: 'GET',
  })
}
