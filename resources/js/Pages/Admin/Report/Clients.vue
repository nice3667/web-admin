<script setup>
import { Head, router } from "@inertiajs/vue3";
import { mdiClockOutline, mdiAlertBoxOutline, mdiAccountGroup } from "@mdi/js";
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
      overdue: 0
    })
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  error: {
    type: String,
    default: null
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
  
  router.get('/admin/reports/clients', params, {
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
  
  router.get('/admin/reports/clients', {}, {
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
    total_pending: props.stats.total_pending,
    total_amount: Number(props.stats.total_amount).toFixed(2),
    due_today: Number(props.stats.due_today).toFixed(4),
    overdue: Number(props.stats.overdue).toFixed(2),
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
      <SectionTitleLineWithButton :icon="mdiAccountGroup" title="ลูกค้า" main>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="props.error" color="danger" :icon="mdiAlertBoxOutline">
        {{ props.error }}
      </NotificationBar>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-4">
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400"
              >Total Partner Account</span
            >
            <span class="text-2xl font-bold">{{ computedStats.total_pending }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (lots)</span>
            <span class="text-2xl font-bold">{{ computedStats.total_amount }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Volume (USD)</span>
            <span class="text-2xl font-bold">{{ computedStats.due_today }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Reward (USD)</span>
            <span class="text-2xl font-bold">{{ computedStats.overdue }}</span>
          </div>
        </CardBox>
      </div>

      <CardBox class="mb-6">
        <div class="grid grid-cols-1 gap-4 mb-4 md:grid-cols-4">
          <div>
            <label
              class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
              >Search Client UID</label
            >
            <input
              v-model="filters.search"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              @input="applyFilters"
            />
          </div>
          <div>
            <label
              class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-300"
              >Status</label
            >
            <select
              v-model="filters.status"
              class="w-full border-gray-300 rounded-md shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              @change="applyFilters"
            >
              <option value="all">All Status</option>
              <option value="INACTIVE">INACTIVE</option>
              <option value="ACTIVE">ACTIVE</option>
            </select>
          </div>

          <div class="flex items-end">
            <BaseButton color="gray-500" label="Reset" @click="resetFilters" />
          </div>
        </div>
      </CardBox>

      <CardBox class="mb-6" has-table>
        <div v-if="!props.clients.length" class="p-4 text-center text-gray-600">
          <p>ไม่พบข้อมูล</p>
          <p class="mt-2 text-sm">จำนวนข้อมูล: {{ props.clients.length }}</p>
          <p class="text-sm">ข้อมูลที่กรองแล้ว: {{ filteredClients.length }}</p>
        </div>
        <table
          v-else
          class="min-w-full divide-y divide-gray-200 dark:divide-gray-700"
        >
          <thead>
            <tr>
              <th
                class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
              >
                Client UID
              </th>
              <th
                class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
              >
                Status
              </th>
              <th
                class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
              >
                Rewards (USD)
              </th>
              <th
                class="px-6 py-3 text-sm font-bold tracking-wider text-left text-gray-500 uppercase"
              >
                Rebate Amount (USD)
              </th>
            </tr>
          </thead>
          <tbody
            class="bg-white divide-y divide-gray-200 dark:bg-slate-800 dark:divide-gray-700"
          >
            <tr v-for="client in filteredClients" :key="client.client_uid">
              <td class="px-6 py-4 whitespace-nowrap">
                {{ client.client_uid }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="getStatusColor(client.client_status)">
                  {{ getStatusText(client.client_status) }}
                </span>
              </td>
              <td class="px-6 py-4 font-bold whitespace-nowrap">
                {{ client.reward_usd || "0.00" }}
              </td>
              <td class="px-6 py-4 font-bold whitespace-nowrap">
                {{
                  client.rebate_amount_usd !== undefined
                    ? client.rebate_amount_usd
                    : "-"
                }}
              </td>
            </tr>
          </tbody>
        </table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
