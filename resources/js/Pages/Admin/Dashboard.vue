<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted } from "vue";
import { useMainStore } from "@/Stores/main";
import {
  mdiAccountMultiple,
  mdiCartOutline,
  mdiChartTimelineVariant,
  mdiFinance,
  mdiMonitorCellphone,
  mdiReload,
  mdiGithub,
  mdiChartPie,
} from "@mdi/js";
import * as chartConfig from "@/Components/Charts/chart.config.js";
import LineChart from "@/Components/Charts/LineChart.vue";
import SectionMain from "@/Components/SectionMain.vue";
import CardBoxWidget from "@/Components/CardBoxWidget.vue";
import CardBox from "@/Components/CardBox.vue";
import TableSampleClients from "@/Components/TableSampleClients.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import CardBoxTransaction from "@/Components/CardBoxTransaction.vue";
import CardBoxClient from "@/Components/CardBoxClient.vue";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import SectionBannerStarOnGitHub from "@/Components/SectionBannerStarOnGitHub.vue";
import { mdiCash, mdiCurrencyUsd } from "@mdi/js";

const chartData = ref(null);
const totalAmount = ref(0);
const loading = ref(true);

const fillChartData = () => {
  chartData.value = chartConfig.sampleChartData();
};

const fetchWalletAccounts = async () => {
  try {
    loading.value = true;
    // Use CSRF token for session-based auth
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    
    const response = await fetch('/api/wallet/accounts', {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken || '',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });

    console.log('Response status:', response.status);
    console.log('Response headers:', response.headers);

    if (!response.ok) {
      const errorText = await response.text();
      console.error('Response error:', errorText);
      throw new Error(`HTTP ${response.status}: ${errorText}`);
    }

    const json = await response.json();
    console.log('API Response:', json);

    // Handle the new response format from ExnessController
    if (json.combined_wallets && Array.isArray(json.combined_wallets)) {
      totalAmount.value = json.combined_wallets.reduce((sum, account) => {
        if (account.currency === 'USD' && typeof account.balance === 'number') {
          return sum + account.balance;
        }
        return sum;
      }, 0);
    } else if (json.v1_data?.data || json.v2_data?.data) {
      // Fallback to process v1 or v2 data directly
      let accounts = [];
      if (json.v1_data?.data) accounts = accounts.concat(json.v1_data.data);
      if (json.v2_data?.data) accounts = accounts.concat(json.v2_data.data);
      
      totalAmount.value = accounts.reduce((sum, account) => {
        if (account.currency === 'USD' && typeof account.balance === 'number') {
          return sum + account.balance;
        }
        return sum;
      }, 0);
    } else {
      console.warn('No wallet data found in response:', json);
      totalAmount.value = 0;
    }
  } catch (err) {
    console.error('Error fetching wallet accounts:', err);
    totalAmount.value = 0;
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fillChartData();
  fetchWalletAccounts();
});

const mainStore = useMainStore();

/* Fetch sample data */
mainStore.fetchSampleClients();
mainStore.fetchSampleHistory();

const clientBarItems = computed(() => mainStore.clients.slice(0, 4));
const transactionBarItems = computed(() => mainStore.history);
</script>

<template>
  <LayoutSidebar></LayoutSidebar>
  <LayoutAuthenticated>
    <Head title="Dashboard" />
    <SectionMain>
      <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-1">
        <CardBoxWidget
          color="text-emerald-500"
          :icon="mdiCash"
          :number="totalAmount"
          prefix="$"
          label="จำนวนเงินทั้งหมด (USD)"
        />

        <!-- <CardBoxWidget
          trend="12%"
          trend-type="down"
          color="text-blue-500"
          :icon="mdiCartOutline"
          :number="7770"
          prefix="$"
          label="Sales"
        />
        <CardBoxWidget
          trend="Overflow"
          trend-type="alert"
          color="text-red-500"
          :icon="mdiChartTimelineVariant"
          :number="256"
          suffix="%"
          label="Performance"
        /> -->
      </div>

      <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
        <div class="flex flex-col justify-between">
          <CardBoxTransaction
            v-for="(transaction, index) in transactionBarItems"
            :key="index"
            :amount="transaction.amount"
            :date="transaction.date"
            :business="transaction.business"
            :type="transaction.type"
            :name="transaction.name"
            :account="transaction.account"
          />
        </div>
        <div class="flex flex-col justify-between">
          <CardBoxClient
            v-for="client in clientBarItems"
            :key="client.id"
            :name="client.name"
            :login="client.login"
            :date="client.created"
            :progress="client.progress"
          />
        </div>
      </div>

      <!-- <NotificationBar color="info" :icon="mdiMonitorCellphone">
        <b>Responsive table.</b> Collapses on mobile
      </NotificationBar> -->

      <!-- <CardBox :icon="mdiMonitorCellphone" title="Responsive table" has-table>
        <TableSampleClients />
      </CardBox> -->
    </SectionMain>
  </LayoutAuthenticated>
</template>
