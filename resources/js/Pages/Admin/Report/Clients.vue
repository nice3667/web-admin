<script setup>
import { Head, router } from "@inertiajs/vue3";
import {
  mdiClockOutline,
  mdiAlertBoxOutline,
  mdiAccountGroup,
  mdiChartLine,
  mdiCurrencyUsd,
  mdiGift,
} from "@mdi/js";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, computed, watch } from "vue";
import Pagination from "@/Components/Admin/Pagination.vue";
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
      total_pending: 0,
      total_amount: 0,
      due_today: 0,
      overdue: 0,
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

  if (filters.value.status !== "all") {
    params.status = filters.value.status;
  }

  if (filters.value.date_range.start) {
    params.start_date = filters.value.date_range.start;
  }

  if (filters.value.date_range.end) {
    params.end_date = filters.value.date_range.end;
  }

  router.get("/admin/reports/clients", params, {
    preserveState: true,
    preserveScroll: true,
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

  router.get(
    "/admin/reports/clients",
    {},
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Computed properties for filtered data
const filteredClients = computed(() => {
  let result = props.clients.data || [];

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
    total_pending: props.stats.total_client_uids,
    total_amount: Number(props.stats.total_amount).toFixed(4),
    due_today: Number(props.stats.due_today).toFixed(4),
    overdue: Number(props.stats.overdue).toFixed(4),
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
watch(
  () => props.filters,
  (newFilters) => {
    filters.value = {
      search: newFilters.search || "",
      status: newFilters.status || "all",
      date_range: {
        start: newFilters.start_date || "",
        end: newFilters.end_date || "",
      },
    };
  },
  { immediate: true }
);

// Add after filteredClients computed
const itemsPerPage = 10;
const currentPage = ref(props.clients.current_page || 1);
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() =>
  Math.min(startIndex.value + itemsPerPage, filteredClients.value.length)
);
const paginatedClients = computed(() => {
  return filteredClients.value.slice(startIndex.value, endIndex.value);
});

watch(
  () => props.clients.current_page,
  (val) => {
    currentPage.value = val || 1;
  }
);

// Pagination logic (XM style)
const goToPage = (page) => {
  if (page < 1 || page > Math.ceil(filteredClients.value.length / itemsPerPage))
    return;
  currentPage.value = page;
};
const displayedPages = computed(() => {
  const totalPages = Math.ceil(filteredClients.value.length / itemsPerPage);
  const page = currentPage.value;
  const pages = [];
  if (totalPages <= 7) {
    for (let i = 1; i <= totalPages; i++) {
      pages.push(i);
    }
  } else {
    let start = Math.max(page - 3, 1);
    let end = Math.min(page + 3, totalPages);
    if (start > 1) {
      pages.push(1);
      if (start > 2) {
        pages.push("...");
      }
    }
    for (let i = start; i <= end; i++) {
      pages.push(i);
    }
    if (end < totalPages) {
      if (end < totalPages - 1) {
        pages.push("...");
      }
      pages.push(totalPages);
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
          รายงานลูกค้า
        </h1>
        <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
          ดูและวิเคราะห์ข้อมูลลูกค้าและสถิติการเทรด
        </p>
      </div>

      <!-- ลูกค้า Partner Label -->
      <div
        class="mb-2 text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in"
      >
        ลูกค้า Partner
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
                ค้นหา Client UID
              </span>
            </label>
            <input
              v-model="filters.search"
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
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                  ></path>
                </svg>
                สถานะ
              </span>
            </label>
            <select
              v-model="filters.status"
              class="w-full px-4 py-3 text-gray-700 transition duration-200 bg-white border-2 border-blue-100 rounded-xl dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300 focus:border-blue-500 dark:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
              @change="applyFilters"
            >
              <option value="all">ทุกสถานะ</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="INACTIVE">INACTIVE</option>
            </select>
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
                ช่วงวันที่
              </span>
            </label>
            <input
              v-model="filters.date_range.start"
              type="date"
              class="w-full px-4 py-3 text-gray-700 transition duration-200 bg-white border-2 border-blue-100 rounded-xl dark:border-slate-600 dark:bg-slate-800 dark:text-gray-300 focus:border-blue-500 dark:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800"
              @change="applyFilters"
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
                ถึงวันที่
              </span>
            </label>
            <input
              v-model="filters.date_range.end"
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                จำนวน Client UID
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Volume (lots)
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Volume (USD)
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
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
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">
                Reward (USD)
              </p>
              <p
                class="mt-2 text-3xl font-extrabold text-transparent bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text"
              >
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
              รายชื่อลูกค้า
            </h3>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              ข้อมูลลูกค้าทั้งหมดและสถานะปัจจุบัน
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
                    Client UID
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    สถานะ
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Rewards (USD)
                  </th>
                  <th
                    scope="col"
                    class="px-6 py-4 text-xs font-semibold tracking-wider text-left text-gray-600 uppercase dark:text-gray-300"
                  >
                    Rebate Amount (USD)
                  </th>
                </tr>
              </thead>
              <tbody
                class="divide-y bg-white/50 dark:bg-slate-800/50 divide-blue-100/20 dark:divide-slate-700/20"
              >
                <tr v-if="paginatedClients.length === 0">
                  <td
                    colspan="4"
                    class="px-6 py-12 text-sm text-center text-gray-600 dark:text-gray-400"
                  >
                    ไม่พบข้อมูลลูกค้า
                  </td>
                </tr>
                <tr
                  v-for="client in paginatedClients"
                  :key="client.client_uid"
                  class="transition-colors duration-200 hover:bg-blue-50/50 dark:hover:bg-slate-700/50"
                >
                  <td
                    class="px-6 py-4 text-sm font-medium text-gray-900 whitespace-nowrap dark:text-white"
                  >
                    {{ client.client_uid }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm whitespace-nowrap"
                    :class="getStatusColor(client.client_status)"
                  >
                    <span
                      :class="[
                        'px-4 py-1.5 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full',
                        client.client_status === 'ACTIVE'
                          ? 'bg-green-100/80 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                          : client.client_status === 'INACTIVE'
                          ? 'bg-red-100/80 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                          : 'bg-gray-100/80 text-gray-800 dark:bg-gray-900/20 dark:text-gray-200',
                      ]"
                    >
                      <span class="relative flex w-2 h-2">
                        <span
                          :class="[
                            'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                            client.client_status === 'ACTIVE'
                              ? 'bg-green-400'
                              : client.client_status === 'INACTIVE'
                              ? 'bg-red-400'
                              : 'bg-gray-400',
                          ]"
                        ></span>
                        <span
                          :class="[
                            'relative inline-flex rounded-full h-2 w-2',
                            client.client_status === 'ACTIVE'
                              ? 'bg-green-500'
                              : client.client_status === 'INACTIVE'
                              ? 'bg-red-500'
                              : 'bg-gray-500',
                          ]"
                        ></span>
                      </span>
                      {{ getStatusText(client.client_status) }}
                    </span>
                  </td>
                  <td
                    class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300"
                  >
                    {{ client.reward_usd }}
                  </td>
                  <td
                    class="px-6 py-4 text-sm text-gray-600 whitespace-nowrap dark:text-gray-300"
                  >
                    {{
                      client.rebate_amount_usd !== undefined
                        ? "$" + Number(client.rebate_amount_usd).toFixed(4)
                        : "-"
                    }}
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
                  filteredClients.length
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
                    currentPage ===
                      Math.ceil(filteredClients.length / itemsPerPage) ||
                    filteredClients.length === 0
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
