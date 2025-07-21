<script setup>
import { Head, router, Link } from "@inertiajs/vue3";
import { mdiClockOutline, mdiAlertBoxOutline, mdiAccountGroup, mdiChartLine, mdiCurrencyUsd, mdiGift } from "@mdi/js";
import TopNavBar from '@/Components/TopNavBar.vue';
import BottomNavBar from '@/Components/BottomNavBar.vue';
import { ref, computed, watch } from "vue";

const props = defineProps({
  clients: {
    type: Object,
    required: true,
    default: () => ({}),
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
    default: () => ({}),
  },
  user_email: {
    type: String,
    default: "",
  },
  data_source: {
    type: String,
    default: "database",
  },
  error: {
    type: String,
    default: "",
  },
});

// Debugging: Log the clients prop to verify data
console.log("Clients data:", props.clients);

// Local filters for UI
const filters = ref({
  client_account: props.filters.client_account || "",
  account_type: props.filters.account_type || "",
  platform: props.filters.platform || "",
  partner_account: props.filters.partner_account || "",
  client_uid: props.filters.client_uid || "",
  client_country: props.filters.client_country || "",
  client_status: props.filters.client_status || "",
  reg_date: props.filters.reg_date || "",
});

// XM-style filter options
const signUpDateFilter = ref("all");
const startDate = ref("");
const endDate = ref("");

// Account type options (placeholder - you can customize based on your data)
const accountTypeOptions = computed(() => {
  const types = new Set();
  if (props.clients?.data) {
    props.clients.data.forEach(account => {
      if (account.account_type) {
        types.add(account.account_type);
      }
    });
  }
  return Array.from(types).sort();
});

// Platform options (MT4, MT5 like XM page)
const platformOptions = computed(() => {
  return ["MT4", "MT5"];
});

// Apply filters (client-side filtering - no page refresh)
const applyFilters = () => {
  // No router navigation - just update local filters
  // The filteredAccounts computed property will handle the filtering
  console.log('Filters applied:', filters.value);
};

const resetFilters = () => {
  filters.value = {
    client_account: "",
    account_type: "",
    platform: "",
    partner_account: "",
    client_uid: "",
    client_country: "",
    client_status: "",
    reg_date: "",
  };

  signUpDateFilter.value = "all";
  startDate.value = "";
  endDate.value = "";

  console.log('Filters reset');
};

// Computed properties for filtered data
const filteredAccounts = computed(() => {
  let result = props.clients?.data || [];

  // Filter by Client Account
  if (filters.value?.client_account) {
    const searchLower = filters.value.client_account.toLowerCase();
    result = result.filter((account) =>
      (account?.client_account || '').toLowerCase().includes(searchLower) ||
      (account?.client_uid || '').toLowerCase().includes(searchLower) ||
      (account?.partner_account || '').toLowerCase().includes(searchLower)
    );
  }

  // Filter by Account Type
  if (filters.value?.account_type) {
    result = result.filter((account) =>
      (account?.account_type || '').toLowerCase() === filters.value.account_type.toLowerCase()
    );
  }

  // Filter by Platform
  if (filters.value?.platform) {
    result = result.filter((account) =>
      (account?.platform || '').toLowerCase() === filters.value.platform.toLowerCase()
    );
  }

  // Filter by Partner Account
  if (filters.value?.partner_account) {
    const searchLower = filters.value.partner_account.toLowerCase();
    result = result.filter((account) =>
      (account?.partner_account || '').toLowerCase().includes(searchLower)
    );
  }

  // Filter by Client UID
  if (filters.value?.client_uid) {
    const searchLower = filters.value.client_uid.toLowerCase();
    result = result.filter((account) =>
      (account?.client_uid || '').toLowerCase().includes(searchLower)
    );
  }

  // Filter by Country
  if (filters.value?.client_country) {
    const searchLower = filters.value.client_country.toLowerCase();
    result = result.filter((account) =>
      (account?.client_country || '').toLowerCase().includes(searchLower)
    );
  }

  // Filter by Status
  if (filters.value?.client_status) {
    result = result.filter((account) =>
      (account?.client_status || '').toLowerCase() === filters.value.client_status.toLowerCase()
    );
  }

  // Filter by Registration Date
  if (filters.value?.reg_date) {
    result = result.filter((account) => {
      if (!account?.reg_date) return false;
      const accountDate = new Date(account.reg_date).toISOString().split('T')[0];
      return accountDate === filters.value.reg_date;
    });
  }

  // Filter by Sign Up Date Range
  if (signUpDateFilter.value !== "all") {
    const now = new Date();
    let startDate = null;
    let endDate = null;

    if (signUpDateFilter.value === "1m") {
      startDate = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate());
      endDate = now;
    } else if (signUpDateFilter.value === "2m") {
      startDate = new Date(now.getFullYear(), now.getMonth() - 2, now.getDate());
      endDate = now;
    } else if (signUpDateFilter.value === "custom" && startDate.value && endDate.value) {
      startDate = new Date(startDate.value);
      endDate = new Date(endDate.value);
    }

    if (startDate && endDate) {
      result = result.filter((account) => {
        if (!account?.reg_date) return false;
        const regDate = new Date(account.reg_date);
        return regDate >= startDate && regDate <= endDate;
      });
    }
  }

  return result;
});

// Computed properties for stats with safe defaults
const computedStats = computed(() => ({
  total_pending: props.stats?.total_pending || props.stats?.total_client_uids || 0,
  total_amount: Number(props.stats?.total_amount || 0).toFixed(4),
  due_today: Number(props.stats?.due_today || 0).toFixed(4),
  overdue: Number(props.stats?.overdue || 0).toFixed(4)
}));

// Helper functions for status display
const getStatusColor = (status) => {
  const statusUpper = (status || '').toUpperCase();
  switch (statusUpper) {
    case "ACTIVE":
      return "text-green-600";
    case "INACTIVE":
      return "text-red-600";
    default:
      return "text-gray-600";
  }
};

const getStatusText = (status) => {
  return (status || 'UNKNOWN').toUpperCase();
};

// Helper functions for formatting
const formatDate = (dateString) => {
  if (!dateString) return "-";
  try {
    const date = new Date(dateString);
    return date.toLocaleDateString("th-TH", {
      year: "numeric",
      month: "long",
      day: "numeric",
    });
  } catch (e) {
    return "-";
  }
};

const formatCountry = (code) => {
  if (!code || code === "-" || code === "") return "-";
  // Return full country name if found, otherwise show the code with a prefix
  return countryNames[code] || `[${code}]`;
};

const formatNumber = (number) => {
  return Number(number || 0).toFixed(4);
};

const formatCurrency = (amount) => {
  return Number(amount || 0).toFixed(4);
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
  PE: "Peru",
  CO: "Colombia",
  VE: "Venezuela",
  // Other regions
  AU: "Australia",
  NZ: "New Zealand",
  IN: "India",
  RU: "Russia",
  ZA: "South Africa",
  EG: "Egypt",
  NG: "Nigeria",
  KE: "Kenya",
  MA: "Morocco",
  SA: "Saudi Arabia",
  AE: "United Arab Emirates",
  IL: "Israel",
  TR: "Turkey",
  IR: "Iran",
  PK: "Pakistan",
  BD: "Bangladesh",
  LK: "Sri Lanka",
  NP: "Nepal",
  BT: "Bhutan",
  MV: "Maldives",
};

// Watch for props changes to update filters
watch(
  () => props.filters,
  (newFilters) => {
    filters.value = {
      client_account: newFilters.client_account || "",
      account_type: newFilters.account_type || "",
      platform: newFilters.platform || "",
      partner_account: newFilters.partner_account || "",
      client_uid: newFilters.client_uid || "",
      client_country: newFilters.client_country || "",
      client_status: newFilters.client_status || "",
      reg_date: newFilters.reg_date || "",
    };

    // Update sign up date filter
    if (newFilters.sign_up_date_filter) {
      signUpDateFilter.value = newFilters.sign_up_date_filter;
    }
    if (newFilters.start_date) {
      startDate.value = newFilters.start_date;
    }
    if (newFilters.end_date) {
      endDate.value = newFilters.end_date;
    }
  },
  { immediate: true }
);

// Use filtered accounts for display
const paginatedAccounts = computed(() => {
  return filteredAccounts.value;
});

const startIndex = computed(() => 1);
const endIndex = computed(() => filteredAccounts.value.length);
</script>

<template>
  <TopNavBar />
  <div
    class="min-h-screen py-12 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
  >
    <div class="mx-auto sm:px-6 lg:px-8">
      <!-- Page Title with Animation -->
      <div class="mb-8 text-center animate-fade-in">
        <h1
          class="text-4xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
        >
          รายงานบัญชีลูกค้า
        </h1>
        <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
          ดูและวิเคราะห์ข้อมูลบัญชีลูกค้าและสถิติการเทรด
        </p>
      </div>

      <!-- ลูกค้า Partner Label -->
      <div
        class="mb-2 text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in"
      >
        ลูกค้า Partner (Ham)
      </div>

      <!-- Error Notification -->
      <div
        v-if="error"
        class="p-6 mb-6 overflow-hidden border shadow-xl bg-red-50/80 dark:bg-red-900/20 backdrop-blur-lg rounded-2xl border-red-200/20 dark:border-red-800/20"
      >
        <div class="flex items-center">
          <svg
            class="w-6 h-6 mr-3 text-red-500"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
            ></path>
          </svg>
          <span class="font-semibold text-red-700 dark:text-red-400">{{
            error
          }}</span>
        </div>
      </div>

      <!-- Data Source Info -->
      <div
        class="p-6 mb-6 overflow-hidden border shadow-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl border-white/20 dark:border-slate-700/20"
      >
        <div
          class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between"
        >
          <div class="flex items-center gap-4">
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
              <span class="text-blue-600 dark:text-blue-400">แหล่งข้อมูล:</span>
              {{ data_source }}
            </div>
            <div class="text-sm font-semibold text-gray-700 dark:text-gray-300">
              <span class="text-blue-600 dark:text-blue-400">อีเมล:</span>
              {{ user_email }}
            </div>
          </div>
        </div>
      </div>

      <!-- Filter Box (XM style) -->
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
              v-model="filters.client_account"
              type="text"
              placeholder="ค้นหา Client Account จากทุกหน้า (Client Account, Account ID, etc.)"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
              @input="applyFilters"
            >
          </div>
          <div class="col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6" />
                </svg>
                Account Type
              </span>
            </label>
            <select
              v-model="filters.account_type"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
              @change="applyFilters"
            >
              <option value="">All</option>
              <option v-for="type in accountTypeOptions" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
          <div class="col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Platform
              </span>
            </label>
            <select
              v-model="filters.platform"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
              @change="applyFilters"
            >
              <option value="">All</option>
              <option v-for="platform in platformOptions" :key="platform" :value="platform">{{ platform }}</option>
            </select>
          </div>
          <div class="col-span-1 sm:col-span-2 lg:col-span-1">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Sign Up Date
              </span>
            </label>
            <select v-model="signUpDateFilter" class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base">
              <option value="all">ทั้งหมด</option>
              <option value="1m">เปิดบัญชีเมื่อ 1 เดือนที่แล้ว</option>
              <option value="2m">เปิดบัญชีเมื่อ 2 เดือนที่แล้ว</option>
              <option value="custom">เลือกวันที่เอง</option>
            </select>
          </div>

          <!-- Reset Button -->
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

          <!-- Custom Date Range (Mobile: Full width) -->
          <div v-if="signUpDateFilter === 'custom'" class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Start Date
                  </span>
                </label>
                <input
                  type="date"
                  v-model="startDate"
                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                >
              </div>
              <div>
                <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                  <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    End Date
                  </span>
                </label>
                <input
                  type="date"
                  v-model="endDate"
                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                >
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistics Cards (XM style) -->
      <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
        <div
          class="p-6 overflow-hidden transition-all duration-300 transform border shadow-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl border-white/20 dark:border-slate-700/20 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">จำนวน Client UID</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ computedStats.total_pending }}
              </p>
            </div>
            <div
              class="p-4 transition-transform duration-300 transform bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 hover:rotate-6"
            >
              <svg
                class="w-8 h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                ></path>
              </svg>
            </div>
          </div>
        </div>
        <div
          class="p-6 overflow-hidden transition-all duration-300 transform border shadow-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl border-white/20 dark:border-slate-700/20 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Volume (lots)</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ computedStats.total_amount }}
              </p>
            </div>
            <div
              class="p-4 transition-transform duration-300 transform bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 hover:rotate-6"
            >
              <svg
                class="w-8 h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                ></path>
              </svg>
            </div>
          </div>
        </div>
        <div
          class="p-6 overflow-hidden transition-all duration-300 transform border shadow-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl border-white/20 dark:border-slate-700/20 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Volume (USD)</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ computedStats.due_today }}
              </p>
            </div>
            <div
              class="p-4 transition-transform duration-300 transform bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 hover:rotate-6"
            >
              <svg
                class="w-8 h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                ></path>
              </svg>
            </div>
          </div>
        </div>
        <div
          class="p-6 overflow-hidden transition-all duration-300 transform border shadow-xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl border-white/20 dark:border-slate-700/20 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Reward (USD)</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ computedStats.overdue }}
              </p>
            </div>
            <div
              class="p-4 transition-transform duration-300 transform bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 hover:rotate-6"
            >
              <svg
                class="w-8 h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                ></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Table Section (XM style) -->
      <div
        class="overflow-hidden border shadow-2xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg rounded-2xl border-white/20 dark:border-slate-700/20"
      >
        <div
          class="flex flex-col gap-2 px-8 py-6 md:flex-row md:items-center md:justify-between"
        >
          <div>
            <h3
              class="text-2xl font-bold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
            >
              รายชื่อบัญชีลูกค้า
            </h3>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              ข้อมูลบัญชีลูกค้าทั้งหมดและสถานะปัจจุบัน
            </p>
          </div>
        </div>
        <div class="border-t border-blue-100/20 dark:border-slate-700/20">
          <div class="overflow-x-auto">
            <table
              class="min-w-full divide-y divide-blue-100/20 dark:divide-slate-700/20"
            >
              <thead class="bg-blue-50/50 dark:bg-slate-700/50">
                <tr>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Partner Account
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Client UID
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Client Account
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    วันที่ลงทะเบียน
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    ประเทศ
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Volume (Lots)
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Volume (USD)
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Reward (USD)
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    สถานะ
                  </th>
                </tr>
              </thead>
              <tbody
                class="divide-y bg-white/50 dark:bg-slate-800/50 divide-blue-100/20 dark:divide-slate-700/20"
              >
                <tr v-if="filteredAccounts.length === 0">
                  <td
                    colspan="9"
                    class="px-6 py-12 text-sm text-center text-gray-600 dark:text-gray-400"
                  >
                    ไม่พบข้อมูลบัญชีลูกค้า
                  </td>
                </tr>
                <tr
                  v-for="account in paginatedAccounts"
                  :key="account?.client_uid || Math.random()"
                  class="transition-colors duration-200 hover:bg-blue-50/50 dark:hover:bg-slate-700/50"
                >
                  <td
                    class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white"
                  >
                    {{ account?.partner_account || "-" }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white"
                  >
                    {{ account?.client_uid || "-" }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm font-semibold text-blue-600 whitespace-nowrap dark:text-blue-400"
                  >
                    {{ account?.client_account || "-" }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300"
                  >
                    {{ account?.reg_date ? formatDate(account.reg_date) : "-" }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300"
                  >
                    {{
                      account?.client_country
                        ? formatCountry(account.client_country)
                        : "-"
                    }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm font-semibold text-indigo-600 whitespace-nowrap dark:text-indigo-400"
                  >
                    {{
                      account?.volume_lots
                        ? formatNumber(account.volume_lots)
                        : "0"
                    }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm font-semibold text-yellow-600 whitespace-nowrap dark:text-yellow-400"
                  >
                    ${{
                      account?.volume_mln_usd
                        ? formatCurrency(account.volume_mln_usd)
                        : "0"
                    }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm font-semibold text-red-600 whitespace-nowrap dark:text-red-400"
                  >
                    ${{
                      account?.reward_usd
                        ? formatCurrency(account.reward_usd)
                        : "0"
                    }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm whitespace-nowrap"
                    :class="getStatusColor(account?.client_status)"
                  >
                    <span
                      :class="[
                        'px-4 py-1.5 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full',
                        account?.client_status === 'ACTIVE'
                          ? 'bg-green-100/80 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                          : account?.client_status === 'INACTIVE'
                          ? 'bg-red-100/80 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                          : 'bg-gray-100/80 text-gray-800 dark:bg-gray-900/20 dark:text-gray-200',
                      ]"
                    >
                      <span class="relative flex w-2 h-2">
                        <span
                          :class="[
                            'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                            account?.client_status === 'ACTIVE'
                              ? 'bg-green-400'
                              : account?.client_status === 'INACTIVE'
                              ? 'bg-red-400'
                              : 'bg-gray-400',
                          ]"
                        ></span>
                        <span
                          :class="[
                            'relative inline-flex rounded-full h-2 w-2',
                            account?.client_status === 'ACTIVE'
                              ? 'bg-green-500'
                              : account?.client_status === 'INACTIVE'
                              ? 'bg-red-500'
                              : 'bg-gray-500',
                          ]"
                        ></span>
                      </span>
                      {{ getStatusText(account?.client_status) }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

        </div>
      </div>
      <!-- Pagination -->
      <div v-if="props.clients && props.clients.data && props.clients.data.length > 0" class="px-6 py-4 bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg border-t border-blue-100/20 dark:border-slate-700/20">
        <div class="flex items-center justify-between">
          <!-- Pagination Info -->
          <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
            <span>แสดง {{ startIndex }} ถึง {{ endIndex }} จากทั้งหมด {{ filteredAccounts.length }} รายการ</span>
          </div>
          
          <!-- Pagination Navigation -->
          <div class="flex items-center space-x-2">
            <!-- Previous Page -->
            <Link
              v-if="props.clients.prev_page_url"
              :href="props.clients.prev_page_url"
              class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200"
              preserve-scroll
            >
              ก่อนหน้า
            </Link>
            <span v-else class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
              ก่อนหน้า
            </span>

            <!-- Page Numbers -->
            <template v-for="(link, i) in props.clients.links" :key="i">
              <Link
                v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next') && !link.label.includes('...')"
                :href="link.url"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
                :class="{
                  'text-white bg-blue-600 dark:bg-blue-500': link.active,
                  'text-blue-600 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-slate-700': !link.active
                }"
                preserve-scroll
              >
                {{ link.label }}
              </Link>
              <span
                v-else-if="link.label.includes('...')"
                class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg cursor-default"
              >
                ...
              </span>
            </template>

            <!-- Next Page -->
            <Link
              v-if="props.clients.next_page_url"
              :href="props.clients.next_page_url"
              class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700 transition-colors duration-200"
              preserve-scroll
            >
              ถัดไป
            </Link>
            <span v-else class="px-4 py-2 text-sm font-medium text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-slate-900 border border-gray-200 dark:border-slate-700 rounded-lg cursor-not-allowed">
              ถัดไป
            </span>
          </div>
        </div>
      </div>
      <BottomNavBar />
    </div>
  </div>
</template>

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
.bg-gradient-animate {
  background-size: 200% 200%;
  animation: gradient 15s ease infinite;
}
</style>
