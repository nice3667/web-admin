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
  mdiFilterVariant,
  mdiChevronDown,
} from "@mdi/js";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, onMounted, computed, watch } from "vue";
import FormControl from "@/Components/FormControl.vue";
import Pagination from "@/Components/Admin/Pagination.vue";
import BaseIcon from "@/Components/BaseIcon.vue";

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

const showAdvancedSearch = ref(false);

const filters = ref({
  partner_account: props.filters.partner_account || "",
  client_uid: props.filters.client_uid || "",
  client_country: props.filters.client_country || "",
  client_status: props.filters.client_status || "",
  reg_date: props.filters.reg_date || "",
  kyc_passed: props.filters.kyc_passed || "",
});

// Initialize filters from props
onMounted(() => {
  filters.value = { ...props.filters };
});

// Computed properties for data
const accounts = computed(() => {
  return props.clients.data || [];
});

// Apply search filters
const applyFilters = () => {
  router.get(
    "/admin/reports1/client-account1",
    { ...filters.value },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
  showAdvancedSearch.value = false;
};

// Reset filters
const resetFilters = () => {
  filters.value = {
    partner_account: "",
    client_uid: "",
    client_country: "",
    client_status: "",
    reg_date: "",
    kyc_passed: "",
  };
  router.get(
    "/admin/reports1/client-account1",
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
    filters.value = {
      partner_account: newFilters.partner_account || "",
      client_uid: newFilters.client_uid || "",
      client_country: newFilters.client_country || "",
      client_status: newFilters.client_status || "",
      reg_date: newFilters.reg_date || "",
      kyc_passed: newFilters.kyc_passed || "",
    };
  },
  { immediate: true }
);
</script>

<template>
  <LayoutAuthenticated>
    <Head title="บัญชีลูกค้า" />

    <SectionMain>
      <SectionTitleLineWithButton
        title="บัญชีลูกค้า (User 1)"
        main
      >
        <template #button>
          <div class="flex items-center space-x-3">
            <!-- User Email Badge -->
            <div v-if="props.user_email" class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
              {{ props.user_email }}
            </div>

            <!-- Data Source Badge -->
            <div :class="{
              'bg-green-100 text-green-800': props.data_source === 'Exness API',
              'bg-yellow-100 text-yellow-800': props.data_source === 'Database',
              'bg-red-100 text-red-800': props.data_source === 'Error'
            }" class="px-3 py-1 rounded-full text-sm font-medium">
              {{ props.data_source }}
            </div>

            <BaseButton
              :icon="mdiRefresh"
              label="อัปเดตข้อมูล"
              color="success"
              @click="router.get('/admin/reports1/client-account1')"
            />
          </div>
        </template>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="props.error" color="danger" :icon="mdiAlertBoxOutline">
        {{ props.error }}
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

      <!-- Search and Filter Section -->
      <CardBox class="mb-8 p-8 shadow-2xl border-0 bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 relative overflow-hidden">
        <!-- Background decorative elements -->
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5"></div>
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-400/10 to-purple-400/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-0 left-0 w-24 h-24 bg-gradient-to-tr from-indigo-400/10 to-pink-400/10 rounded-full blur-2xl"></div>

        <div class="relative z-10">
          <!-- Main Search Row -->
          <div class="flex flex-col lg:flex-row items-center justify-between space-y-4 lg:space-y-0 lg:space-x-6">
            <!-- Left Side: Search Inputs -->
            <div class="flex flex-col md:flex-row items-center space-y-4 md:space-y-0 md:space-x-4 w-full lg:w-auto">
              <!-- Search Input with enhanced styling -->
              <div class="w-full md:w-80 relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-purple-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>
                <div class="relative">
                  <input
                    v-model="filters.client_uid"
                    type="text"
                    placeholder="🔍 ค้นหา Client UID หรือ Partner Account..."
                    class="w-full pl-14 pr-6 py-4 bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border-2 border-transparent rounded-2xl shadow-lg focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 placeholder-gray-500 dark:placeholder-gray-400 group-hover:shadow-xl"
                    @input="applyFilters"
                  />
                  <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                    <div class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-500 rounded-lg flex items-center justify-center shadow-lg">
                      <BaseIcon :path="mdiMagnify" class="h-4 w-4 text-white" />
                    </div>
                  </div>
                </div>
              </div>

              <!-- Status Filter - Moved outside -->
              <div class="w-full md:w-64 relative group">
                <div class="absolute inset-0 bg-gradient-to-r from-orange-500 to-red-500 rounded-2xl blur opacity-20 group-hover:opacity-30 transition-opacity duration-300"></div>
                <div class="relative">
                  <select
                    v-model="filters.client_status"
                    class="w-full pl-12 pr-16 py-4 truncate bg-white/80 dark:bg-gray-800/80 backdrop-blur-sm border-2 border-transparent rounded-2xl shadow-lg focus:border-orange-500 focus:ring-4 focus:ring-orange-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 group-hover:shadow-xl appearance-none"
                    @change="applyFilters"
                  >
                    <option value="">ทุกสถานะ</option>
                    <option value="ACTIVE">🟢 ACTIVE</option>
                    <option value="INACTIVE">🔴 INACTIVE</option>
                    <option value="PENDING">🟡 PENDING</option>
                  </select>
                  <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <div class="w-6 h-6 bg-gradient-to-br from-orange-500 to-red-500 rounded-lg flex items-center justify-center shadow-lg">
                      <span class="text-white text-xs font-bold">⚡</span>
                    </div>
                  </div>

                </div>
              </div>
            </div>

            <!-- Right Side: Action Buttons -->
            <div class="flex items-center space-x-3 relative z-20">
              <BaseButton
                label="ค้นหาขั้นสูง"
                :icon="mdiFilterVariant"
                color="info"
                @click="showAdvancedSearch = !showAdvancedSearch"
                class="font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 bg-gradient-to-r from-blue-500 to-purple-500 hover:from-blue-600 hover:to-purple-600 border-0 px-6 py-4 rounded-2xl"
              />
              <BaseButton
                label="รีเซ็ต"
                :icon="mdiRefresh"
                color="gray"
                @click="resetFilters"
                class="font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 bg-gradient-to-r from-gray-500 to-gray-600 hover:from-gray-600 hover:to-gray-700 border-0 px-6 py-4 rounded-2xl"
              />
            </div>
          </div>

          <!-- Enhanced Advanced Search Popup -->
          <div v-if="showAdvancedSearch" class="mt-8 relative">
            <!-- Enhanced background blur effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 rounded-3xl blur-2xl animate-pulse"></div>
            <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/10 via-purple-500/10 to-pink-500/10 rounded-3xl blur-xl"></div>

            <div class="relative bg-white/95 dark:bg-gray-800/95 backdrop-blur-2xl rounded-3xl shadow-2xl border border-white/30 dark:border-gray-700/50 p-8 animate-fade-in-down overflow-hidden">
              <!-- Floating decorative elements -->
              <div class="absolute top-4 right-4 w-16 h-16 bg-gradient-to-br from-blue-400/20 to-purple-400/20 rounded-full blur-xl animate-bounce"></div>
              <div class="absolute bottom-4 left-4 w-12 h-12 bg-gradient-to-tr from-pink-400/20 to-orange-400/20 rounded-full blur-lg animate-pulse"></div>

              <!-- Header with enhanced gradient background -->
              <div class="mb-8 p-6 bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 rounded-2xl shadow-2xl relative overflow-hidden">
                <!-- Animated background pattern -->
                <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent transform -skew-x-12 animate-pulse"></div>
                <div class="absolute top-0 right-0 w-32 h-32 bg-white/5 rounded-full blur-2xl"></div>

                <div class="relative flex items-center space-x-4">
                  <div class="w-14 h-14 bg-white/25 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-xl transform hover:scale-110 transition-transform duration-300">
                    <BaseIcon :path="mdiFilterVariant" class="h-7 w-7 text-white" />
                  </div>
                  <div>
                    <h3 class="text-3xl font-bold text-white mb-2">ค้นหาขั้นสูง</h3>
                    <p class="text-blue-100 text-sm font-medium">กรองข้อมูลตามเงื่อนไขที่ต้องการ</p>
                  </div>
                </div>
              </div>

              <!-- Enhanced Form Grid with better spacing and effects -->
              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 relative z-10">

                <!-- Partner Account with enhanced styling -->
                <div class="group transform hover:scale-105 transition-all duration-300">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                    <div class="w-7 h-7 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center mr-3 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                      <span class="text-white text-xs font-bold">PA</span>
                    </div>
                    <span class="group-hover:text-blue-600 transition-colors duration-300">Partner Account</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-blue-500/20 to-purple-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <input
                      v-model="filters.partner_account"
                      type="text"
                      placeholder="กรอก Partner Account..."
                      class="relative w-full pl-4 pr-4 py-4 bg-white/90 dark:bg-gray-700/90 backdrop-blur-sm border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-lg focus:border-blue-500 focus:ring-4 focus:ring-blue-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 group-hover:shadow-xl group-hover:border-blue-300"
                    />
                  </div>
                </div>

                <!-- Client UID with enhanced styling -->
                <div class="group transform hover:scale-105 transition-all duration-300">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                    <div class="w-7 h-7 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center mr-3 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                      <span class="text-white text-xs font-bold">ID</span>
                    </div>
                    <span class="group-hover:text-green-600 transition-colors duration-300">Client UID</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-500/20 to-emerald-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <input
                      v-model="filters.client_uid"
                      type="text"
                      placeholder="กรอก Client UID..."
                      class="relative w-full pl-4 pr-4 py-4 bg-white/90 dark:bg-gray-700/90 backdrop-blur-sm border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-lg focus:border-green-500 focus:ring-4 focus:ring-green-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 group-hover:shadow-xl group-hover:border-green-300"
                    />
                  </div>
                </div>

                <!-- Country with enhanced styling -->
                <div class="group transform hover:scale-105 transition-all duration-300">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                    <div class="w-7 h-7 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center mr-3 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                      <span class="text-white text-xs font-bold">🌍</span>
                    </div>
                    <span class="group-hover:text-purple-600 transition-colors duration-300">ประเทศ (รหัสย่อ)</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-purple-500/20 to-pink-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <input
                      v-model="filters.client_country"
                      type="text"
                      placeholder="เช่น TH, US, GB..."
                      class="relative w-full pl-4 pr-4 py-4 bg-white/90 dark:bg-gray-700/90 backdrop-blur-sm border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-lg focus:border-purple-500 focus:ring-4 focus:ring-purple-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 group-hover:shadow-xl group-hover:border-purple-300"
                    />
                  </div>
                </div>

                <!-- Registration Date with enhanced styling -->
                <div class="group transform hover:scale-105 transition-all duration-300">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                    <div class="w-7 h-7 bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl flex items-center justify-center mr-3 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                      <span class="text-white text-xs font-bold">📅</span>
                    </div>
                    <span class="group-hover:text-pink-600 transition-colors duration-300">วันที่ลงทะเบียน</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-pink-500/20 to-rose-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <input
                      v-model="filters.reg_date"
                      type="date"
                      class="relative w-full pl-4 pr-4 py-4 bg-white/90 dark:bg-gray-700/90 backdrop-blur-sm border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-lg focus:border-pink-500 focus:ring-4 focus:ring-pink-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 group-hover:shadow-xl group-hover:border-pink-300"
                    />
                  </div>
                </div>

                <!-- KYC Status with enhanced styling -->
                <div class="group transform hover:scale-105 transition-all duration-300">
                  <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-3 flex items-center">
                    <div class="w-7 h-7 bg-gradient-to-br from-teal-500 to-teal-600 rounded-xl flex items-center justify-center mr-3 shadow-lg transform group-hover:scale-110 transition-transform duration-300">
                      <span class="text-white text-xs font-bold">✓</span>
                    </div>
                    <span class="group-hover:text-teal-600 transition-colors duration-300">สถานะ KYC</span>
                  </label>
                  <div class="relative">
                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500/20 to-cyan-500/20 rounded-xl blur opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <select
                      v-model="filters.kyc_passed"
                      class="relative w-full pl-4 pr-10 py-4 bg-white/90 dark:bg-gray-700/90 backdrop-blur-sm border-2 border-gray-200 dark:border-gray-600 rounded-xl shadow-lg focus:border-teal-500 focus:ring-4 focus:ring-teal-200 focus:ring-opacity-50 dark:text-white transition-all duration-300 group-hover:shadow-xl group-hover:border-teal-300 appearance-none"
                    >
                      <option value="">ทั้งหมด</option>
                      <option value="1">✅ ผ่าน</option>
                      <option value="0">❌ ไม่ผ่าน</option>
                    </select>

                  </div>
                </div>
              </div>

              <!-- Enhanced Action Buttons with better styling -->
              <div class="mt-10 flex justify-end space-x-4 relative z-10">
                <BaseButton
                  label="ยกเลิก"
                  color="light"
                  @click="showAdvancedSearch = false"
                  class="font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 border-0 px-8 py-4 rounded-2xl backdrop-blur-sm"
                />
                <BaseButton
                  label="ใช้ตัวกรอง"
                  color="success"
                  @click="applyFilters"
                  class="font-bold shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:scale-105 bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 border-0 px-8 py-4 rounded-2xl backdrop-blur-sm"
                />
              </div>
            </div>
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
                    'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-200'
                  ]">
                    {{ account?.client_status || "-" }}
                  </span>
                </td>
                <td class="px-8 py-8 whitespace-nowrap">
                  <span :class="[
                    'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                    account?.kyc_passed === true ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                    account?.kyc_passed === false ? 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200' :
                    'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-200'
                  ]">
                    {{ account?.kyc_passed === true ? "ผ่าน" : account?.kyc_passed === false ? "ไม่ผ่าน" : "ไม่ทราบ" }}
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
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style scoped>
@keyframes fade-in-down {
  from {
    opacity: 0;
    transform: translateY(-1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
.animate-fade-in-down {
  animation: fade-in-down 0.3s ease-out;
}
</style>
