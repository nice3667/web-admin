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
  mdiChartLine,
  mdiCashMultiple,
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
import { ref, onMounted, computed, watch } from "vue";
import FormControl from "@/Components/FormControl.vue";
import axios from "axios";

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
      next_page_url: null
    })
  },
  stats: {
    type: Object,
    required: true,
    default: () => ({
      total_accounts: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_profit: 0
    })
  }
});

const loading = ref(false);
const error = ref(null);
const isModalActive = ref(false);

// Search filters
const tempFilters = ref({
  partner_account: '',
  client_uid: '',
  client_country: '',
  client_status: '',
  reg_date: '',
  kyc_passed: ''
});

const filters = ref({
  partner_account: '',
  client_uid: '',
  client_country: '',
  client_status: '',
  reg_date: '',
  kyc_passed: ''
});

const accounts = ref(null);
const stats = ref(null);
const currentPage = ref(1);
const totalPages = ref(1);
const itemsPerPage = ref(10);

const fetchData = async (page = 1) => {
  loading.value = true;
  error.value = null;
  try {
    console.log('Fetching data from database...');
    const response = await axios.get('/api/clients', {
      params: {
        page,
        per_page: itemsPerPage.value,
        ...filters.value
      },
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Accept': 'application/json'
      }
    });

    console.log('Response:', response.data);
    
    if (response.data.data) {
      // Ensure we have valid data before assigning
      const clientsData = response.data.data.clients?.data || [];
      accounts.value = clientsData.filter(client => client && client.client_uid);
      stats.value = response.data.data.stats || {
        total_accounts: 0,
        total_volume_lots: 0,
        total_volume_usd: 0,
        total_profit: 0
      };
      currentPage.value = response.data.data.clients?.current_page || 1;
      totalPages.value = response.data.data.clients?.last_page || 1;
      
      // Log the data for debugging
      console.log('Accounts:', accounts.value);
      console.log('Stats:', stats.value);
    } else {
      console.error('Invalid response format:', response.data);
      error.value = 'รูปแบบข้อมูลไม่ถูกต้อง';
      // Set default values
      accounts.value = [];
      stats.value = {
        total_accounts: 0,
        total_volume_lots: 0,
        total_volume_usd: 0,
        total_profit: 0
      };
    }
  } catch (err) {
    console.error('Error fetching data:', err);
    error.value = err.response?.data?.message || 'เกิดข้อผิดพลาดในการดึงข้อมูล';
    // Set default values on error
    accounts.value = [];
    stats.value = {
      total_accounts: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_profit: 0
    };
  } finally {
    loading.value = false;
  }
};

// Apply search filters
const applySearch = () => {
  filters.value = { ...tempFilters.value };
  fetchData(1);
  isModalActive.value = false;
};

// Reset filters
const resetFilters = () => {
  tempFilters.value = {
    partner_account: '',
    client_uid: '',
    client_country: '',
    client_status: '',
    reg_date: '',
    kyc_passed: ''
  };
  filters.value = { ...tempFilters.value };
  fetchData(1);
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
  if (!date) return '-'
  return new Date(date).toLocaleDateString('th-TH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Format country code to name
const formatCountry = (code) => {
  if (!code || code === '-') return '-'
  return countryNames[code] || code
}

// Get status class
const getStatusClass = (status) => {
  const classes = {
    'ACTIVE': 'bg-green-100 text-green-800',
    'INACTIVE': 'bg-red-100 text-red-800',
    'PENDING': 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Add country code to name mapping
const countryNames = {
  'TH': 'Thailand',
  'US': 'United States',
  'GB': 'United Kingdom',
  'CN': 'China',
  'JP': 'Japan',
  'KR': 'South Korea',
  'SG': 'Singapore',
  'MY': 'Malaysia',
  'ID': 'Indonesia',
  'VN': 'Vietnam',
  'PH': 'Philippines',
  'MM': 'Myanmar',
  'KH': 'Cambodia',
  'LA': 'Laos',
  'BN': 'Brunei'
}

// Computed properties for stats
const totalVolumeLots = computed(() => {
  return Number(stats.value?.total_volume_lots || 0);
});

const totalVolumeUsd = computed(() => {
  return Number(stats.value?.total_volume_usd || 0);
});

const totalReward = computed(() => {
  return Number(stats.value?.total_profit || 0);
});

// Add onMounted hook to fetch data when component is mounted
onMounted(() => {
  console.log('Component mounted, fetching initial data...');
  fetchData();
});

// Add watcher for props.clients
watch(() => props.clients, (newClients) => {
  console.log('Clients prop changed:', newClients);
  if (newClients && newClients.data) {
    accounts.value = newClients.data;
    currentPage.value = newClients.current_page;
    totalPages.value = newClients.last_page;
  }
}, { immediate: true });

// Add watcher for props.stats
watch(() => props.stats, (newStats) => {
  console.log('Stats prop changed:', newStats);
  if (newStats) {
    stats.value = newStats;
  }
}, { immediate: true });
</script>

<template>
  <LayoutAuthenticated>
    <Head title="รายงานลูกค้า" />
    <SectionMain>
      <SectionTitleLineWithButton :icon="mdiAccountGroup" title="รายงานลูกค้า" main>
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
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <CardBoxWidget
          :trend="stats.total_accounts"
          :trend-type="'up'"
          :icon="mdiAccountGroup"
          :icon-color="'text-blue-500'"
          :number="stats.total_accounts"
          :label="'จำนวนบัญชีทั้งหมด'"
        />
        <CardBoxWidget
          :trend="stats.total_volume_lots"
          :trend-type="'up'"
          :icon="mdiChartLine"
          :icon-color="'text-green-500'"
          :number="stats.total_volume_lots"
          :label="'Volume (lots)'"
        />
        <CardBoxWidget
          :trend="stats.total_volume_usd"
          :trend-type="'up'"
          :icon="mdiCurrencyUsd"
          :icon-color="'text-yellow-500'"
          :number="stats.total_volume_usd"
          :label="'Volume (USD)'"
        />
        <CardBoxWidget
          :trend="stats.total_profit"
          :trend-type="'up'"
          :icon="mdiCashMultiple"
          :icon-color="'text-purple-500'"
          :number="stats.total_profit"
          :label="'กำไร (USD)'"
        />
      </div>

      <!-- ตารางข้อมูล -->
      <CardBox class="mb-6">
        <table>
          <thead>
            <tr>
              <th>Partner Account</th>
              <th>Client UID</th>
              <th>Registration Date</th>
              <th>Country</th>
              <th>Volume (lots)</th>
              <th>Volume (USD)</th>
              <th>Reward (USD)</th>
              <th>Status</th>
              <th>KYC</th>
              <th>FTD</th>
              <th>FTT</th>
            </tr>
          </thead>
          <tbody>
            <template v-if="accounts && accounts.length > 0">
              <tr v-for="account in accounts" :key="account?.client_uid || Math.random()">
                <td>{{ account?.partner_account || '-' }}</td>
                <td>{{ account?.client_uid || '-' }}</td>
                <td>{{ account?.reg_date ? formatDate(account.reg_date) : '-' }}</td>
                <td>{{ account?.client_country ? formatCountry(account.client_country) : '-' }}</td>
                <td>{{ account?.volume_lots ? formatNumber(account.volume_lots) : '0' }}</td>
                <td>{{ account?.volume_mln_usd ? formatCurrency(account.volume_mln_usd) : '$0' }}</td>
                <td>{{ account?.reward_usd ? formatCurrency(account.reward_usd) : '$0' }}</td>
                <td>{{ account?.client_status || '-' }}</td>
                <td>{{ account?.kyc_passed ? 'ผ่าน' : 'ไม่ผ่าน' }}</td>
                <td>{{ account?.ftd_received ? 'มี' : 'ไม่มี' }}</td>
                <td>{{ account?.ftt_made ? 'มี' : 'ไม่มี' }}</td>
              </tr>
            </template>
            <tr v-else>
              <td colspan="11" class="text-center py-4">
                {{ loading ? 'กำลังโหลดข้อมูล...' : 'ไม่พบข้อมูล' }}
              </td>
            </tr>
          </tbody>
        </table>
      </CardBox>

      <!-- Pagination -->
      <div class="flex justify-center mt-4">
        <nav class="flex items-center">
          <button
            class="px-3 py-1 rounded-l border"
            :disabled="currentPage === 1"
            @click="fetchData(currentPage - 1)"
          >
            ก่อนหน้า
          </button>
          <span class="px-3 py-1 border-t border-b">
            หน้า {{ currentPage }} จาก {{ totalPages }}
          </span>
          <button
            class="px-3 py-1 rounded-r border"
            :disabled="currentPage === totalPages"
            @click="fetchData(currentPage + 1)"
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
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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
              { value: '0', label: 'ไม่ผ่าน' }
            ]"
          />
        </div>
      </CardBoxModal>

      <!-- Loading -->
      <NotificationBar
        v-if="loading"
        color="info"
        :icon="mdiRefresh"
        class="animate-spin"
      >
        กำลังโหลดข้อมูล...
      </NotificationBar>

      <!-- Error -->
      <NotificationBar
        v-if="error"
        color="danger"
        :icon="mdiAlertBoxOutline"
      >
        {{ error }}
      </NotificationBar>
    </SectionMain>
  </LayoutAuthenticated>
</template>
