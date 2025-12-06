export interface ShopItem {
  id: number
  title: string
  description: string | null
  price: string
  quantity: number
  icon: string | null
  created_by: number
  created_at: string
  updated_at: string
  creator?: {
    id: number
    name: string
  }
}

export interface ShopItemFormData {
  title: string
  description?: string
  price: number
  quantity: number
  icon?: string
}

export interface Purchase {
  id: number
  user_id: number
  shop_item_id: number
  price_paid: string
  quantity: number
  created_at: string
  updated_at: string
}

export interface ShopItemsResponse {
  shop_items: ShopItem[]
}

export interface ShopItemResponse {
  shop_item: ShopItem
  message?: string
}

export interface PurchaseResponse {
  message: string
  purchase: Purchase
  new_balance: string
}