<script setup>
import { Head } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import BaseLevel from "@/Components/BaseLevel.vue";
import BaseButton from "@/Components/BaseButton.vue";
import FormField from "@/Components/FormField.vue";
import FormControl from "@/Components/FormControl.vue";
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_transactions: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_reward_usd: 0,
      data_source: 'unknown'
    })
  },
  currentFilters: {
    type: Object,
    default: () => ({
      search: ''
    })
  },
  error: {
    type: String,
    default: null
  }
});

const searchForm = ref({
  search: props.currentFilters.search
});

const submitSearch = () => {
  router.get(route('admin.reports2.client-transaction2'), searchForm.value, {
    preserveState: true,
    replace: true
  });
};

const clearSearch = () => {
  searchForm.value.search = '';
  submitSearch();
};

const formatNumber = (num) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 4
  }).format(num || 0);
};

const formatCurrency = (num) => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 2
  }).format(num || 0);
};

const dataSourceBadge = computed(() => {
  switch (props.stats.data_source) {
    case 'exness_api':
      return { text: 'Exness API', color: 'bg-green-500' };
    case 'database':
      return { text: 'Database', color: 'bg-yellow-500' };
    case 'error':
      return { text: 'Error', color: 'bg-red-500' };
    default:
      return { text: 'Unknown', color: 'bg-gray-500' };
  }
});
</script>

<template>
  <LayoutAuthenticated>
    <Head title="ธุรกรรมของลูกค้า - Report2" />
    
    <SectionMain>
      <SectionTitleLineWithButton
        title="ธุรกรรมของลูกค้า - Report2 (Kantapong)"
        main
      >
        <span 
          :class="[dataSourceBadge.color, 'text-white px-2 py-1 rounded text-xs']"
        >
          {{ dataSourceBadge.text }}
        </span>
      </SectionTitleLineWithButton>

      <!-- Error Message -->
      <div v-if="error" class="mb-6 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
        {{ error }}
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-4 mb-6">
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ formatNumber(stats.total_transactions) }}</div>
            <div class="text-sm text-gray-600">Total Transactions</div>
          </div>
        </CardBox>
        
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ formatNumber(stats.total_volume_lots) }}</div>
            <div class="text-sm text-gray-600">Volume (Lots)</div>
          </div>
        </CardBox>
        
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.total_volume_usd) }}</div>
            <div class="text-sm text-gray-600">Volume (USD)</div>
          </div>
        </CardBox>
        
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(stats.total_reward_usd) }}</div>
            <div class="text-sm text-gray-600">Total Reward</div>
          </div>
        </CardBox>
      </div>

      <!-- Search Form -->
      <CardBox class="mb-6">
        <form @submit.prevent="submitSearch">
          <BaseLevel>
            <div class="flex-1">
              <FormField label="ค้นหา">
                <FormControl
                  v-model="searchForm.search"
                  placeholder="ค้นหาด้วย Client UID หรือ Partner Account..."
                  :icon="'search'"
                />
              </FormField>
            </div>
            <div class="ml-4 flex space-x-2">
              <BaseButton
                type="submit"
                color="info"
                label="ค้นหา"
              />
              <BaseButton
                type="button"
                color="light"
                label="ล้าง"
                @click="clearSearch"
              />
            </div>
          </BaseLevel>
        </form>
      </CardBox>

      <!-- Transactions Table -->
      <CardBox has-table>
        <div class="overflow-x-auto">
          <table class="min-w-full">
            <thead>
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Client UID
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Partner Account
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Transaction Date
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Volume (Lots)
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Volume (USD)
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Reward (USD)
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Country
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-if="transactions.length === 0">
                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                  ไม่พบข้อมูลธุรกรรม
                </td>
              </tr>
              <tr v-for="transaction in transactions" :key="transaction.id || transaction.client_uid" class="hover:bg-gray-50">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ transaction.client_uid }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ transaction.partner_account }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ transaction.transaction_date }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatNumber(transaction.volume_lots) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(transaction.volume_usd) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatCurrency(transaction.reward_usd) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ transaction.client_country }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span 
                    :class="[
                      'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                      transaction.status === 'ACTIVE' ? 'bg-green-100 text-green-800' :
                      transaction.status === 'PENDING' ? 'bg-yellow-100 text-yellow-800' :
                      'bg-gray-100 text-gray-800'
                    ]"
                  >
                    {{ transaction.status }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template> 