<template>
  <TopNavBar />
<<<<<<< HEAD
    <Head title="ค้นหาข้อมูลลูกค้า" />
=======
>>>>>>> e037524a44ff4e6eba521fa3c56060db3857e60c
    <div class="min-h-screen py-4 sm:py-8 lg:py-12 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
          <!-- Page Title with Animation -->
          <div class="mb-6 lg:mb-8 text-center animate-fade-in">
            <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
              Customer Dashboard
            </h1>
        <p class="mt-2 lg:mt-3 text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400">
              View and analyze customer statistics and performance metrics
            </p>
          </div>

          <!-- Customer Partner Label -->
      <div class="mb-4 lg:mb-2 text-xl sm:text-2xl lg:text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in text-center lg:text-left">
            Customer Management
      </div>

      <!-- Filter Section (XM style) -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl p-4 sm:p-6 lg:p-8 mb-6 lg:mb-8 border border-white/20 dark:border-slate-700/20 transform hover:scale-[1.01] lg:hover:scale-[1.02] transition-all duration-300">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-6 items-end">
          <div class="col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 9h2M12 17h.01" />
                    </svg>
                Client Account
              </span>
            </label>
            <input
              type="text"
              v-model="searchAccount"
              placeholder="ค้นหา Client Account, Client UID, Partner Account, หรือ Trader ID"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
            >
                  </div>
          <div class="col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                Actions
              </span>
            </label>
            <button
              @click="resetFilters"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-gray-300 via-gray-200 to-gray-100 text-gray-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm sm:text-base"
            >
              Reset
            </button>
                  </div>
                </div>
              </div>

      <!-- Statistics Cards (XM style) -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Total Customers</p>
              <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ formatNumber(filteredStats.total_customers) }}
              </p>
                </div>
            <div class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0">
              <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                  </div>
                </div>
              </div>
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Active Customers</p>
              <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ formatNumber(filteredStats.active_customers) }}
              </p>
                </div>
            <div class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0">
              <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                  </div>
                </div>
              </div>
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Total Reward (USD)</p>
              <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                ${{ formatNumber(filteredStats.total_reward_usd) }}
              </p>
            </div>
            <div class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0">
              <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                  </div>
                </div>
              </div>
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Total Rebate (USD)</p>
              <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                ${{ formatNumber(filteredStats.total_rebate_usd) }}
              </p>
            </div>
            <div class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0">
              <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                  </svg>
            </div>
                </div>
              </div>
            </div>

      <!-- Customers Table (XM style) -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl border border-white/20 dark:border-slate-700/20">
        <div class="px-4 sm:px-6 lg:px-8 py-4 lg:py-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
          <div>
            <h3 class="text-xl lg:text-2xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
              Customers List
            </h3>
            <p class="mt-1 lg:mt-2 text-sm lg:text-base text-gray-600 dark:text-gray-400">
              Detailed information about all customers and their current status
            </p>
          </div>
          <div class="flex gap-2">
            <button
              @click="fetchData"
              class="group w-full sm:w-auto px-6 lg:px-8 py-2.5 lg:py-3 rounded-xl bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm lg:text-base"
            >
              <span class="flex items-center justify-center gap-2">
                <svg class="w-4 h-4 lg:w-5 lg:h-5 transform group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                <span class="hidden sm:inline">Fetch Data</span>
                <span class="sm:hidden">Fetch</span>
              </span>
            </button>
              </div>
            </div>

        <div class="border-t border-blue-100/20 dark:border-slate-700/20">
              <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-blue-100/20 dark:divide-slate-700/20">
              <thead class="bg-blue-50/50 dark:bg-slate-700/50">
                <tr>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Client UID</th>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Account</th>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Country</th>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Reward (USD)</th>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Rebate (USD)</th>
                  <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Owner</th>
                    </tr>
                  </thead>
              <tbody class="bg-white/50 dark:bg-slate-800/50 divide-y divide-blue-100/20 dark:divide-slate-700/20">
                <!-- Show prompt if searchAccount is empty -->
                <tr v-if="!searchAccount.trim()">
                  <td colspan="7" class="px-3 sm:px-6 py-12 text-center text-gray-400 dark:text-gray-500">
                    กรุณากรอก Client Account หรือ Trader ID เพื่อค้นหาข้อมูลลูกค้า
                  </td>
                </tr>
                <!-- Loading State -->
                <tr v-else-if="isLoading" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50">
                  <td colspan="7" class="px-3 sm:px-6 py-8 lg:py-12 text-center">
                    <div class="flex items-center justify-center">
                      <svg class="animate-spin h-6 w-6 lg:h-8 lg:w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="ml-3 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Loading data...</span>
                        </div>
                      </td>
                </tr>
                <!-- No Data State -->
                <tr v-else-if="paginatedCustomers.length === 0" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50">
                  <td colspan="7" class="px-3 sm:px-6 py-8 lg:py-12 text-center text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                    ไม่พบข้อมูลลูกค้าที่ค้นหา
                  </td>
                </tr>
                <!-- Data Rows -->
                <tr v-else v-for="customer in paginatedCustomers" :key="customer.client_uid" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-white">{{ customer.client_uid }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300">{{ customer.client_id }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300">{{ customer.country || '-' }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                    <span :class="[
                      'px-2 sm:px-4 py-1 sm:py-1.5 inline-flex items-center gap-1 sm:gap-1.5 text-xs font-semibold rounded-full',
                      customer.client_status === 'ACTIVE'
                        ? 'bg-green-100/80 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                        : 'bg-red-100/80 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                    ]">
                      <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span :class="[
                          'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                          customer.client_status === 'ACTIVE' ? 'bg-green-400' : 'bg-red-400'
                        ]"></span>
                        <span :class="[
                          'relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2',
                          customer.client_status === 'ACTIVE' ? 'bg-green-500' : 'bg-red-500'
                        ]"></span>
                      </span>
                      <span class="hidden sm:inline">{{ customer.client_status === 'ACTIVE' ? 'Active' : 'Inactive' }}</span>
                      <span class="sm:hidden">{{ customer.client_status === 'ACTIVE' ? 'A' : 'I' }}</span>
                        </span>
                      </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300">${{ formatNumber(customer.total_reward_usd) }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300">${{ formatNumber(customer.rebate_amount_usd) }}</td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300">{{ customer.owner?.name || '-' }}</td>
                    </tr>
                  </tbody>
                </table>
          </div>

          <!-- Pagination Controls -->
          <div class="px-4 sm:px-6 py-4 bg-white/50 dark:bg-slate-800/50 border-t border-blue-100/20 dark:border-slate-700/20">
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
              <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
                <span>Showing</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ startIndex + 1 }}</span>
                <span>to</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ endIndex }}</span>
                <span>of</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ totalItems }}</span>
                <span class="hidden sm:inline">entries</span>
              </div>

              <div class="flex items-center gap-1 sm:gap-2">
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                    border border-blue-100 dark:border-slate-600"
                >
                  <span class="hidden sm:inline">Previous</span>
                  <span class="sm:hidden">Prev</span>
                </button>
                <div class="flex items-center gap-1">
                  <button
                    v-for="page in displayedPages"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                      'px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200',
                      currentPage === page
                        ? 'bg-blue-500 text-white hover:bg-blue-600'
                        : 'bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600'
                    ]"
                  >
                    {{ page }}
                  </button>
                </div>
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                    border border-blue-100 dark:border-slate-600"
                >
                  <span class="hidden sm:inline">Next</span>
                  <span class="sm:hidden">→</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <BottomNavBar :menu="menuItems" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import TopNavBar from '@/Components/TopNavBar.vue';
import BottomNavBar from '@/Components/BottomNavBar.vue';

const customers = ref([])
const currentPage = ref(1)
const itemsPerPage = 10
const isLoading = ref(false)
const error = ref(null)
const stats = ref({
  total_customers: 0,
  active_customers: 0,
  total_reward_usd: 0,
  total_rebate_usd: 0
})
const searchAccount = ref("");

// Setup axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      window.location.reload();
    }
    return Promise.reject(error);
  }
);

const totalItems = computed(() => customers.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, totalItems.value))
const filteredCustomers = computed(() => {
  const search = searchAccount.value.trim();
  if (!search) return [];
  const searchLower = search.toLowerCase();
  // Debug: log all customers that contain the search term in any field
  const matches = customers.value.filter(customer =>
    Object.values(customer).some(v => v && String(v).toLowerCase().includes(searchLower))
  );
  if (matches.length > 0) {
    console.log('ลูกค้าที่พบเลขค้นหา', search, ':', matches);
  } else {
    console.log('ไม่พบลูกค้าที่มีเลข', search, 'ใน field ใดๆ');
  }
  return customers.value.filter(customer => {
    return (
      (customer.client_uid && String(customer.client_uid).toLowerCase().includes(searchLower)) ||
      (customer.client_id && String(customer.client_id).toLowerCase().includes(searchLower)) ||
      (customer.client_account && String(customer.client_account).toLowerCase().includes(searchLower)) ||
      (customer.partner_account && String(customer.partner_account).toLowerCase().includes(searchLower)) ||
      (customer.traderId && String(customer.traderId).toLowerCase().includes(searchLower)) ||
      (customer.client_name && String(customer.client_name).toLowerCase().includes(searchLower)) ||
      (customer.account_number && String(customer.account_number).toLowerCase().includes(searchLower)) ||
      (customer.login && String(customer.login).toLowerCase().includes(searchLower)) ||
      (customer.exness_id && String(customer.exness_id).toLowerCase().includes(searchLower))
    );
  });
});

const paginatedCustomers = computed(() => {
  return filteredCustomers.value.slice(startIndex.value, endIndex.value)
})

const filteredStats = computed(() => {
  if (!searchAccount.value.trim() || filteredCustomers.value.length === 0) {
    return {
      total_customers: 0,
      active_customers: 0,
      total_reward_usd: 0,
      total_rebate_usd: 0
    }
  }
  return {
    total_customers: filteredCustomers.value.length,
    active_customers: filteredCustomers.value.filter(c => c.client_status === 'ACTIVE').length,
    total_reward_usd: filteredCustomers.value.reduce((sum, c) => sum + (c.total_reward_usd || 0), 0),
    total_rebate_usd: filteredCustomers.value.reduce((sum, c) => sum + (c.rebate_amount_usd || 0), 0)
  }
})

const displayedPages = computed(() => {
  const pages = []
  const maxDisplayedPages = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxDisplayedPages / 2))
  let end = Math.min(totalPages.value, start + maxDisplayedPages - 1)

  if (end - start + 1 < maxDisplayedPages) {
    start = Math.max(1, end - maxDisplayedPages + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

const goToPage = (page) => {
  currentPage.value = page
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const fetchData = async () => {
  isLoading.value = true
  error.value = null

  try {
    const response = await axios.get('/admin/all-customers', {
      params: {
        startTime: startDate.value,
        endTime: endDate.value
      }
    })
    customers.value = Array.isArray(response.data?.data?.customers)
      ? response.data.data.customers
      : [];
    if (response.data?.data?.stats) {
      stats.value = response.data.data.stats;
    }
    currentPage.value = 1
    // Debug: log the structure of the first customer
    if (customers.value.length > 0) {
      console.log('ตัวอย่างข้อมูลลูกค้า:', customers.value[0]);
    } else {
      console.log('ไม่มีข้อมูลลูกค้า');
    }
  } catch (e) {
    error.value = e.message || 'An error occurred while fetching data'
    customers.value = []
  } finally {
    isLoading.value = false
  }
}

const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US').format(number)
}

function resetFilters() {
  searchAccount.value = "";
}

onMounted(() => {
  fetchData()
})

const menuItems = [
  {
    label: "Dashboard",
    to: "/admin",
    icon: 'mdiViewDashboard'
  },
  {
    label: "Customers",
    to: "/admin/customers",
    icon: 'mdiAccountGroup'
  },
  {
    label: "XM",
    to: "/admin/xm",
    icon: 'mdiChartBar'
  },
  // ... add more as needed
];
</script>

<style scoped>
.backdrop-blur-lg {
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.hover\:scale-105:hover {
  transform: scale(1.05);
}
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}
.dark input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}
@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.animate-fade-in {
    animation: fade-in 0.6s ease-out forwards;
}
@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}
</style>

