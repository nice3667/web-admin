<script setup>
import { Head, router } from "@inertiajs/vue3";
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
} from "@mdi/js";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, onMounted, computed, watch } from "vue";
import FormControl from "@/Components/FormControl.vue";
import Pagination from "@/Components/Admin/Pagination.vue";

const props = defineProps({
  clients: {
    type: Object,
    required: true,
    default: () => ({})
  },
  stats: {
    type: Object,
    required: true,
    default: () => ({
      total_accounts: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_profit: 0,
      total_client_uids: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({}),
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
  },
});

const isModalActive = ref(false);

// Search filters
const tempFilters = ref({
  partner_account: "",
  client_uid: "",
  client_country: "",
  client_status: "",
  reg_date: "",
  kyc_passed: "",
});

const filters = ref({
  partner_account: "",
  client_uid: "",
  client_country: "",
  client_status: "",
  reg_date: "",
  kyc_passed: "",
});

// Initialize filters from props
onMounted(() => {
  tempFilters.value = {
    partner_account: props.filters.partner_account || "",
    client_uid: props.filters.client_uid || "",
    client_country: props.filters.client_country || "",
    client_status: props.filters.client_status || "",
    reg_date: props.filters.reg_date || "",
    kyc_passed: props.filters.kyc_passed || "",
  };
  filters.value = { ...tempFilters.value };
});

// Computed properties for data
const accounts = computed(() => {
  return props.clients.data || [];
});

// Apply search filters
const applySearch = () => {
  filters.value = { ...tempFilters.value };
  router.get(
    "/admin/reports2/client-account2",
    {
      ...filters.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
  isModalActive.value = false;
};

// Reset filters
const resetFilters = () => {
  tempFilters.value = {
    partner_account: "",
    client_uid: "",
    client_country: "",
    client_status: "",
    reg_date: "",
    kyc_passed: "",
  };
  filters.value = { ...tempFilters.value };
  router.get(
    "/admin/reports2/client-account2",
    {},
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Format functions
const formatNumber = (value) => {
  if (value === null || value === undefined) return 0;
  return Number(value).toFixed(2);
};

const formatCurrency = (value) => {
  if (value === null || value === undefined) return 0;
  return Number(value).toFixed(2);
};

const formatDate = (date) => {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("th-TH", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

// Format country code to name
const formatCountry = (code) => {
  if (!code || code === "-" || code === "") return "-";
  // Return full country name if found, otherwise show the code with a prefix
  return countryNames[code] || `[${code}]`;
};

// Get status class
const getStatusClass = (status) => {
  const classes = {
    ACTIVE: "bg-green-100 text-green-800",
    INACTIVE: "bg-red-100 text-red-800",
    PENDING: "bg-yellow-100 text-yellow-800",
  };
  return classes[status] || "bg-gray-100 text-gray-800";
};

// Add country code to name mapping
const countryNames = {
  TH: "Thailand",
  US: "United States",
  GB: "United Kingdom",
  CN: "China",
  JP: "Japan",
  KR: "South Korea",
  SG: "Singapore",
  MY: "Malaysia",
  ID: "Indonesia",
  VN: "Vietnam",
  PH: "Philippines",
  MM: "Myanmar",
  KH: "Cambodia",
  LA: "Laos",
  BN: "Brunei",
  // European countries
  DE: "Germany",
  FR: "France",
  IT: "Italy",
  ES: "Spain",
  NL: "Netherlands",
  BE: "Belgium",
  AT: "Austria",
  CH: "Switzerland",
  SE: "Sweden",
  NO: "Norway",
  DK: "Denmark",
  FI: "Finland",
  PT: "Portugal",
  IE: "Ireland",
  PL: "Poland",
  CZ: "Czech Republic",
  HU: "Hungary",
  RO: "Romania",
  BG: "Bulgaria",
  HR: "Croatia",
  GR: "Greece",
  // Americas
  CA: "Canada",
  MX: "Mexico",
  BR: "Brazil",
  AR: "Argentina",
  CL: "Chile",
  CO: "Colombia",
  PE: "Peru",
  // Middle East & Africa
  AE: "United Arab Emirates",
  SA: "Saudi Arabia",
  EG: "Egypt",
  ZA: "South Africa",
  NG: "Nigeria",
  AO: "Angola",
  KE: "Kenya",
  GH: "Ghana",
  MA: "Morocco",
  TN: "Tunisia",
  ET: "Ethiopia",
  UG: "Uganda",
  TZ: "Tanzania",
  ZW: "Zimbabwe",
  BW: "Botswana",
  MW: "Malawi",
  IL: "Israel",
  TR: "Turkey",
  // Asia Pacific
  AU: "Australia",
  NZ: "New Zealand",
  IN: "India",
  PK: "Pakistan",
  BD: "Bangladesh",
  LK: "Sri Lanka",
  HK: "Hong Kong",
  TW: "Taiwan",
  // Additional Southeast Asian countries
  BT: "Bhutan",
  NP: "Nepal",
  MV: "Maldives",
  // Other common countries
  RU: "Russia",
  UA: "Ukraine",
  BY: "Belarus",
  KZ: "Kazakhstan",
  UZ: "Uzbekistan",
};

// Computed properties for stats
const totalVolumeLots = computed(() => {
  return Number(props.stats?.total_volume_lots || 0);
});

const totalVolumeUsd = computed(() => {
  return Number(props.stats?.total_volume_usd || 0);
});

const totalReward = computed(() => {
  return Number(props.stats?.total_profit || 0);
});

const formattedStats = computed(() => {
  const stats = props.stats || {};
  return {
    total_accounts: stats.total_accounts ?? 0,
    total_volume_lots: Number(stats.total_volume_lots ?? 0).toFixed(4),
    total_volume_usd: Number(stats.total_volume_usd ?? 0).toFixed(4),
    total_profit: Number(stats.total_profit ?? 0).toFixed(4),
    total_client_uids: stats.total_client_uids ?? 0
  };
});

// Watch for props changes to update temp filters
watch(
  () => props.filters,
  (newFilters) => {
    tempFilters.value = {
      partner_account: newFilters.partner_account || "",
      client_uid: newFilters.client_uid || "",
      client_country: newFilters.client_country || "",
      client_status: newFilters.client_status || "",
      reg_date: newFilters.reg_date || "",
      kyc_passed: newFilters.kyc_passed || "",
    };
    filters.value = { ...tempFilters.value };
  },
  { immediate: true }
);
</script>

<template>
  <LayoutAuthenticated>
    <Head title="บัญชีลูกค้า" />
    
    <SectionMain>
      <SectionTitleLineWithButton
        title="บัญชีลูกค้า"
        main
      >
        <template #right>
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
          </div>
        </template>
      </SectionTitleLineWithButton>

      <!-- Error Message -->
      <NotificationBar v-if="error" color="danger" :icon="mdiAlertBoxOutline" class="mb-6">
        {{ error }}
      </NotificationBar>

      <!-- Enhanced Statistics Cards -->
      <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
        <!-- Client UID Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">จำนวน Client UID</span>
              <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ formattedStats.total_client_uids }}</span>
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

        <!-- Total Accounts Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-green-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">จำนวนบัญชีทั้งหมด</span>
              <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ formattedStats.total_accounts }}</span>
              <span class="text-xs text-gray-500 mt-1">บัญชี</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Volume (lots) Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Volume (lots)</span>
              <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">{{ formattedStats.total_volume_lots }}</span>
              <span class="text-xs text-gray-500 mt-1">ยอดรวม</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Volume (USD) Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Volume (USD)</span>
              <span class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ formattedStats.total_volume_usd }}</span>
              <span class="text-xs text-gray-500 mt-1">มูลค่า</span>
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

        <!-- Profit (USD) Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-red-500 to-red-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">กำไร (USD)</span>
              <span class="text-3xl font-bold text-red-600 dark:text-red-400">{{ formattedStats.total_profit }}</span>
              <span class="text-xs text-gray-500 mt-1">รายได้</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
      </div>

      <!-- Enhanced Search and Filter Section -->
      <CardBox class="search-filter-section mb-8 shadow-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 transform hover:shadow-2xl transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">ค้นหาและกรองข้อมูล</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">กรองข้อมูลบัญชีลูกค้าตามเงื่อนไขที่ต้องการ</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full text-sm font-semibold shadow-lg">
              แสดง {{ accounts.length }} รายการ
            </span>
          </div>
        </div>
        
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
          <!-- Search Client UID -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              ค้นหา Client UID
            </label>
            <div class="relative">
              <input
                v-model="tempFilters.client_uid"
                type="text"
                placeholder="กรอก Client UID..."
                class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
                @input="applySearch"
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
              v-model="tempFilters.client_status"
              class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
              @change="applySearch"
            >
              <option value="">ทุกสถานะ</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="INACTIVE">INACTIVE</option>
              <option value="PENDING">PENDING</option>
            </select>
          </div>

          <!-- Advanced Search Button -->
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
              ค้นหาขั้นสูง
            </label>
            <BaseButton
              :icon="mdiMagnify"
              label="ค้นหา"
              color="info"
              @click="isModalActive = true"
              class="w-full py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
            />
          </div>

          <!-- Reset Button -->
          <div class="flex items-end">
            <BaseButton 
              color="gray" 
              label="รีเซ็ต" 
              :icon="mdiRefresh"
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
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">รายการบัญชีลูกค้า</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">ข้อมูลบัญชีทั้งหมด {{ accounts.length }} รายการ</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-full text-sm font-semibold shadow-lg">
              {{ accounts.length }} รายการ
            </span>
          </div>
        </div>
        
        <!-- Empty State -->
        <div v-if="!accounts.length" class="p-16 text-center">
          <div class="mx-auto w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-full flex items-center justify-center mb-8 shadow-lg">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">ไม่พบข้อมูล</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg">ลองปรับเปลี่ยนเงื่อนไขการค้นหา</p>
          <div class="space-y-2 text-sm text-gray-500">
            <p>จำนวนข้อมูลทั้งหมด: {{ accounts.length }} รายการ</p>
          </div>
        </div>
        
        <!-- Data Table -->
        <div v-else class="overflow-x-auto">
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
                    <span class="text-sm font-bold">Partner Account</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">Client UID</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">วันที่ลงทะเบียน</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">ประเทศ</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">Volume (lots)</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">Volume (USD)</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-red-500 to-red-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">Reward (USD)</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">สถานะ</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">KYC</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-cyan-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">FTD</span>
                  </div>
                </th>
                <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                  <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-lime-500 to-lime-600 rounded-xl flex items-center justify-center shadow-lg">
                      <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                      </svg>
                    </div>
                    <span class="text-sm font-bold">FTT</span>
                  </div>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
              <tr v-for="account in accounts" :key="account?.client_uid || Math.random()"
                  class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all duration-300 transform hover:scale-[1.01]">
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ account?.partner_account || "-" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-sm font-bold text-gray-900 dark:text-white">
                    {{ account?.client_uid || "-" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ account?.reg_date ? formatDate(account.reg_date) : "-" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-sm text-gray-900 dark:text-white">
                    {{ account?.client_country ? formatCountry(account.client_country) : "-" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-lg font-bold text-indigo-600 dark:text-indigo-400">
                    {{ account?.volume_lots ? formatNumber(account.volume_lots) : "0" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-lg font-bold text-yellow-600 dark:text-yellow-400">
                    ${{ account?.volume_mln_usd ? formatCurrency(account.volume_mln_usd) : "0" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <div class="text-lg font-bold text-red-600 dark:text-red-400">
                    ${{ account?.reward_usd ? formatCurrency(account.reward_usd) : "0" }}
                  </div>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                    account?.client_status === 'ACTIVE' ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                    account?.client_status === 'INACTIVE' ? 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200' :
                    account?.client_status === 'PENDING' ? 'bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 dark:from-yellow-900 dark:to-yellow-800 dark:text-yellow-200' :
                    'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-200'
                  ]">
                    {{ account?.client_status || "-" }}
                  </span>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                    account?.kyc_passed ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                    'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200'
                  ]">
                    {{ account?.kyc_passed ? "ผ่าน" : "ไม่ผ่าน" }}
                  </span>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                    account?.ftd_received ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                    'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200'
                  ]">
                    {{ account?.ftd_received ? "มี" : "ไม่มี" }}
                  </span>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                    account?.ftt_made ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                    'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200'
                  ]">
                    {{ account?.ftt_made ? "มี" : "ไม่มี" }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <!-- Pagination -->
        <div class="py-4">
          <Pagination :data="clients" />
        </div>
      </CardBox>

      <!-- Search Modal -->
      <CardBoxModal
        v-model="isModalActive"
        title="ค้นหาข้อมูล"
        button="info"
        has-cancel
        @confirm="applySearch"
        @cancel="resetFilters"
      >
        <div class="flex flex-col gap-5 px-2 md:px-6 py-2 md:py-6">
          <FormControl
            v-model="tempFilters.partner_account"
            label="Partner Account"
            placeholder="กรอก Partner Account"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm px-4 py-3 border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 transition-all"
            input-class="text-lg py-3"
          />
          <FormControl
            v-model="tempFilters.client_uid"
            label="Client UID"
            placeholder="กรอก Client UID"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm px-4 py-3 border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 transition-all"
            input-class="text-lg py-3"
          />
          <FormControl
            v-model="tempFilters.client_country"
            label="ประเทศ"
            placeholder="กรอกรหัสประเทศ"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm px-4 py-3 border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 transition-all"
            input-class="text-lg py-3"
          />
          <FormControl
            v-model="tempFilters.client_status"
            label="สถานะ"
            type="select"
            :options="[
              { value: '', label: 'ทั้งหมด' },
              { value: 'ACTIVE', label: 'ACTIVE' },
              { value: 'INACTIVE', label: 'INACTIVE' },
              { value: 'PENDING', label: 'PENDING' },
            ]"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm px-4 py-3 border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 transition-all"
            input-class="text-lg py-3"
          />
          <FormControl
            v-model="tempFilters.reg_date"
            label="วันที่ลงทะเบียน"
            type="date"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm px-4 py-3 border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 transition-all"
            input-class="text-lg py-3"
          />
          <FormControl
            v-model="tempFilters.kyc_passed"
            label="KYC"
            type="select"
            :options="[
              { value: '', label: 'ทั้งหมด' },
              { value: '1', label: 'ผ่าน' },
              { value: '0', label: 'ไม่ผ่าน' },
            ]"
            class="bg-white dark:bg-slate-800 rounded-xl shadow-sm px-4 py-3 border border-gray-200 dark:border-slate-700 focus-within:ring-2 focus-within:ring-blue-200 dark:focus-within:ring-blue-800 transition-all"
            input-class="text-lg py-3"
          />
        </div>
        <div class="border-t border-gray-100 dark:border-slate-700 mt-4"></div>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
  /* Custom styles for better UI */
  .search-filter-section {
    position: relative;
    z-index: 10;
  }
</style>
