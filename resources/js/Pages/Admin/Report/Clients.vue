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
    console.log('เริ่มดึงข้อมูล...')
    const response = await axios.get('/api/exness/clients')
    console.log('ข้อมูลที่ได้รับจาก API:', response.data)
    
    // ตรวจสอบโครงสร้างข้อมูล
    if (response.data && response.data.data_v1 && response.data.data_v2) {
      const v1Data = response.data.data_v1
      const v2Data = response.data.data_v2
      console.log('ข้อมูล V1:', v1Data)
      console.log('ข้อมูล V2:', v2Data)
      
      // สร้าง map ของ client_status จาก V2 โดยใช้ 8 ตัวแรกของ client_uid
      const v2StatusMap = {}
      if (Array.isArray(v2Data)) {
        v2Data.forEach(client => {
          if (client.client_uid) {
            const shortUid = client.client_uid.substring(0, 8)
            v2StatusMap[shortUid] = client.client_status ? client.client_status.toUpperCase() : 'UNKNOWN'
            console.log(`V2 Mapping: ${shortUid} => ${v2StatusMap[shortUid]}`)
          }
        })
      }
      console.log('V2 Status Map:', v2StatusMap)
      
      // รวมข้อมูล V1 กับ client_status จาก V2
      if (Array.isArray(v1Data)) {
        clients.value = v1Data.map(client => {
          const status = v2StatusMap[client.client_uid] || 'UNKNOWN'
          console.log(`Mapping client_uid ${client.client_uid} to status: ${status}`)
          return {
            ...client,
            client_status: status
          }
        })
        console.log('ข้อมูลลูกค้าที่รวมแล้ว:', clients.value)
        console.log('จำนวนลูกค้าที่พบ:', clients.value.length)
      } else {
        console.error('รูปแบบข้อมูล V1 ไม่ถูกต้อง:', v1Data)
        error.value = 'รูปแบบข้อมูลไม่ถูกต้อง'
      }
    } else {
      console.error('ไม่พบข้อมูลใน response:', response.data)
      error.value = 'ไม่พบข้อมูลลูกค้า'
    }
  } catch (err) {
    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', err)
    error.value = 'ไม่สามารถดึงข้อมูลลูกค้าได้ กรุณาลองใหม่อีกครั้ง'
  } finally {
    loading.value = false
  }
})


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
  console.log('ข้อมูลก่อนกรอง:', result)

  // ค้นหา
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase()
    result = result.filter(client => 
      (client.client_uid?.toLowerCase().includes(searchLower) || 
       client.id?.toString().includes(searchLower))
    )
  }

  // กรองตามสถานะ
  if (filters.value.status !== 'all') {
    result = result.filter(client => 
      client.client_status === filters.value.status
    )
  }

  // กรองตามช่วงวันที่
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

  console.log('ข้อมูลหลังกรอง:', result)
  return result
})

const stats = computed(() => {
  const clientsArray = clients.value || []
  console.log('ข้อมูลสำหรับคำนวณสถิติ:', clientsArray)
  
  const totalAccounts = clientsArray.length
  const totalVolumeLots = clientsArray.reduce((sum, client) => sum + (parseFloat(client.volume_lots) || 0), 0)
  const totalVolumeUsd = clientsArray.reduce((sum, client) => sum + (parseFloat(client.volume_mln_usd) || 0), 0)
  const totalReward = clientsArray.reduce((sum, client) => sum + (parseFloat(client.reward_usd) || 0), 0)

  return {
    total_pending: totalAccounts,
    total_amount: totalVolumeLots,
    due_today: totalVolumeUsd.toFixed(4),
    overdue: totalReward.toFixed(4)
  }
})

// แก้ไขส่วนแสดงผล Status ในตาราง
const getStatusColor = (status) => {
  switch (status?.toUpperCase()) {
    case 'ACTIVE':
      return 'text-green-600'
    case 'INACTIVE':
      return 'text-red-600'
    default:
      return 'text-gray-600'
  }
}

const getStatusText = (status) => {
  return status?.toUpperCase() || 'UNKNOWN'
}
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
          <p class="text-sm mt-2">จำนวนข้อมูล: {{ clients.length }}</p>
          <p class="text-sm">ข้อมูลที่กรองแล้ว: {{ filteredClients.length }}</p>
        </div>
        <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead>
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                ID
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Client UID
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Country
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Currency
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Registration Date
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Volume (Lots)
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Volume (USD)
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Rewards (USD)
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 dark:divide-gray-700">
            <template v-if="filteredClients.length > 0">
              <tr v-for="client in filteredClients" :key="client.id || client.client_uid">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ client.id || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ client.client_uid || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="getStatusColor(client.client_status)">
                    {{ getStatusText(client.client_status) }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ client.country || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ client.currency || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ client.reg_date || '-' }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ parseFloat(client.volume_lots || 0).toFixed(2) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-gray-100">{{ parseFloat(client.volume_mln_usd || 0).toFixed(4) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-green-600 dark:text-green-400">
                    {{ parseFloat(client.reward_usd || 0).toFixed(4) }}
                  </div>
                </td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="9" class="px-6 py-4 text-center text-gray-500">
                ไม่พบข้อมูลที่ตรงกับเงื่อนไขการค้นหา
              </td>
            </tr>
          </tbody>
        </table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template> 