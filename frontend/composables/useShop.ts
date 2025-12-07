import type { ShopItemFormData, ShopItemsResponse, ShopItemResponse, PurchaseResponse } from '~/types/shop'

export const useFetchShopItems = () => {
  const { $api } = useNuxtApp()
  return $api<ShopItemsResponse>('/api/shop', {
    method: 'GET',
  })
}

export const useCreateShopItem = (body: ShopItemFormData) => {
  const { $api } = useNuxtApp()
  return $api<ShopItemResponse>('/api/shop', {
    method: 'POST',
    body,
  })
}

export const useUpdateShopItem = (id: number, body: Partial<ShopItemFormData>) => {
  const { $api } = useNuxtApp()
  return $api<ShopItemResponse>(`/api/shop/${id}`, {
    method: 'PUT',
    body,
  })
}

export const useDeleteShopItem = (id: number) => {
  const { $api } = useNuxtApp()
  return $api(`/api/shop/${id}`, {
    method: 'DELETE',
  })
}

export const usePurchaseShopItem = (id: number, quantity: number = 1) => {
  const { $api } = useNuxtApp()
  return $api<PurchaseResponse>(`/api/shop/${id}/purchase`, {
    method: 'POST',
    body: { quantity },
  })
}