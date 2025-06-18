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

const props = defineProps({
  clients: {
    type: Object,
    required: true,
    default: () => ({
      data: [],
      current_page: 1,
      from: 0,
      to: 0,
      total: 0,
      prev_page_url: null,
      next_page_url: null,
      last_page: 1,
    }),
  },
  stats: {
    type: Object,
    required: true,
    default: () => ({
      total_accounts: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_profit: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
});

const isModalActive = ref(false);

// Search filters
const tempFilters = ref({
  partner_account: props.filters.partner_account || "",
  client_uid: props.filters.client_uid || "",
  client_country: props.filters.client_country || "",
  client_status: props.filters.client_status || "",
  reg_date: props.filters.reg_date || "",
  kyc_passed: props.filters.kyc_passed || "",
});

const filters = ref({
  partner_account: props.filters.partner_account || "",
  client_uid: props.filters.client_uid || "",
  client_country: props.filters.client_country || "",
  client_status: props.filters.client_status || "",
  reg_date: props.filters.reg_date || "",
  kyc_passed: props.filters.kyc_passed || "",
});

// Computed properties for data
const accounts = computed(() => {
  return props.clients?.data || [];
});

const currentPage = computed(() => {
  return props.clients?.current_page || 1;
});

const totalPages = computed(() => {
  return props.clients?.last_page || 1;
});

const itemsPerPage = ref(10);

// Function to navigate to different page
const goToPage = (page) => {
  if (page < 1 || page > totalPages.value) return;

  router.get(
    "/admin/reports/client-account",
    {
      page,
      per_page: itemsPerPage.value,
      ...filters.value,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

// Apply search filters
const applySearch = () => {
  filters.value = { ...tempFilters.value };
  router.get(
    "/admin/reports/client-account",
    {
      page: 1,
      per_page: itemsPerPage.value,
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
    "/admin/reports/client-account",
    {
      page: 1,
      per_page: itemsPerPage.value,
    },
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
  if (!code || code === "-") return "-";
  return countryNames[code] || code;
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
    total_volume_lots: Number(stats.total_volume_lots ?? 0).toFixed(2),
    total_volume_usd: Number(stats.total_volume_usd ?? 0).toFixed(4),
    total_profit: Number(stats.total_profit ?? 0).toFixed(2)
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
    <Head title="รายงานลูกค้า" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountGroup"
        title="รายงานลูกค้า"
        main
      >
        <div class="flex space-x-2">
          <BaseButton
            :icon="mdiMagnify"
            label="ค้นหา"
            color="contrast"
            @click="isModalActive = true"
          />
        </div>
      </SectionTitleLineWithButton>

      <!-- สถิติ -->
      <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2 lg:grid-cols-4">
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">จำนวนบัญชีทั้งหมด</span>
            <span class="text-2xl font-bold">{{ formattedStats.total_accounts }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (lots)</span>
            <span class="text-2xl font-bold">{{ formattedStats.total_volume_lots }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (USD)</span>
            <span class="text-2xl font-bold">{{ formattedStats.total_volume_usd }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">กำไร (USD)</span>
            <span class="text-2xl font-bold">{{ formattedStats.total_profit }}</span>
          </div>
        </CardBox>
      </div>

      <!-- Filter/Search Bar เหมือน Clients.vue -->
      <CardBox class="mb-6">
        <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-4">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Search Client UID</label>
            <input
              v-model="filters.client_uid"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              @input="applySearch"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
            <select
              v-model="filters.client_status"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              @change="applySearch"
            >
              <option value="">All Status</option>
              <option value="INACTIVE">INACTIVE</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="PENDING">PENDING</option>
            </select>
          </div>
          <div class="flex items-end">
            <BaseButton color="gray-500" label="Reset" @click="resetFilters" />
          </div>
        </div>
      </CardBox>

      <!-- ตารางข้อมูล -->
      <CardBox class="mb-6" has-table>
        <div v-if="!accounts.length" class="p-4 text-center text-gray-600">
          <p>ไม่พบข้อมูล</p>
          <p class="mt-2 text-sm">จำนวนข้อมูล: {{ accounts.length }}</p>
        </div>
        <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
          <thead>
            <tr>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Partner Account</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Client UID</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Registration Date</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Country</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Volume (lots)</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Volume (USD)</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Reward (USD)</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">Status</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">KYC</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">FTD</th>
              <th class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase">FTT</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 dark:bg-slate-800 dark:divide-gray-700">
            <tr v-for="account in accounts" :key="account?.client_uid || Math.random()">
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.partner_account || "-" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.client_uid || "-" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.reg_date ? formatDate(account.reg_date) : "-" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.client_country ? formatCountry(account.client_country) : "-" }}</td>
              <td class="px-6 py-4 font-bold whitespace-nowrap">{{ account?.volume_lots ? formatNumber(account.volume_lots) : "0" }}</td>
              <td class="px-6 py-4 font-bold whitespace-nowrap">{{ account?.volume_mln_usd ? formatCurrency(account.volume_mln_usd) : "$0" }}</td>
              <td class="px-6 py-4 font-bold whitespace-nowrap">{{ account?.reward_usd ? formatCurrency(account.reward_usd) : "$0" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.client_status || "-" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.kyc_passed ? "ผ่าน" : "ไม่ผ่าน" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.ftd_received ? "มี" : "ไม่มี" }}</td>
              <td class="px-6 py-4 whitespace-nowrap">{{ account?.ftt_made ? "มี" : "ไม่มี" }}</td>
            </tr>
          </tbody>
        </table>
      </CardBox>

      <!-- Pagination -->
      <div class="flex justify-center mt-4">
        <nav class="flex items-center">
          <button
            class="px-3 py-1 border rounded-l"
            :disabled="currentPage === 1"
            @click="goToPage(currentPage - 1)"
          >
            ก่อนหน้า
          </button>
          <span class="px-3 py-1 border-t border-b">
            หน้า {{ currentPage }} จาก {{ totalPages }}
          </span>
          <button
            class="px-3 py-1 border rounded-r"
            :disabled="currentPage === totalPages"
            @click="goToPage(currentPage + 1)"
          >
            ถัดไป
          </button>
        </nav>
      </div>

      <!-- Search Modal -->
      <CardBoxModal
        v-model="isModalActive"
        title="ค้นหาข้อมูล"
        button="info"
        has-cancel
        @confirm="applySearch"
        @cancel="resetFilters"
      >
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
          <FormControl
            v-model="tempFilters.partner_account"
            label="Partner Account"
            placeholder="กรอก Partner Account"
          />
          <FormControl
            v-model="tempFilters.client_uid"
            label="Client UID"
            placeholder="กรอก Client UID"
          />
          <FormControl
            v-model="tempFilters.client_country"
            label="ประเทศ"
            placeholder="กรอกรหัสประเทศ"
          />
          <FormControl
            v-model="tempFilters.client_status"
            label="สถานะ"
            placeholder="กรอกสถานะ"
          />
          <FormControl
            v-model="tempFilters.reg_date"
            label="วันที่ลงทะเบียน"
            type="date"
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
          />
        </div>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
