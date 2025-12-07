<script setup lang="ts">
import type { ShopItem, ShopItemFormData } from '~/types/shop'

definePageMeta({
  middleware: ['auth'],
})

const { user } = useAuth()
const { isParent } = useUserRole()

const shopItems = ref<ShopItem[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const showForm = ref(false)
const editingItem = ref<ShopItem | null>(null)

const form = ref<ShopItemFormData>({
  title: '',
  description: '',
  price: 0,
  quantity: 1,
  icon: 'gift',
})

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

async function handleSubmit() {
  try {
    if (editingItem.value) {
      await useUpdateShopItem(editingItem.value.id, form.value)
    } else {
      await useCreateShopItem(form.value)
    }
    resetForm()
    await fetchShopItems()
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko išsaugoti prekės'
    console.error('Error saving shop item:', err)
  }
}

async function deleteItem(id: number) {
  if (!confirm('Ar tikrai norite ištrinti šią prekę?')) return

  try {
    await useDeleteShopItem(id)
    await fetchShopItems()
  } catch (err: any) {
    error.value = err.data?.message || 'Nepavyko ištrinti prekės'
    console.error('Error deleting shop item:', err)
  }
}

function editItem(item: ShopItem) {
  editingItem.value = item
  form.value = {
    title: item.title,
    description: item.description || '',
    price: parseFloat(item.price),
    quantity: item.quantity,
    icon: item.icon || 'gift',
  }
  showForm.value = true
}

function resetForm() {
  form.value = {
    title: '',
    description: '',
    price: 0,
    quantity: 1,
    icon: 'gift',
  }
  editingItem.value = null
  showForm.value = false
}

onMounted(() => {
  fetchShopItems()
})
</script>

<template>
  <div class="page-container">
    <div class="page-header">
      <div class="header-left">
        <h1 class="page-title">Valdyti parduotuvę</h1>
        <NuxtLink to="/" class="stats-link">Grįžti į užduotis</NuxtLink>
        <NuxtLink to="/shop" class="stats-link">Peržiūrėti parduotuvę</NuxtLink>
      </div>
      <div class="user-info">
        <span class="user-name">{{ user?.name }}</span>
        <span class="user-role">{{ user?.role }}</span>
      </div>
    </div>

    <div class="content-container">
      <div v-if="!isParent" class="error-message">
        Tik tėvai gali valdyti parduotuvę
      </div>

      <div v-else>
        <button @click="showForm = !showForm" class="btn btn-primary">
          {{ showForm ? 'Atšaukti' : 'Pridėti naują prekę' }}
        </button>

        <div v-if="showForm" class="form-container">
          <h2>{{ editingItem ? 'Redaguoti' : 'Pridėti' }} prekę</h2>
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label>Pavadinimas</label>
              <input v-model="form.title" required type="text" />
            </div>

            <div class="form-group">
              <label>Aprašymas</label>
              <textarea v-model="form.description" rows="3"></textarea>
            </div>

            <div class="form-group">
              <label>Kaina (taškai)</label>
              <input v-model.number="form.price" required type="number" min="0" step="0.01" />
            </div>

            <div class="form-group">
              <label>Kiekis</label>
              <input v-model.number="form.quantity" required type="number" min="0" />
            </div>

            <ShopIconPicker v-model="form.icon" />

            <div class="form-actions">
              <button type="submit" class="btn btn-success">Išsaugoti</button>
              <button type="button" @click="resetForm" class="btn btn-secondary">Atšaukti</button>
            </div>
          </form>
        </div>

        <div v-if="error" class="error-message">{{ error }}</div>

        <div v-if="loading" class="loading-state">Kraunama...</div>

        <div v-else class="items-list">
          <div v-for="item in shopItems" :key="item.id" class="shop-item-card">
            <div class="item-icon-display">
              <ShopIcon :name="item.icon || 'gift'" />
            </div>
            <div class="item-header">
              <h3>{{ item.title }}</h3>
              <span class="item-price">{{ item.price }} tšk</span>
            </div>
            <p class="item-description">{{ item.description || 'Nėra aprašymo' }}</p>
            <div class="item-footer">
              <span>Kiekis: {{ item.quantity }}</span>
              <span v-if="item.creator">Sukūrė: {{ item.creator.name }}</span>
            </div>
            <div class="item-actions">
              <button @click="editItem(item)" class="btn btn-small btn-primary">Redaguoti</button>
              <button @click="deleteItem(item.id)" class="btn btn-small btn-danger">Ištrinti</button>
            </div>
          </div>
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

.user-role {
  font-size: 0.9rem;
  opacity: 0.9;
}

.content-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 2rem;
}

.btn {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  cursor: pointer;
  font-size: 1rem;
  font-weight: 600;
  transition: all 0.3s;
}

.btn-primary {
  background: #4f46e5;
  color: white;
}

.btn-primary:hover {
  background: #4338ca;
}

.btn-success {
  background: #10b981;
  color: white;
}

.btn-success:hover {
  background: #059669;
}

.btn-secondary {
  background: #6b7280;
  color: white;
}

.btn-secondary:hover {
  background: #4b5563;
}

.btn-danger {
  background: #ef4444;
  color: white;
}

.btn-danger:hover {
  background: #dc2626;
}

.btn-small {
  padding: 0.5rem 1rem;
  font-size: 0.875rem;
}

.form-container {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  margin: 2rem 0;
}

.form-container h2 {
  margin-top: 0;
  color: #1f2937;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 1rem;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #4f46e5;
}

.form-actions {
  display: flex;
  gap: 1rem;
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

.items-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  margin-top: 2rem;
}

.shop-item-card {
  background: white;
  border-radius: 1rem;
  padding: 1.5rem;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
}

.item-price {
  background: #4f46e5;
  color: white;
  padding: 0.25rem 0.75rem;
  border-radius: 0.5rem;
  font-weight: 600;
  font-size: 0.875rem;
}

.item-description {
  color: #6b7280;
  margin: 0 0 1rem 0;
}

.item-footer {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  margin-bottom: 1rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.item-actions {
  display: flex;
  gap: 0.5rem;
}

.item-icon-display {
  text-align: center;
  margin-bottom: 1rem;
}
</style>