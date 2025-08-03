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
import CardBoxModal from "@/Components/CardBoxModal.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, onMounted, computed, watch, onUnmounted } from "vue";
import FormControl from "@/Components/FormControl.vue";
import Pagination from "@/Components/Admin/Pagination.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import TopNavBar from "@/Components/TopNavBar.vue";
import BottomNavBar from "@/Components/BottomNavBar.vue";

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
  error: {
    type: String,
    default: null,
  },
  user_email: {
    type: String,
    default: null,
  },
  data_source: {
    type: String,
    default: "Database",
  },
});

const isModalActive = ref(false);
const showAdvancedSearch = ref(false);

const filters = ref({
  client_account: "",
  partner_account: "",
  client_uid: "",
  client_country: "",
  client_status: "",
  reg_date: "",
});

const signUpDateFilter = ref("all");
const startDate = ref("");
const endDate = ref("");

const accounts = computed(() => {
  return props.clients.data || [];
});

// Filtered accounts based on search input
const filteredAccounts = computed(() => {
  let result = accounts.value;

  if (filters.value.client_account) {
    const searchLower = filters.value.client_account.toLowerCase();
    result = result.filter(
      (account) =>
        (account.client_account || "").toLowerCase().includes(searchLower) ||
        (account.client_uid || "").toLowerCase().includes(searchLower) ||
        (account.partner_account || "").toLowerCase().includes(searchLower)
    );
  }

  if (filters.value.partner_account) {
    const searchLower = filters.value.partner_account.toLowerCase();
    result = result.filter((account) =>
      (account.partner_account || "").toLowerCase().includes(searchLower)
    );
  }

  if (filters.value.client_uid) {
    const searchLower = filters.value.client_uid.toLowerCase();
    result = result.filter((account) =>
      (account.client_uid || "").toLowerCase().includes(searchLower)
    );
  }

  if (filters.value.client_country) {
    const searchLower = filters.value.client_country.toLowerCase();
    result = result.filter((account) =>
      (account.client_country || "").toLowerCase().includes(searchLower)
    );
  }

  if (filters.value.client_status) {
    result = result.filter(
      (account) =>
        (account.client_status || "").toLowerCase() ===
        filters.value.client_status.toLowerCase()
    );
  }

  if (filters.value.reg_date) {
    result = result.filter((account) => {
      if (!account.reg_date) return false;
      const accountDate = new Date(account.reg_date)
        .toISOString()
        .split("T")[0];
      return accountDate === filters.value.reg_date;
    });
  }

  if (signUpDateFilter.value !== "all") {
    const now = new Date();
    let start = null;
    let end = null;

    if (signUpDateFilter.value === "1m") {
      start = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate());
      end = now;
    } else if (signUpDateFilter.value === "2m") {
      start = new Date(now.getFullYear(), now.getMonth() - 2, now.getDate());
      end = now;
    } else if (
      signUpDateFilter.value === "custom" &&
      startDate.value &&
      endDate.value
    ) {
      start = new Date(startDate.value);
      end = new Date(endDate.value);
    }

    if (start && end) {
      result = result.filter((account) => {
        if (!account.reg_date) return false;
        const regDate = new Date(account.reg_date);
        return regDate >= start && regDate <= end;
      });
    }
  }

  return result;
});

// Apply search filters with debounce
let searchTimeout = null;
const applyFilters = () => {
  // Clear existing timeout
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
  
  // Set new timeout for debounced search
  searchTimeout = setTimeout(() => {
    currentPage.value = 1; // Reset to the first page when filters are applied
    // Use client-side filtering instead of server request
    // router.get(
    //   "/admin/reports/client-account",
    //   { ...filters.value },
    //   {
    //     preserveState: true,
    //     preserveScroll: true,
    //   }
    // );
    showAdvancedSearch.value = false;
  }, 300); // 300ms delay
};

// Reset filters
const resetFilters = () => {
  filters.value = {
    client_account: "",
    partner_account: "",
    client_uid: "",
    client_country: "",
    client_status: "",
    reg_date: "",
  };
  signUpDateFilter.value = "all";
  startDate.value = "";
  endDate.value = "";
  currentPage.value = 1; // Reset to the first page when filters are reset
  // Use client-side filtering instead of server request
  // router.get(
  //   "/admin/reports/client-account",
  //   {},
  //   {
  //     preserveState: true,
  //     preserveScroll: true,
  //   }
  // );
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
    total_client_uids: stats.total_client_uids ?? 0,
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

// Add after filteredAccounts computed
const itemsPerPage = 15;
const currentPage = ref(props.clients.current_page || 1);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() =>
  Math.min(startIndex.value + itemsPerPage, accounts.value.length)
);
const paginatedAccounts = computed(() => {
  return filteredAccounts.value.slice(startIndex.value, endIndex.value);
});

watch(
  () => props.clients.current_page,
  (val) => {
    currentPage.value = val || 1;
  }
);

// Pagination logic (XM style)
const goToPage = (page) => {
  if (page < 1 || page > Math.ceil(accounts.value.length / itemsPerPage))
    return;
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

// Cleanup timeout on component unmount
onUnmounted(() => {
  if (searchTimeout) {
    clearTimeout(searchTimeout);
  }
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
          บัญชีลูกค้า
        </h1>
        <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
          ดูและวิเคราะห์ข้อมูลบัญชีลูกค้าและสถิติการเทรด
        </p>
      </div>

      <!-- ลูกค้า Partner Label -->
      <div
        class="mb-2 text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in"
      >
        ลูกค้า Partner (Janischa)
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
        class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl p-4 sm:p-6 lg:p-8 mb-6 lg:mb-8 border border-white/20 dark:border-slate-700/20 transform hover:scale-[1.01] lg:hover:scale-[1.02] transition-all duration-300"
      >
        <div
          class="grid items-end grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 lg:gap-6"
        >
          <div class="col-span-1">
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
                    d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 9h2M12 17h.01"
                  />
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
            />
          </div>

          <div class="col-span-1 sm:col-span-2 lg:col-span-1">
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
                  />
                </svg>
                Sign Up Date
              </span>
            </label>
            <select
              v-model="signUpDateFilter"
              @change="applyFilters"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
            >
              <option value="all">ทั้งหมด</option>
              <option value="1m">เปิดบัญชีเมื่อ 1 เดือนที่แล้ว</option>
              <option value="2m">เปิดบัญชีเมื่อ 2 เดือนที่แล้ว</option>
              <option value="custom">เลือกวันที่เอง</option>
            </select>
          </div>

          <!-- Reset Button -->
          <div class="col-span-1">
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
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
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
          <div
            v-if="signUpDateFilter === 'custom'"
            class="col-span-1 sm:col-span-2 lg:col-span-3 xl:col-span-5"
          >
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
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
                      />
                    </svg>
                    Start Date
                  </span>
                </label>
                <input
                  type="date"
                  v-model="startDate"
                  @change="applyFilters"
                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                />
              </div>
              <div>
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
                      />
                    </svg>
                    End Date
                  </span>
                </label>
                <input
                  type="date"
                  v-model="endDate"
                  @change="applyFilters"
                  class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                />
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                บัญชีทั้งหมด
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
                {{ formattedStats.total_accounts }}
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Volume (lots)
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
                {{ formattedStats.total_volume_lots }}
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Volume (USD)
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
                {{ formattedStats.total_volume_usd }}
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Reward (USD)
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
                {{ formattedStats.total_profit }}
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
          <!-- Pagination Controls (XM style) -->
          <div
            class="px-6 py-4 border-t bg-white/50 dark:bg-slate-800/50 border-blue-100/20 dark:border-slate-700/20"
          >
            <div class="flex items-center justify-between">
              <div
                class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400"
              >
                <span>Showing</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  startIndex + 1
                }}</span>
                <span>to</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  endIndex
                }}</span>
                <span>of</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  accounts.length
                }}</span>
                <span>entries</span>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="goToPage(currentPage - 1)"
                  :disabled="currentPage === 1"
                  class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-blue-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600 dark:border-slate-600"
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
                        : 'bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600',
                    ]"
                  >
                    {{ page }}
                  </button>
                </div>
                <button
                  @click="goToPage(currentPage + 1)"
                  :disabled="
                    currentPage === Math.ceil(accounts.length / itemsPerPage)
                  "
                  class="px-4 py-2 text-sm font-medium text-gray-700 transition-colors duration-200 bg-white border border-blue-100 rounded-lg disabled:opacity-50 disabled:cursor-not-allowed dark:bg-slate-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600 dark:border-slate-600"
                >
                  Next
                </button>
              </div>
            </div>
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
