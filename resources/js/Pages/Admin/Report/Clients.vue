<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiClockOutline,
  mdiAlertBoxOutline,
  mdiAccountGroup
} from "@mdi/js"
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue"
import SectionMain from "@/Components/SectionMain.vue"
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import CardBox from "@/Components/CardBox.vue"
import NotificationBar from "@/Components/NotificationBar.vue"
import BaseButton from "@/Components/BaseButton.vue"
import BaseButtons from "@/Components/BaseButtons.vue"
import { ref, onMounted, computed } from 'vue'
import axios from 'axios'

const clients = ref([])
const loading = ref(false)
const error = ref('')

onMounted(async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await axios.get('/api/exness/clients')
    // Convert the object to an array if it's not already
    const data = response.data.data.data || {}
    clients.value = Object.values(data)
    console.log('Fetched clients:', clients.value) // Debug log
  } catch (err) {
    console.error('Error fetching clients:', err)
    error.value = 'ไม่สามารถดึงข้อมูลลูกค้าได้ กรุณาลองใหม่อีกครั้ง'
  } finally {
    loading.value = false
  }
})

const pendingTransactions = ref([
  {
    id: 1,
    client_name: 'John Doe',
    transaction_id: 'TRX001',
    type: 'Withdrawal',
    amount: 1000.00,
    currency: 'USD',
    payment_method: 'Bank Transfer',
    requested_date: '2024-03-20',
    due_date: '2024-03-22'
  },
  {
    id: 2,
    client_name: 'Jane Smith',
    transaction_id: 'TRX002',
    type: 'Deposit',
    amount: 2000.00,
    currency: 'USD',
    payment_method: 'Bank Transfer',
    requested_date: '2024-03-20',
    due_date: '2024-03-22'
  }
  // Add more sample data as needed
])

const filters = ref({
  search: '',
  status: 'all',
  date_range: {
    start: '',
    end: ''
  }
})

const resetFilters = () => {
  filters.value = {
    search: '',
    status: 'all',
    date_range: {
      start: '',
      end: ''
    }
  }
}

const filteredClients = computed(() => {
  let result = clients.value || []

  // Search filter
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    result = result.filter(client => 
      client.partner_account?.toLowerCase().includes(searchLower)
    )
  }

  // Status filter
  if (filters.value.status !== 'all') {
    result = result.filter(client => 
      client.client_status === filters.value.status
    )
  }

  // Date range filter
  if (filters.value.date_range.start || filters.value.date_range.end) {
    result = result.filter(client => {
      const regDate = new Date(client.reg_date)
      const start = filters.value.date_range.start ? new Date(filters.value.date_range.start) : null
      const end = filters.value.date_range.end ? new Date(filters.value.date_range.end) : null

      if (start && regDate < start) return false
      if (end && regDate > end) return false
      return true
    })
  }

  return result
})

const stats = computed(() => {
  const clientsArray = clients.value || [];
  const totalAccounts = clientsArray.length;
  const totalVolumeLots = clientsArray.reduce((sum, client) => sum + (parseFloat(client.volume_lots) || 0), 0);
  const totalVolumeUsd = clientsArray.reduce((sum, client) => sum + (parseFloat(client.volume_mln_usd) || 0), 0);
  const totalReward = clientsArray.reduce((sum, client) => sum + (parseFloat(client.reward_usd) || 0), 0);

  return {
    total_pending: totalAccounts,
    total_amount: totalVolumeLots,
    due_today: totalVolumeUsd.toFixed(4),
    overdue: Math.ceil(totalReward)
  }
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

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Total Partner Account</span>
            <span class="text-2xl font-bold">{{ stats.total_pending }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (lots)</span>
            <span class="text-2xl font-bold">{{ stats.total_amount.toFixed(2) }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (USD)</span>
            <span class="text-2xl font-bold text-yellow-600">{{ stats.due_today }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Reward (USD)</span>
            <span class="text-2xl font-bold text-red-600">{{ stats.overdue }}</span>
          </div>
        </CardBox>
        
      </div>

      <CardBox class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search Account</label>
            <input
              v-model="filters.search"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="Search Partner Account"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Status</option>
              <option value="INACTIVE">INACTIVE</option>
              <option value="ACTIVE">ACTIVE</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Registration Date Range</label>
            <div class="flex space-x-2">
              <input
                v-model="filters.date_range.start"
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
              <input
                v-model="filters.date_range.end"
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
          </div>
          <div class="flex items-end">
            <BaseButton
              color="warning"
              label="Reset"
              @click="resetFilters"
            />
          </div>
        </div>
      </CardBox>

      <CardBox class="mb-6" has-table>
        <div v-if="loading" class="p-4 text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
          <p class="mt-2 text-gray-600">กำลังโหลดข้อมูล...</p>
        </div>
        <div v-else-if="error" class="p-4 text-center text-red-600">{{ error }}</div>
        <div v-else-if="!clients.length" class="p-4 text-center text-gray-600">
          <p>ไม่พบข้อมูล</p>
        </div>
        <table v-else>
          <thead>
            <tr>
              <th>Partner Account</th>
              <th>Client UID</th>
              <th>วันที่ลงทะเบียน</th>
              <th>ประเทศ</th>
              <th>สถานะ</th>
              <th>Volume (Lots)</th>
              <th>Reward (USD)</th>
              <th>KYC</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="client in filteredClients" :key="client.client_uid">
              <td data-label="Partner Account">{{ client.partner_account }}</td>
              <td data-label="Client UID">{{ client.client_uid }}</td>
              <td data-label="วันที่ลงทะเบียน">{{ client.reg_date }}</td>
              <td data-label="ประเทศ">{{ client.client_country }}</td>
              <td data-label="สถานะ">{{ client.client_status }}</td>
              <td data-label="Volume (Lots)">{{ client.volume_lots }}</td>
              <td data-label="Reward (USD)">{{ client.reward_usd }}</td>
              <td data-label="KYC">{{ client.kyc_passed ? 'ผ่าน' : 'ไม่ผ่าน' }}</td>
            </tr>
          </tbody>
        </table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template> 