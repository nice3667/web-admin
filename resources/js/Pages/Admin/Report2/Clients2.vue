<script setup>
import { Head, router } from "@inertiajs/vue3";
import { mdiClockOutline, mdiAlertBoxOutline, mdiAccountGroup, mdiChartLine, mdiCurrencyUsd, mdiGift } from "@mdi/js";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, computed, watch } from "vue";

const props = defineProps({
  clients: {
    type: Array,
    required: true,
    default: () => []
  },
  stats: {
    type: Object,
    required: true,
    default: () => ({
      total_pending: 0,
      total_amount: 0,
      due_today: 0,
      overdue: 0,
      total_client_uids: 0
    })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  user_email: {
    type: String,
    default: '',
  },
  data_source: {
    type: String,
    default: 'database',
  },
  error: {
    type: String,
    default: '',
  }
});

// Local filters for UI
const filters = ref({
  search: props.filters.search || "",
  status: props.filters.status || "all",
  date_range: {
    start: props.filters.start_date || "",
    end: props.filters.end_date || "",
  },
});

// Apply filters and navigate
const applyFilters = () => {
  const params = {};
  
  if (filters.value.search) {
    params.search = filters.value.search;
  }
  
  if (filters.value.status !== 'all') {
    params.status = filters.value.status;
  }
  
  if (filters.value.date_range.start) {
    params.start_date = filters.value.date_range.start;
  }
  
  if (filters.value.date_range.end) {
    params.end_date = filters.value.date_range.end;
  }
  
  router.get('/admin/reports2/clients2', params, {
    preserveState: true,
    preserveScroll: true
  });
};

const resetFilters = () => {
  filters.value = {
    search: "",
    status: "all",
    date_range: {
      start: "",
      end: "",
    },
  };
  
  router.get('/admin/reports2/clients2', {}, {
    preserveState: true,
    preserveScroll: true
  });
};

// Computed properties for filtered data
const filteredClients = computed(() => {
  let result = props.clients || [];

  // ค้นหา (เฉพาะ client_uid)
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase();
    result = result.filter((client) =>
      client.client_uid?.toLowerCase().includes(searchLower)
    );
  }

  // กรองตามสถานะ
  if (filters.value.status !== "all") {
    const statusFilter = filters.value.status.toUpperCase();
    result = result.filter((client) => {
      const clientStatus = (client.client_status || "").toUpperCase();
      return clientStatus === statusFilter;
    });
  }

  // กรองตามช่วงวันที่
  if (filters.value.date_range.start || filters.value.date_range.end) {
    result = result.filter((client) => {
      const regDate = new Date(client.reg_date);
      const start = filters.value.date_range.start
        ? new Date(filters.value.date_range.start)
        : null;
      const end = filters.value.date_range.end
        ? new Date(filters.value.date_range.end)
        : null;

      if (start && regDate < start) return false;
      if (end && regDate > end) return false;
      return true;
    });
  }

  return result;
});

// Computed properties for stats
const computedStats = computed(() => {
  return {
    total_pending: props.stats.total_pending || props.stats.total_client_uids || 0,
    total_amount: Number(props.stats.total_amount || 0).toFixed(4),
    due_today: Number(props.stats.due_today || 0).toFixed(4),
    overdue: Number(props.stats.overdue || 0).toFixed(4),
  };
});

// Helper functions for status display
const getStatusColor = (status) => {
  switch (status?.toUpperCase()) {
    case "ACTIVE":
      return "text-green-600";
    case "INACTIVE":
      return "text-red-600";
    default:
      return "text-gray-600";
  }
};

const getStatusText = (status) => {
  return status?.toUpperCase() || "UNKNOWN";
};

// Watch for props changes to update filters
watch(() => props.filters, (newFilters) => {
  filters.value = {
    search: newFilters.search || "",
    status: newFilters.status || "all",
    date_range: {
      start: newFilters.start_date || "",
      end: newFilters.end_date || "",
    },
  };
}, { immediate: true });
</script>

<template>
  <LayoutAuthenticated>
    <Head title="ลูกค้า" />
    <SectionMain>
      <SectionTitleLineWithButton  title="รายงานลูกค้า" main>
        <template #button>
          <div class="flex items-center space-x-4">
            <!-- User Info -->
            <div class="flex items-center space-x-2 px-4 py-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
              <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
              </svg>
              <span class="text-sm font-medium text-blue-800 dark:text-blue-200">{{ user_email }}</span>
            </div>
            
            <!-- Data Source -->
            <div class="flex items-center space-x-2 px-4 py-2 rounded-lg" :class="data_source === 'exness_api' ? 'bg-green-100 dark:bg-green-900' : 'bg-yellow-100 dark:bg-yellow-900'">
              <svg class="w-5 h-5" :class="data_source === 'exness_api' ? 'text-green-600 dark:text-green-400' : 'text-yellow-600 dark:text-yellow-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" v-if="data_source === 'exness_api'"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.664-.833-2.464 0L4.732 16.5c-.77.833.192 2.5 1.732 2.5z" v-else></path>
              </svg>
              <span class="text-sm font-medium" :class="data_source === 'exness_api' ? 'text-green-800 dark:text-green-200' : 'text-yellow-800 dark:text-yellow-200'">
                {{ data_source === 'exness_api' ? 'Exness API' : 'Database' }}
              </span>
            </div>
            
            <BaseButton
              :icon="mdiChartLine"
              label="อัปเดตข้อมูล"
              color="success"
              @click="router.get('/admin/reports2/clients2')"
            />
          </div>
        </template>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="props.error" color="danger" :icon="mdiAlertBoxOutline">
        {{ props.error }}
      </NotificationBar>

      <!-- Enhanced Statistics Cards -->
      <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
        <!-- Client UID Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">จำนวน Client UID</span>
              <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ computedStats.total_pending }}</span>
              <span class="text-xs text-gray-500 mt-1">ลูกค้า</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Volume (lots) Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-green-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Volume (lots)</span>
              <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ computedStats.total_amount }}</span>
              <span class="text-xs text-gray-500 mt-1">ยอดรวม</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Volume (USD) Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Volume (USD)</span>
              <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ computedStats.due_today }}</span>
              <span class="text-xs text-gray-500 mt-1">มูลค่า</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Reward (USD) Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Reward (USD)</span>
              <span class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ computedStats.overdue }}</span>
              <span class="text-xs text-gray-500 mt-1">กำไรรวม</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
      </div>

      <!-- Enhanced Search and Filter Section -->
      <CardBox class="mb-8 shadow-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 transform hover:shadow-2xl transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">ค้นหาและกรองข้อมูล</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">กรองข้อมูลลูกค้าตามเงื่อนไขที่ต้องการ</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full text-sm font-semibold shadow-lg">
              แสดง {{ filteredClients.length }} รายการ
            </span>
          </div>
        </div>
        
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
          <!-- Search Input -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              ค้นหา Client UID
            </label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="กรอก Client UID..."
                class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
                @input="applyFilters"
              />
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>

          <!-- Status Filter -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              สถานะ
            </label>
            <select
              v-model="filters.status"
              class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
              @change="applyFilters"
            >
              <option value="all">ทุกสถานะ</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="INACTIVE">INACTIVE</option>
              <option value="LEFT">LEFT</option>
            </select>
          </div>

          <!-- Date Range -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
              </svg>
              ช่วงวันที่
            </label>
            <input
              v-model="filters.date_range.start"
              type="date"
              class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
              @change="applyFilters"
            />
          </div>

          <!-- Reset Button -->
          <div class="flex items-end">
            <BaseButton 
              color="gray" 
              label="รีเซ็ต" 
              :icon="mdiAlertBoxOutline"
              @click="resetFilters"
              class="w-full py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
            />
          </div>
        </div>
      </CardBox>

      <!-- Enhanced Data Table -->
      <CardBox class="shadow-2xl border-0 overflow-hidden transform hover:shadow-3xl transition-all duration-300" has-table>
        <div class="flex items-center justify-between mb-6 p-8 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">รายการ Client UID</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">ข้อมูลลูกค้าทั้งหมด {{ props.clients.length }} รายการ</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full text-sm font-semibold shadow-lg">
              {{ filteredClients.length }} รายการ
            </span>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="!props.clients.length" class="p-16 text-center">
          <div class="mx-auto w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-full flex items-center justify-center mb-8 shadow-lg">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">ไม่พบข้อมูล</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg">ลองปรับเปลี่ยนเงื่อนไขการค้นหา</p>
          <div class="space-y-2 text-sm text-gray-500">
            <p>จำนวนข้อมูลทั้งหมด: {{ props.clients.length }} รายการ</p>
            <p>ข้อมูลที่กรองแล้ว: {{ filteredClients.length }} รายการ</p>
          </div>
        </div>
        
        <!-- Data Table -->
        <div v-else class="overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                <tr>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">Client UID</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">สถานะ</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">Rewards (USD)</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">Rebate Amount (USD)</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="client in filteredClients" :key="client.client_uid" 
                    class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all duration-300 transform hover:scale-[1.01]">
                  <td class="px-8 py-8 whitespace-nowrap">
                    <div>
                      <div class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ client.client_uid }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        Account: {{ client.client_id }}
                      </div>
                    </div>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap">
                    <span :class="[
                      'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                      client.client_status === 'ACTIVE' ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                      client.client_status === 'INACTIVE' ? 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200' :
                      client.client_status === 'LEFT' ? 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 dark:from-yellow-900 dark:to-yellow-800 dark:text-yellow-200' :
                      'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-200'
                    ]">
                      <svg v-if="client.client_status === 'ACTIVE'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                      </svg>
                      <svg v-else-if="client.client_status === 'INACTIVE'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                      </svg>
                      <svg v-else-if="client.client_status === 'LEFT'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                      </svg>
                      {{ getStatusText(client.client_status) }}
                    </span>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                      ${{ Number(client.reward_usd || 0).toFixed(4) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      Volume: {{ Number(client.volume_lots || 0).toFixed(4) }} lots
                    </div>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap">
                    <div class="text-lg font-bold text-purple-600 dark:text-purple-400">
                      {{ client.rebate_amount_usd !== undefined ? '$' + Number(client.rebate_amount_usd).toFixed(4) : "-" }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      Volume USD: ${{ Number(client.volume_mln_usd || 0).toFixed(4) }}
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
