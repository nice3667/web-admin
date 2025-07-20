<script setup>
import { Head, router } from "@inertiajs/vue3";
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
  partner_account: props.filters.partner_account || "",
  client_uid: props.filters.client_uid || "",
  client_country: props.filters.client_country || "",
  client_status: props.filters.client_status || "",
  reg_date: props.filters.reg_date || "",
});

// Apply filters and navigate
const applyFilters = () => {
  const params = {};

  if (filters.value.partner_account) {
    params.partner_account = filters.value.partner_account;
  }

  if (filters.value.client_uid) {
    params.client_uid = filters.value.client_uid;
  }

  if (filters.value.client_country) {
    params.client_country = filters.value.client_country;
  }

  if (filters.value.client_status) {
    params.client_status = filters.value.client_status;
  }

  if (filters.value.reg_date) {
    params.reg_date = filters.value.reg_date;
  }

  router.get("/admin/reports1/client-account1", params, {
    preserveState: true,
    preserveScroll: true,
  });
};

const resetFilters = () => {
  filters.value = {
    partner_account: "",
    client_uid: "",
    client_country: "",
    client_status: "",
    reg_date: "",
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

// Computed properties for filtered data
const filteredAccounts = computed(() => {
  let result = props.accounts.data || [];

  // ค้นหา (เฉพาะ client_uid)
  if (filters.value.search) {
    const searchLower = filters.value.search.toLowerCase();
    result = result.filter((account) =>
      account.client_uid?.toLowerCase().includes(searchLower)
    );
  }

  // กรองตามสถานะ
  if (filters.value.status !== "all") {
    const statusFilter = filters.value.status.toUpperCase();
    result = result.filter((account) => {
      const accountStatus = (account.client_status || "").toUpperCase();
      return accountStatus === statusFilter;
    });
  }

  // กรองตามช่วงวันที่
  if (filters.value.date_range.start || filters.value.date_range.end) {
    result = result.filter((account) => {
      const regDate = new Date(account.reg_date);
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
const formattedStats = computed(() => {
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

// Helper functions for formatting
const formatDate = (dateString) => {
  if (!dateString) return "-";
  const date = new Date(dateString);
  return date.toLocaleDateString("th-TH", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
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
      partner_account: newFilters.partner_account || "",
      client_uid: newFilters.client_uid || "",
      client_country: newFilters.client_country || "",
      client_status: newFilters.client_status || "",
      reg_date: newFilters.reg_date || "",
    };
  },
  { immediate: true }
);

// Pagination logic
const itemsPerPage = 10;
const currentPage = ref(1);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, accounts.value.length));
const paginatedAccounts = computed(() => {
  return accounts.value.slice(startIndex.value, endIndex.value);
});

const goToPage = (page) => {
  if (page < 1 || page > Math.ceil(accounts.value.length / itemsPerPage)) return;
  currentPage.value = page;
};

const displayedPages = computed(() => {
  const totalPages = Math.ceil(accounts.value.length / itemsPerPage);
  const currentPageValue = currentPage.value;
  const pages = [];

  if (totalPages <= 5) {
    // ถ้ามีหน้าไม่เกิน 5 หน้า ให้แสดงทุกหน้า
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i);
    }
  } else {
    // ถ้ามีมากกว่า 5 หน้า ให้แสดงแค่ 5 หน้า
    let start = Math.max(currentPageValue - 2, 1);
    let end = Math.min(currentPageValue + 2, totalPages);

    // ปรับให้แสดง 5 หน้าเสมอ
    if (end - start + 1 < 5) {
      if (start === 1) {
        end = Math.min(5, totalPages);
      } else {
        start = Math.max(totalPages - 4, 1);
      }
    }

    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
  }
  return pages;
});
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
      <div
        class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-2xl p-8 mb-8 border border-white/20 dark:border-slate-700/20 transform hover:scale-[1.02] transition-all duration-300"
      >
        <div class="flex flex-wrap items-end gap-6">
          <div class="flex-1 min-w-[200px]">
            <label
              class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                  ></path>
                </svg>
                Partner Account
              </span>
            </label>
            <input
              v-model="filters.partner_account"
              type="text"
              placeholder="กรอก Partner Account..."
              class="w-full px-4 py-3 text-gray-700 transition duration-200 bg-white border-2 border-blue-100 rounded-xl dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
              @input="applyFilters"
            />
          </div>
          <div class="flex-1 min-w-[200px]">
            <label
              class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
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
                Client UID
              </span>
            </label>
            <input
              v-model="filters.client_uid"
              type="text"
              placeholder="กรอก Client UID..."
              class="w-full px-4 py-3 text-gray-700 transition duration-200 bg-white border-2 border-blue-100 rounded-xl dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
              @input="applyFilters"
            />
          </div>
          <div class="flex-1 min-w-[200px]">
            <label
              class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                ประเทศ
              </span>
            </label>
            <input
              v-model="filters.client_country"
              type="text"
              placeholder="กรอกประเทศ..."
              class="w-full px-4 py-3 text-gray-700 transition duration-200 bg-white border-2 border-blue-100 rounded-xl dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
              @input="applyFilters"
            />
          </div>
          <div class="flex-1 min-w-[200px]">
            <label
              class="block mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
                  ></path>
                </svg>
                วันที่ลงทะเบียน
              </span>
            </label>
            <input
              v-model="filters.reg_date"
              type="date"
              class="w-full px-4 py-3 text-gray-700 transition duration-200 bg-white border-2 border-blue-100 rounded-xl dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300 focus:border-blue-500 dark:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
              @change="applyFilters"
            />
          </div>
          <div class="flex flex-none gap-2 ml-auto">
            <button
              @click="resetFilters"
              class="px-8 py-3 font-semibold text-gray-700 transition-all duration-300 transform shadow-lg rounded-xl bg-gradient-to-r from-gray-300 via-gray-200 to-gray-100 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
            >
              Reset
            </button>
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
                <tr v-if="accounts.length === 0">
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
                    {{
                      account?.client_name ||
                      account?.client_email ||
                      account?.client_id ||
                      "-"
                    }}
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
          <!-- Pagination Controls -->
          <div class="px-6 py-4 bg-white/50 dark:bg-slate-800/50 border-t border-blue-100/20 dark:border-slate-700/20">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span>Showing</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ startIndex + 1 }}</span>
                <span>to</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ endIndex }}</span>
                <span>of</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ accounts.length }}</span>
                <span>entries</span>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="goToPage(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                    border border-blue-100 dark:border-slate-600"
                >
                  Previous
                </button>
                <div class="flex items-center gap-1">
                  <button
                    v-for="page in displayedPages"
                    :key="page"
                    @click="page !== '...' ? goToPage(page) : null"
                    :disabled="page === '...'"
                    :class="[
                      'px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                      page === '...'
                        ? 'bg-gray-100 dark:bg-gray-600 text-gray-400 dark:text-gray-500 cursor-default'
                        : currentPage === page
                        ? 'bg-blue-500 text-white hover:bg-blue-600'
                        : 'bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600'
                    ]"
                  >
                    {{ page }}
                  </button>
                </div>
                <button
                  @click="goToPage(currentPage + 1)"
                  :disabled="currentPage === Math.ceil(accounts.length / itemsPerPage)"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                    border border-blue-100 dark:border-slate-600"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <!-- Pagination -->
      <div v-if="props.clients?.data?.length > 0" class="px-6 py-4 bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg border-t border-blue-100/20 dark:border-slate-700/20">
        <div class="flex items-center justify-between">
          <!-- Pagination Info -->
          <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
            <span>แสดง {{ props.clients.from }} ถึง {{ props.clients.to }} จากทั้งหมด {{ props.clients.total }} รายการ</span>
          </div>
          
          <!-- Pagination Navigation -->
          <div class="flex items-center space-x-2">
            <!-- Previous Page -->
            <Link
              v-if="props.clients.prev_page_url"
              :href="props.clients.prev_page_url"
              class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700"
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
                v-if="link.url && !link.label.includes('Previous') && !link.label.includes('Next')"
                :href="link.url"
                class="px-4 py-2 text-sm font-medium rounded-lg"
                :class="{
                  'text-white bg-blue-600 dark:bg-blue-500': link.active,
                  'text-blue-600 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-600 hover:bg-blue-50 dark:hover:bg-slate-700': !link.active
                }"
                preserve-scroll
              >
                {{ link.label }}
              </Link>
            </template>

            <!-- Next Page -->
            <Link
              v-if="props.clients.next_page_url"
              :href="props.clients.next_page_url"
              class="px-4 py-2 text-sm font-medium text-blue-600 dark:text-blue-400 bg-white dark:bg-slate-800 border border-blue-200 dark:border-slate-600 rounded-lg hover:bg-blue-50 dark:hover:bg-slate-700"
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
