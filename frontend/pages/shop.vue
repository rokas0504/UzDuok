<script setup lang="ts">
import type { ShopItem } from '~/types/shop'

definePageMeta({
  middleware: ['auth'],
})

const { user } = useAuth()
const { isParent } = useUserRole()

const shopItems = ref<ShopItem[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const purchaseLoading = ref<number | null>(null)

async function fetchShopItems() {
  loading.value = true
  error.value = null
  try {
    const response = await useFetchShopItems()
    shopItems.value = response.shop_items
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko įkelti prekių'
    console.error('Error fetching shop items:', err)
  } finally {
    loading.value = false
  }
}

async function buyItem(item: ShopItem) {
  const userPoints = parseFloat(user.value?.points || '0')
  const itemPrice = parseFloat(item.price)

  if (userPoints < itemPrice) {
    alert(`Nepakanka taškų! Turite ${userPoints} taškų, o ši prekė kainuoja ${itemPrice} taškų.`)
    return
  }

  if (item.quantity <= 0) {
    alert('Ši prekė išparduota!')
    return
  }

  if (!confirm(`Pirkti "${item.title}" už ${item.price} taškų?`)) return

  purchaseLoading.value = item.id
  try {
    const response = await usePurchaseShopItem(item.id, 1)
    alert(response.message)

    // Update user points
    if (user.value) {
      user.value.points = response.new_balance
    }

    // Refresh shop items to get updated quantities
    await fetchShopItems()
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko nupirkti prekės'
    alert(error.value)
    console.error('Error purchasing item:', err)
  } finally {
    purchaseLoading.value = null
  }
}

function canAfford(item: ShopItem): boolean {
  const userPoints = parseFloat(user.value?.points || '0')
  const itemPrice = parseFloat(item.price)
  return userPoints >= itemPrice
}

onMounted(() => {
  fetchShopItems()
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">Parduotuvė</h1>
        <NuxtLink to="/" class="stats-link">Grįžti į užduotis</NuxtLink>
        <NuxtLink v-if="isParent" to="/shop-manage" class="stats-link">Valdyti parduotuvę</NuxtLink>
      </div>
      <div class="user-info">
        <span class="user-name">{{ user?.name }}</span>
        <span class="user-points">Taškai: {{ user?.points || 0 }}</span>
      </div>
    </div>

    <div class="content-container">
      <div v-if="error" class="error-message">{{ error }}</div>

      <div v-if="loading" class="loading-state">Kraunama...</div>

      <div v-else-if="shopItems.length === 0" class="empty-state">
        <p>Parduotuvėje dar nėra prekių!</p>
        <NuxtLink v-if="isParent" to="/shop-manage" class="btn btn-primary">
          Pridėti prekių
        </NuxtLink>
      </div>

      <div v-else class="items-list">
        <div
          v-for="item in shopItems"
          :key="item.id"
          class="shop-item-card"
          :class="{ 'out-of-stock': item.quantity <= 0, 'cannot-afford': !canAfford(item) }"
        >
          <div class="item-icon">
            <ShopIcon :name="item.icon || 'gift'" />
          </div>

          <div class="item-header">
            <h3>{{ item.title }}</h3>
            <span class="item-price">{{ item.price }} tšk</span>
          </div>

          <p class="item-description">{{ item.description || 'Nėra aprašymo' }}</p>

          <div class="item-footer">
            <span class="item-quantity" :class="{ 'low-stock': item.quantity <= 5 && item.quantity > 0 }">
              {{ item.quantity > 0 ? `Likutis: ${item.quantity}` : 'Išparduota' }}
            </span>
          </div>

          <button
            @click="buyItem(item)"
            class="btn btn-buy"
            :disabled="item.quantity <= 0 || !canAfford(item) || purchaseLoading === item.id"
            :class="{
              'btn-disabled': item.quantity <= 0 || !canAfford(item),
              'btn-loading': purchaseLoading === item.id
            }"
          >
            <span v-if="purchaseLoading === item.id">Perkama...</span>
            <span v-else-if="item.quantity <= 0">Išparduota</span>
            <span v-else-if="!canAfford(item)">Nepakanka taškų</span>
            <span v-else>Pirkti</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.page-container {
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  padding: 2rem 0;
}

.page-header {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.header-left {
  display: flex;
  align-items: center;
  gap: 1.5rem;
}

.page-title {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  margin: 0;
}

.stats-link {
  color: white;
  text-decoration: none;
  padding: 0.5rem 1rem;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 0.5rem;
  transition: background 0.3s;
}

.stats-link:hover {
  background: rgba(255, 255, 255, 0.3);
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  color: white;
}

.user-name {
  font-weight: 600;
  font-size: 1.1rem;
}

.user-points {
  font-size: 1.2rem;
  font-weight: 700;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 0.5rem;
  margin-top: 0.25rem;
}

.content-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.error-message {
  background: #fee2e2;
  color: #991b1b;
  padding: 1rem;
  border-radius: 0.5rem;
  margin: 1rem 0;
}

.loading-state {
  color: white;
  font-size: 1.2rem;
  text-align: center;
  padding: 2rem;
}

.empty-state {
  text-align: center;
  color: white;
  padding: 3rem;
}

.empty-state p {
  font-size: 1.5rem;
  margin-bottom: 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s;
  text-decoration: none;
  display: inline-block;
}

.btn-primary {
  background: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background: #4338ca;
}

.items-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.shop-item-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}

.shop-item-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.shop-item-card.out-of-stock {
  opacity: 0.7;
}

.shop-item-card.cannot-afford {
  border: 2px solid #fbbf24;
}

.item-icon {
  font-size: 3rem;
  text-align: center;
  margin-bottom: 1rem;
}

.item-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.item-header h3 {
  margin: 0;
  color: #1f2937;
  font-size: 1.25rem;
  flex: 1;
}

.item-price {
  background: #4f46e5;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 0.5rem;
  font-weight: 700;
  font-size: 0.875rem;
  white-space: nowrap;
  margin-left: 0.5rem;
}

.item-description {
  color: #6b7280;
  margin: 0 0 1rem 0;
  min-height: 3rem;
}

.item-footer {
  margin-bottom: 1rem;
}

.item-quantity {
  font-size: 0.875rem;
  color: #10b981;
  font-weight: 600;
}

.item-quantity.low-stock {
  color: #f59e0b;
}

.btn-buy {
  width: 100%;
  background: #10b981;
  color: white;
}

.btn-buy:hover:not(:disabled) {
  background: #059669;
}

.btn-buy:disabled {
  cursor: not-allowed;
  opacity: 0.6;
}

.btn-disabled {
  background: #9ca3af !important;
}

.btn-loading {
  background: #6b7280 !important;
}
</style>