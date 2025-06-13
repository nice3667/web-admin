<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiAccountMultiple,
  mdiAccountStar,
  mdiAccountGroup,
  mdiCash,
  mdiCurrencyUsd,
  mdiTrendingUp,
  mdiAlertBoxOutline,
  mdiMagnify,
  mdiRefresh,
  mdiChevronLeft,
  mdiChevronRight,
} from "@mdi/js"
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import CardBoxWidget from '@/Components/CardBoxWidget.vue'
import CardBoxModal from '@/Components/CardBoxModal.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import { ref, onMounted, computed } from 'vue'
import FormControl from '@/Components/FormControl.vue'

const loading = ref(true)
const error = ref(null)
const accounts = ref([])
const isModalActive = ref(false)
const currentPage = ref(1)
const itemsPerPage = 10

// Add temporary filters for search modal
const tempFilters = ref({
  partner_account: '',
  client_uid: '',
  reg_date: '',
  client_country: '',
  client_status: 'all'
})

// Keep the actual filters for filtering
const filters = ref({
  partner_account: '',
  client_uid: '',
  reg_date: '',
  client_country: '',
  client_status: 'all'
})

// Reset filters
const resetFilters = () => {
  tempFilters.value = {
    partner_account: '',
    client_uid: '',
    reg_date: '',
    client_country: '',
    client_status: 'all'
  }
  filters.value = {
    partner_account: '',
    client_uid: '',
    reg_date: '',
    client_country: '',
    client_status: 'all'
  }
  currentPage.value = 1
}

// Filtered accounts
const filteredAccounts = computed(() => {
  return accounts.value.filter(account => {
    // Partner Account filter
    if (filters.value.partner_account && account.partner_account && 
        !account.partner_account.toLowerCase().includes(filters.value.partner_account.toLowerCase())) {
      return false
    }

    // Client UID filter
    if (filters.value.client_uid && account.client_uid && 
        !account.client_uid.toLowerCase().includes(filters.value.client_uid.toLowerCase())) {
      return false
    }

    // Registration Date filter
    if (filters.value.reg_date && account.reg_date && 
        account.reg_date !== filters.value.reg_date) {
      return false
    }

    // Country filter
    if (filters.value.client_country && account.client_country && 
        account.client_country !== filters.value.client_country) {
      return false
    }

    // Status filter
    if (filters.value.client_status !== 'all' && account.client_status && 
        account.client_status !== filters.value.client_status) {
      return false
    }

    return true
  })
})

// Paginated accounts
const paginatedAccounts = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredAccounts.value.slice(start, end)
})

// Total pages
const totalPages = computed(() => {
  return Math.ceil(filteredAccounts.value.length / itemsPerPage)
})

// Navigation methods
const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

// Statistics
const stats = computed(() => {
  const accountsArray = filteredAccounts.value;
  const totalAccounts = accountsArray.length;
  const totalVolumeLots = accountsArray.reduce((sum, acc) => sum + (parseFloat(acc.volume_lots) || 0), 0);
  const totalVolumeUsd = accountsArray.reduce((sum, acc) => sum + (parseFloat(acc.volume_mln_usd) || 0), 0);
  const totalReward = accountsArray.reduce((sum, acc) => sum + (parseFloat(acc.reward_usd) || 0), 0);

  return {
    level1_clients: totalAccounts,
    total_accounts: totalAccounts,
    total_volume_lots: totalVolumeLots.toFixed(2),
    total_volume_usd: (totalVolumeUsd * 1000000).toFixed(2), // Convert to actual USD
    total_profit: totalReward.toFixed(2)
  }
})

// Apply search
const applySearch = () => {
  filters.value = { ...tempFilters.value }
  currentPage.value = 1
  isModalActive.value = false
}

// Format functions
const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatNumber = (number) => {
  if (!number) return '0'
  return new Intl.NumberFormat('th-TH', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(number)
}

const formatCurrency = (amount) => {
  if (!amount) return '$0.00'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(amount)
}

// Fetch data
const fetchAccounts = async () => {
  try {
    loading.value = true
    error.value = null
    const response = await fetch('http://127.0.0.1:8000/api/exness/clients')
    if (!response.ok) throw new Error('Network response was not ok')
    const json = await response.json()
    // ตรวจสอบโครงสร้างข้อมูลแบบ Clients.vue
    if (json && json.data_v1 && json.data_v2) {
      const v1Data = json.data_v1
      const v2Data = json.data_v2
      // สร้าง map ของ client_status และ rebate_amount_usd จาก V2 โดยใช้ 8 ตัวแรกของ client_uid
      const v2Map = {}
      if (Array.isArray(v2Data)) {
        v2Data.forEach(client => {
          if (client.client_uid) {
            const shortUid = client.client_uid.substring(0, 8)
            v2Map[shortUid] = {
              client_status: client.client_status ? client.client_status.toUpperCase() : 'UNKNOWN',
              rebate_amount_usd: client.rebate_amount_usd !== undefined ? client.rebate_amount_usd : '-'
            }
          }
        })
      }
      // รวมข้อมูล V1 กับ client_status และ rebate_amount_usd จาก V2
      if (Array.isArray(v1Data)) {
        accounts.value = v1Data.map(client => {
          const v2 = v2Map[client.client_uid] || {}
          return {
            ...client,
            client_status: v2.client_status || 'UNKNOWN',
            rebate_amount_usd: v2.rebate_amount_usd !== undefined ? v2.rebate_amount_usd : '-'
          }
        })
      } else {
        error.value = 'รูปแบบข้อมูลไม่ถูกต้อง'
      }
    } else {
      error.value = 'ไม่พบข้อมูลลูกค้า'
    }
  } catch (err) {
    console.error('Error fetching accounts:', err)
    error.value = 'ไม่สามารถดึงข้อมูลบัญชีได้ กรุณาลองใหม่อีกครั้ง'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchAccounts()
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="รายงานบัญชีลูกค้า" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountGroup"
        title="รายงานบัญชีลูกค้า"
        main
      >
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="error"
        color="danger"
        :icon="mdiAlertBoxOutline"
      >
        {{ error }}
      </NotificationBar>

      <!-- Statistics Widgets -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <CardBoxWidget
          :number="stats.level1_clients"
          label="จำนวนลูกค้าระดับ 1"
          color="text-purple-500"
          class="transform hover:scale-105 transition-transform duration-200"
        />
        <CardBoxWidget
          :number="stats.total_accounts"
          label="จำนวนบัญชีทั้งหมด"
          color="text-blue-500"
          class="transform hover:scale-105 transition-transform duration-200"
        />
        <CardBoxWidget
          :number="stats.total_volume_lots"
          label="Volume (lots)"
          :suffix="'lots'"
          color="text-green-500"
          class="transform hover:scale-105 transition-transform duration-200"
        />
        <CardBoxWidget
          :number="stats.total_volume_usd"
          label="Volume (USD)"
          prefix="$"
          color="text-yellow-500"
          class="transform hover:scale-105 transition-transform duration-200"
        />
        <CardBoxWidget
          :number="stats.total_profit"
          label="Reward (USD)"
          prefix="$"
          color="text-emerald-500"
          class="transform hover:scale-105 transition-transform duration-200"
        />
      </div>

      <!-- Search Button -->
      <div class="flex justify-end mb-6">
        <BaseButton
          label="ค้นหาข้อมูล"
          color="info"
          :icon="mdiMagnify"
          @click="isModalActive = true"
        />
        <BaseButton
          label="รีเซ็ต"
          color="danger"
          :icon="mdiRefresh"
          class="ml-2"
          @click="resetFilters"
        />
      </div>

      <!-- Table Section -->
      <CardBox class="mb-6 overflow-hidden" has-table>
        <div v-if="loading" class="p-4 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
          <p class="mt-2 text-gray-600">กำลังโหลดข้อมูล...</p>
        </div>
        <div v-else-if="error" class="p-4 text-center text-red-600">{{ error }}</div>
        <div v-else-if="!accounts.length" class="p-4 text-center text-gray-600">
          <p>ไม่พบข้อมูล</p>
          <p class="text-sm mt-2">จำนวนข้อมูล: {{ accounts.length }}</p>
          <p class="text-sm">ข้อมูลที่กรองแล้ว: {{ paginatedAccounts.length }}</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">partner_account_name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">partner_account</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">country</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">client_uid</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">client_account</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">client_account_type</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">reg_date</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">volume_mln_usd</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">volume_lots</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">currency</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">comment</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 dark:divide-gray-700">
              <tr v-for="account in paginatedAccounts" :key="account.client_uid">
                <td class="px-6 py-4 whitespace-nowrap">{{ account.partner_account_name }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.partner_account }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.country }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.client_uid }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.client_account }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.client_account_type }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatDate(account.reg_date) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatNumber(account.volume_mln_usd) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ formatNumber(account.volume_lots) }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.currency }}</td>
                <td class="px-6 py-4 whitespace-nowrap">{{ account.comment }}</td>
              </tr>
              <tr v-if="paginatedAccounts.length === 0">
                <td colspan="11" class="px-6 py-4 text-center text-sm text-gray-500">
                  <div class="flex flex-col items-center justify-center py-8">
                    <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-lg font-medium text-gray-900">ไม่พบข้อมูลที่ค้นหา</p>
                    <p class="text-sm text-gray-500 mt-1">ลองปรับเงื่อนไขการค้นหาใหม่</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <!-- Pagination Controls -->
      <div class="flex justify-between items-center mt-4">
        <div class="text-sm text-gray-700 dark:text-gray-400">
          แสดง {{ (currentPage - 1) * itemsPerPage + 1 }} ถึง {{ Math.min(currentPage * itemsPerPage, accounts.length) }} จาก {{ accounts.length }} รายการ
        </div>
        <div class="flex space-x-2">
          <BaseButton
            :disabled="currentPage === 1"
            :icon="mdiChevronLeft"
            color="white"
            @click="prevPage"
          />
          <BaseButton
            :disabled="currentPage === totalPages"
            :icon="mdiChevronRight"
            color="white"
            @click="nextPage"
          />
        </div>
      </div>

      <!-- Search Modal -->
      <CardBoxModal
        v-model="isModalActive"
        title="ค้นหาข้อมูล"
        @confirm="applySearch"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <FormControl
            v-model="tempFilters.partner_account"
            label="Partner Account"
            placeholder="ค้นหาด้วย Partner Account"
          />
          <FormControl
            v-model="tempFilters.client_uid"
            label="Client UID"
            placeholder="ค้นหาด้วย Client UID"
          />
          <FormControl
            v-model="tempFilters.reg_date"
            label="วันที่ลงทะเบียน"
            type="date"
          />
          <FormControl
            v-model="tempFilters.client_country"
            label="ประเทศ"
            placeholder="ค้นหาด้วยรหัสประเทศ"
          />
          <FormControl
            v-model="tempFilters.client_status"
            label="สถานะ"
            :options="[
              { id: 'all', label: 'ทั้งหมด' },
              { id: 'active', label: 'Active' },
              { id: 'inactive', label: 'Inactive' },
              { id: 'pending', label: 'Pending' }
            ]"
          />
        </div>
        <template #footer>
          <div class="flex justify-between w-full">
            <BaseButton
              label="รีเซ็ต"
              color="danger"
              @click="resetFilters"
            />
            <div class="flex gap-2">
              <BaseButton
                label="ยกเลิก"
                color="white"
                @click="isModalActive = false"
              />
              <BaseButton
                label="ค้นหา"
                color="info"
                @click="applySearch"
              />
            </div>
          </div>
        </template>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template> 