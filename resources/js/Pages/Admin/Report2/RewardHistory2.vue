<script setup>
import { Head } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import { computed } from 'vue';

const props = defineProps({
  rewards: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_rewards: 0,
      total_amount: 0,
      total_volume: 0,
      data_source: 'unknown'
    })
  },
  error: {
    type: String,
    default: null
  }
});

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

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  try {
    return new Date(dateStr).toLocaleDateString('th-TH');
  } catch {
    return dateStr;
  }
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
    <Head title="ประวัติการจ่ายรางวัล - Report2" />
    
    <SectionMain>
      <SectionTitleLineWithButton
        title="ประวัติการจ่ายรางวัล - Report2 (Kantapong)"
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
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-6">
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-green-600">{{ formatNumber(stats.total_rewards) }}</div>
            <div class="text-sm text-gray-600">Total Rewards</div>
          </div>
        </CardBox>
        
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(stats.total_amount) }}</div>
            <div class="text-sm text-gray-600">Total Amount</div>
          </div>
        </CardBox>
        
        <CardBox>
          <div class="text-center">
            <div class="text-2xl font-bold text-blue-600">{{ formatNumber(stats.total_volume) }}</div>
            <div class="text-sm text-gray-600">Total Volume (Lots)</div>
          </div>
        </CardBox>
      </div>

      <!-- Reward History Table -->
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
                  Reward Date
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Reward Amount
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Volume (Lots)
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
              <tr v-if="rewards.length === 0">
                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                  ไม่พบประวัติการจ่ายรางวัล
                </td>
              </tr>
              <tr v-for="reward in rewards" :key="reward.id || reward.client_uid" class="hover:bg-gray-50">
                <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ reward.client_uid }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ reward.partner_account }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(reward.reward_date) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold text-green-600">
                  {{ formatCurrency(reward.reward_amount) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatNumber(reward.volume_lots) }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ reward.client_country }}
                </td>
                <td class="px-4 py-4 whitespace-nowrap">
                  <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                    {{ reward.status }}
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