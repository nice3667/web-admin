<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiAccountMultiple,
  mdiAlertBoxOutline,
  mdiCash,
  mdiCurrencyUsd,
  mdiGift,
} from "@mdi/js"
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue"
import SectionMain from "@/Components/SectionMain.vue"
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import CardBox from "@/Components/CardBox.vue"
import CardBoxWidget from "@/Components/CardBoxWidget.vue"
import NotificationBar from "@/Components/NotificationBar.vue"
import BaseButton from "@/Components/BaseButton.vue"
import BaseButtons from "@/Components/BaseButtons.vue"
import { ref, computed } from 'vue'

const stats = ref({
  total_clients: 150,
  total_volume_lots: 1250.5,
  total_volume_usd: 1250000,
  total_rewards: 25000
})

const clients = ref([
  {
    id: 1,
    name: 'Somchai Jaidee',
    email: 'somchai@example.com',
    phone: '+66812345678',
    status: 'Active',
    progress: 100,
    rewards: 2500,
    comment: 'Top performer',
    rebates: '0.8 pips'
  },
  {
    id: 2,
    name: 'Somsri Rakdee',
    email: 'somsri@example.com',
    phone: '+66823456789',
    status: 'Active',
    progress: 100,
    rewards: 3200,
    comment: 'Consistent trader',
    rebates: '0.7 pips'
  },
  {
    id: 3,
    name: 'Wichai Sangdee',
    email: 'wichai@example.com',
    phone: '+66834567890',
    status: 'Inactive',
    progress: 50,
    rewards: 800,
    comment: 'Needs improvement',
    rebates: '0.5 pips'
  },
  {
    id: 4,
    name: 'Pranee Suksai',
    email: 'pranee@example.com',
    phone: '+66845678901',
    status: 'Active',
    progress: 100,
    rewards: 1900,
    comment: 'Good progress',
    rebates: '0.6 pips'
  },
  {
    id: 5,
    name: 'Sompong Chailee',
    email: 'sompong@example.com',
    phone: '+66856789012',
    status: 'Active',
    progress: 100,
    rewards: 4200,
    comment: 'Excellent results',
    rebates: '0.9 pips'
  },
  {
    id: 6,
    name: 'Ratree Meesuk',
    email: 'ratree@example.com',
    phone: '+66867890123',
    status: 'Inactive',
    progress: 0,
    rewards: 500,
    comment: 'Limited activity',
    rebates: '0.4 pips'
  },
  {
    id: 7,
    name: 'Mongkol Deejai',
    email: 'mongkol@example.com',
    phone: '+66878901234',
    status: 'Active',
    progress: 100,
    rewards: 2800,
    comment: 'Strong performance',
    rebates: '0.7 pips'
  },
  {
    id: 8,
    name: 'Sunee Wandee',
    email: 'sunee@example.com',
    phone: '+66889012345',
    status: 'Active',
    progress: 50,
    rewards: 2300,
    comment: 'Steady growth',
    rebates: '0.6 pips'
  },
  {
    id: 9,
    name: 'Pichai Rukdee',
    email: 'pichai@example.com',
    phone: '+66890123456',
    status: 'Active',
    progress: 50,
    rewards: 1700,
    comment: 'Good potential',
    rebates: '0.5 pips'
  },
  {
    id: 10,
    name: 'Malee Sombat',
    email: 'malee@example.com',
    phone: '+66901234567',
    status: 'Inactive',
    progress: 0,
    rewards: 600,
    comment: 'Requires attention',
    rebates: '0.4 pips'
  },
  {
    id: 11,
    name: 'Somchai Jaidee',
    email: 'somchai@example.com',
    phone: '+66812345678',
    status: 'Inactive',
    progress: 0,
    rewards: 600,
    comment: 'Requires attention',
    rebates: '0.4 pips'
  },
  {
    id: 12,
    name: 'Somchai Jaidee',
    email: 'somchai@example.com',
    phone: '+66812345678',
    status: 'Inactive',
    progress: 0,
    rewards: 600,
    comment: 'Requires attention',
    rebates: '0.4 pips'
  },
  {
    id: 13,
    name: 'Somchai Jaidee',
    email: 'somchai@example.com',
    phone: '+66812345678',
    status: 'Inactive',
    progress: 0,
    rewards: 600,
    comment: 'Requires attention',
    rebates: '0.4 pips'
  },
  {
    id: 14,
    name: 'Somchai Jaidee',
    email: 'maleei@example.com',
    phone: '+66812345678',
    status: 'Inactive',
    progress: 0,
    rewards: 600,
    comment: 'Requires attention',
    rebates: '0.4 pips'
  },
  {
    id: 15,
    name: 'Somchai Jaidee',
    email: 'maleei@example.com',
    phone: '+66812345678',
    status: 'Inactive',
    progress: 0,
    rewards: 600,
    comment: 'Requires attention',
    rebates: '0.4 pips'
  }
])

const filters = ref({
  search: '',
  status: 'all',
  date_from: '',
  date_to: ''
})

// Update the filteredClients computed property to search by ID
const filteredClients = computed(() => {
  let filtered = [...clients.value]
  
  // Apply search filter by Client ID
  if (filters.value.search) {
    filtered = filtered.filter(client => 
      client.id.toString().includes(filters.value.search)
    )
  }
  
  // Apply status filter
  if (filters.value.status !== 'all') {
    filtered = filtered.filter(client => 
      client.status.toLowerCase() === filters.value.status.toLowerCase()
    )
  }
  
  return filtered
})

// Add pagination state
const currentPage = ref(1)
const itemsPerPage = 10

// Add computed property for paginated clients
const paginatedClients = computed(() => {
  const startIndex = (currentPage.value - 1) * itemsPerPage
  const endIndex = startIndex + itemsPerPage
  return filteredClients.value.slice(startIndex, endIndex)
})

// Add computed property for total pages
const totalPages = computed(() => {
  return Math.ceil(filteredClients.value.length / itemsPerPage)
})

// Add method to change page
const changePage = (page) => {
  currentPage.value = page
}

// Add a method to get progress color based on level
const getProgressColor = (progress) => {
  if (progress === 100) return 'bg-green-600'
  if (progress === 50) return 'bg-yellow-600'
  return 'bg-red-600'
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Client Reports" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountMultiple"
        title="Client Reports"
        main
      >
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash.message"
        color="success"
        :icon="mdiAlertBoxOutline"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <!-- Statistics Widgets -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <CardBoxWidget
          :icon="mdiAccountMultiple"
          :number="stats.total_clients"
          label="Total Clients"
          color="text-blue-500"
        />
        <CardBoxWidget
          :icon="mdiCash"
          :number="stats.total_volume_lots"
          label="Volume (lots)"
          :suffix="'lots'"
          color="text-green-500"
        />
        <CardBoxWidget
          :icon="mdiCurrencyUsd"
          :number="stats.total_volume_usd"
          label="Volume (Min. USD)"
          prefix="$"
          color="text-yellow-500"
        />
        <CardBoxWidget
          :icon="mdiGift"
          :number="stats.total_rewards"
          label="Rewards"
          prefix="$"
          color="text-purple-500"
        />
      </div>

      <CardBox class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">ค้นหา Client ID</label>
            <input
              v-model="filters.search"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="ระบุ Client ID..."
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
          </div>
          <div class="md:col-span-1 flex items-end justify-end">
  <BaseButtons>
    <BaseButton
      color="warning"
      label="Reset"
      @click="filters = { search: '', status: 'all', date_from: '', date_to: '' }"
    />
  </BaseButtons>
</div>

        </div>
       
      </CardBox>

      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th>Client ID</th>
              <th>Status</th>
              <th>Client Progress</th>
              <th>Rewards</th>
              <th>Comment</th>
              <th>Rebates</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="client in paginatedClients" :key="client.id">
              <td data-label="Client ID">{{ client.id }}</td>
              <td data-label="Status">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-green-100 text-green-800': client.status === 'Active',
                    'bg-red-100 text-red-800': client.status === 'Inactive'
                  }"
                >
                  {{ client.status }}
                </span>
              </td>
              <td data-label="Client Progress">
                <div class="flex items-center">
                  <div class="w-full bg-gray-200 rounded-full h-2.5 mr-2">
                    <div 
                      class="h-2.5 rounded-full" 
                      :class="getProgressColor(client.progress)"
                      :style="{ width: client.progress + '%' }"
                    ></div>
                  </div>
                  <span class="text-sm">{{ client.progress }}%</span>
                </div>
              </td>
              <td data-label="Rewards">${{ client.rewards }}</td>
              <td data-label="Comment">{{ client.comment }}</td>
              <td data-label="Rebates">{{ client.rebates }}</td>
              <td class="before:hidden lg:w-1">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    label="View"
                  />
                  <BaseButton
                    color="danger"
                    label="Delete"
                  />
                </BaseButtons>
              </td>
            </tr>
          </tbody>
        </table>
        <!-- Add pagination controls -->
        <div class="mt-6 flex justify-between items-center">
          <div class="text-gray-600 dark:text-gray-400 p-3">
              Showing {{ ((currentPage - 1) * itemsPerPage) + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredClients.length) }} of {{ filteredClients.length }} entries
          </div>
          <div class="flex space-x-2 p-3">
            <BaseButton
              :color="currentPage === 1 ? 'gray' : 'info'"
              :disabled="currentPage === 1"
              label="Previous"
              @click="changePage(currentPage - 1)"
            />
            <template v-for="page in totalPages" :key="page">
              <BaseButton
                :color="currentPage === page ? 'info' : 'white'"
                :label="page.toString()"
                @click="changePage(page)"
              />
            </template>
            <BaseButton
              :color="currentPage === totalPages ? 'gray' : 'info'"
              :disabled="currentPage === totalPages"
              label="Next"
              @click="changePage(currentPage + 1)"
            />
          </div>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template> 