<script setup>
import { Head } from "@inertiajs/vue3";
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
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, onMounted, computed } from "vue";
import FormControl from "@/Components/FormControl.vue";

const loading = ref(true);
const error = ref(null);
const accounts = ref([]);
const isModalActive = ref(false);

// Add temporary filters for search modal
const tempFilters = ref({
  partner_account_name: '',
  partner_account: '',
  country: '',
  client_uid: '',
  client_account: '',
  client_account_type: '',
  reg_date: '',
  currency: ''
});

// Keep the actual filters for filtering
const filters = ref({
  partner_account_name: '',
  partner_account: '',
  country: '',
  client_uid: '',
  client_account: '',
  client_account_type: '',
  reg_date: '',
  currency: ''
});

// Reset filters
const resetFilters = () => {
  tempFilters.value = {
    partner_account_name: '',
    partner_account: '',
    country: '',
    client_uid: '',
    client_account: '',
    client_account_type: '',
    reg_date: '',
    currency: ''
  };
  filters.value = {
    partner_account_name: '',
    partner_account: '',
    country: '',
    client_uid: '',
    client_account: '',
    client_account_type: '',
    reg_date: '',
    currency: ''
  };
};

// Filtered accounts
const filteredAccounts = computed(() => {
  return accounts.value.filter(account => {
    // Partner Account Name filter
    if (filters.value.partner_account_name && account.partner_account_name && 
        !account.partner_account_name.toLowerCase().includes(filters.value.partner_account_name.toLowerCase())) {
      return false
    }

    // Partner Account filter
    if (filters.value.partner_account && account.partner_account && 
        !account.partner_account.toLowerCase().includes(filters.value.partner_account.toLowerCase())) {
      return false
    }

    // Country filter
    if (filters.value.country && account.country && 
        !account.country.toLowerCase().includes(filters.value.country.toLowerCase())) {
      return false
    }

    // Client UID filter
    if (filters.value.client_uid && account.client_uid && 
        !account.client_uid.toLowerCase().includes(filters.value.client_uid.toLowerCase())) {
      return false
    }

    // Client Account filter
    if (filters.value.client_account && account.client_account && 
        !account.client_account.toLowerCase().includes(filters.value.client_account.toLowerCase())) {
      return false
    }

    // Client Account Type filter
    if (filters.value.client_account_type && account.client_account_type && 
        !account.client_account_type.toLowerCase().includes(filters.value.client_account_type.toLowerCase())) {
      return false
    }

    // Registration Date filter
    if (filters.value.reg_date && account.reg_date && 
        account.reg_date !== filters.value.reg_date) {
      return false
    }

    // Currency filter
    if (filters.value.currency && account.currency && 
        !account.currency.toLowerCase().includes(filters.value.currency.toLowerCase())) {
      return false
    }

    return true
  });
});

// Statistics
const stats = computed(() => {
  const accountsArray = filteredAccounts.value;
  const totalAccounts = accountsArray.length;
  const totalVolumeLots = accountsArray.reduce((sum, acc) => sum + (parseFloat(acc.volume_lots) || 0), 0);
  const totalVolumeUsd = accountsArray.reduce((sum, acc) => sum + (parseFloat(acc.volume_mln_usd) || 0), 0);
  const totalReward = accountsArray.reduce((sum, acc) => sum + (parseFloat(acc.reward_usd) || 0), 0);

  return {
    level1_clients: totalAccounts,
    total_accounts: totalAccounts,
    total_volume_lots: totalVolumeLots.toFixed(2),
    total_volume_usd: totalVolumeUsd.toFixed(4),
    total_profit: totalReward.toFixed(2)
  }
});

// Apply search
const applySearch = () => {
  filters.value = { ...tempFilters.value };
  isModalActive.value = false;
};

// Format functions
const formatDate = (date) => {
  if (!date) return "-";
  return new Date(date).toLocaleDateString("th-TH", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

const formatNumber = (number) => {
  if (!number) return "0";
  return new Intl.NumberFormat("th-TH", {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(number);
};

const formatCurrency = (amount) => {
  if (!amount) return "$0.00";
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount);
};

// Fetch data
const fetchAccounts = async () => {
  try {
    loading.value = true;
    error.value = null;
    const response = await fetch("http://127.0.0.1:8000/api/exness/clients");
    if (!response.ok) throw new Error("Network response was not ok");
    const json = await response.json();
    // ตรวจสอบโครงสร้างข้อมูลแบบ Clients.vue
    if (json && json.data_v1 && json.data_v2) {
      const v1Data = json.data_v1;
      const v2Data = json.data_v2;
      // สร้าง map ของ client_status และ rebate_amount_usd จาก V2 โดยใช้ 8 ตัวแรกของ client_uid
      const v2Map = {};
      if (Array.isArray(v2Data)) {
        v2Data.forEach((client) => {
          if (client.client_uid) {
            const shortUid = client.client_uid.substring(0, 8);
            v2Map[shortUid] = {
              client_status: client.client_status
                ? client.client_status.toUpperCase()
                : "UNKNOWN",
              rebate_amount_usd:
                client.rebate_amount_usd !== undefined
                  ? client.rebate_amount_usd
                  : "-",
            };
          }
        });
      }
      // รวมข้อมูล V1 กับ client_status และ rebate_amount_usd จาก V2
      if (Array.isArray(v1Data)) {
        accounts.value = v1Data.map((client) => {
          const v2 = v2Map[client.client_uid] || {};
          return {
            ...client,
            client_status: v2.client_status || "UNKNOWN",
            rebate_amount_usd:
              v2.rebate_amount_usd !== undefined ? v2.rebate_amount_usd : "-",
          };
        });
      } else {
        error.value = "รูปแบบข้อมูลไม่ถูกต้อง";
      }
    } else {
      error.value = "ไม่พบข้อมูลลูกค้า";
    }
  } catch (err) {
    console.error("Error fetching accounts:", err);
    error.value = "ไม่สามารถดึงข้อมูลบัญชีได้ กรุณาลองใหม่อีกครั้ง";
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchAccounts();
});
</script>

<template>
  <LayoutAuthenticated>
    <Head title="รายงานบัญชีลูกค้า" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountGroup"
        title="รายงานบัญชีลูกค้า"
        main
      >
      </SectionTitleLineWithButton>

      <NotificationBar v-if="error" color="danger" :icon="mdiAlertBoxOutline">
        {{ error }}
      </NotificationBar>

      <!-- Statistics Widgets -->
      <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-5">
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400"
              >จำนวนลูกค้าระดับ 1</span
            >
            <span class="text-2xl font-bold">{{ stats.level1_clients }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400"
              >จำนวนบัญชีทั้งหมด</span
            >
            <span class="text-2xl font-bold">{{ stats.total_accounts }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (lots)</span>
            <span class="text-2xl font-bold">{{
              stats.total_volume_lots
            }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (USD)</span>
            <span class="text-2xl font-bold">{{ stats.total_volume_usd }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Reward (USD)</span>
            <span class="text-2xl font-bold">{{ stats.total_profit }}</span>
          </div>
        </CardBox>
      </div>

      <!-- Search Button -->
      <div class="flex justify-end mb-6">
        <BaseButton
          label="ค้นหาข้อมูล"
          color="gray-500"
          :icon="mdiMagnify"
          @click="isModalActive = true"
        />
        <BaseButton
          label="Reset"
          color="gray-500"
          :icon="mdiRefresh"
          class="ml-2"
          @click="resetFilters"
        />
      </div>

      <!-- Table Section -->
      <CardBox class="mb-6 overflow-hidden" has-table>
        <div v-if="loading" class="p-4 text-center">
          <div
            class="w-12 h-12 mx-auto border-b-2 border-blue-500 rounded-full animate-spin"
          ></div>
          <p class="mt-2 text-gray-600">กำลังโหลดข้อมูล...</p>
        </div>
        <div v-else-if="error" class="p-4 text-center text-red-600">
          {{ error }}
        </div>
        <div v-else-if="!accounts.length" class="p-4 text-center text-gray-600">
          <p>ไม่พบข้อมูล</p>
          <p class="mt-2 text-sm">จำนวนข้อมูล: {{ accounts.length }}</p>
          <p class="text-sm">
            ข้อมูลที่กรองแล้ว: {{ filteredAccounts.length }}
          </p>
        </div>
        <div v-else class="overflow-x-auto">
          <table
            class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
          >
            <thead>
              <tr>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  partner account name
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  partner account
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  country
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  client uid
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  client account
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  client account_type
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  reg date
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  volume mln usd
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  volume lots
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  currency
                </th>
                <th
                  class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
                >
                  comment
                </th>
              </tr>
            </thead>
            <tbody
              class="bg-white divide-y divide-gray-200 dark:bg-slate-800 dark:divide-gray-700"
            >
              <tr v-for="account in filteredAccounts" :key="account.client_uid">
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.partner_account_name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.partner_account }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.country }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.client_uid }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.client_account }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.client_account_type }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatDate(account.reg_date) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatNumber(account.volume_mln_usd) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ formatNumber(account.volume_lots) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.currency }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ account.comment }}
                </td>
              </tr>
              <tr v-if="filteredAccounts.length === 0">
                <td
                  colspan="11"
                  class="px-6 py-4 text-sm text-center text-gray-500"
                >
                  <div class="flex flex-col items-center justify-center py-8">
                    <svg
                      class="w-12 h-12 mb-4 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                      />
                    </svg>
                    <p class="text-lg font-medium text-gray-900">
                      ไม่พบข้อมูลที่ค้นหา
                    </p>
                    <p class="mt-1 text-sm text-gray-500">
                      ลองปรับเงื่อนไขการค้นหาใหม่
                    </p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>

      <!-- Search Modal -->
      <CardBoxModal
        v-model="isModalActive"
        title="ค้นหาข้อมูล"
        @confirm="applySearch"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Partner Account Name</label>
            <input
              v-model="tempFilters.partner_account_name"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Partner Account</label>
            <input
              v-model="tempFilters.partner_account"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Country</label>
            <input
              v-model="tempFilters.country"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Client UID</label>
            <input
              v-model="tempFilters.client_uid"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Client Account</label>
            <input
              v-model="tempFilters.client_account"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Client Account Type</label>
            <input
              v-model="tempFilters.client_account_type"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Registration Date</label>
            <input
              v-model="tempFilters.reg_date"
              type="date"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
          <div>
            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Currency</label>
            <input
              v-model="tempFilters.currency"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            />
          </div>
        </div>
        <template #footer>
          <div class="flex justify-end gap-2">
            <BaseButton
              label="Reset"
              color="gray-500"
              :icon="mdiRefresh"
              @click="resetFilters"
            />
            <BaseButton
              label="ยกเลิก"
              color="gray-500"
              @click="isModalActive = false"
            />
            <BaseButton 
              label="ค้นหา" 
              color="gray-500" 
              @click="applySearch" 
            />
          </div>
        </template>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template>
