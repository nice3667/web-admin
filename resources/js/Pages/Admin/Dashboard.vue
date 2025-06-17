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
  mdiAlertBoxOutline,
  mdiAlertCircle,
  mdiCashMultiple,
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
import axios from 'axios';

const props = defineProps({
    title: {
        type: String,
        default: 'Dashboard'
    }
});

const chartData = ref(null);
const totalAmount = ref(0);
const loading = ref(true);
const exnessError = ref('');
const walletAccounts = ref([]);
const error = ref(null);

const fillChartData = () => {
  chartData.value = chartConfig.sampleChartData();
};

const fetchWalletAccounts = async () => {
  try {
    const response = await axios.get('/api/wallet/accounts');
    walletAccounts.value = response.data;
    loading.value = false;
  } catch (err) {
    error.value = err.message;
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
  <Head :title="title" />

  <LayoutAuthenticated>
    <NotificationBar v-if="error" color="danger" :icon="mdiAlertCircle">
      {{ error }}
    </NotificationBar>

    <SectionMain>
      <!-- Exness Error Notification -->
      <NotificationBar v-if="exnessError" color="danger" :icon="mdiAlertBoxOutline">
        <strong>ข้อผิดพลาด Exness:</strong> {{ exnessError }}
        <br>
        <small>กรุณาตรวจสอบว่าคุณมีบัญชี Exness และใช้ email/password เดียวกันกับบัญชี Exness ของคุณ</small>
      </NotificationBar>
      
      <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2 xl:grid-cols-4">
        <CardBoxWidget
          trend="12%"
          trend-type="up"
          color="text-emerald-500"
          :icon="mdiAccountMultiple"
          :number="512"
          label="Clients"
        />
        <CardBoxWidget
          trend="12%"
          trend-type="down"
          color="text-blue-500"
          :icon="mdiCashMultiple"
          :number="777"
          suffix="$"
          label="Sales"
        />
        <CardBoxWidget
          trend="Overflow"
          trend-type="danger"
          color="text-red-500"
          :icon="mdiCartOutline"
          :number="256"
          suffix="%"
          label="Performance"
        />
        <CardBoxWidget
          trend="12%"
          trend-type="warning"
          color="text-yellow-500"
          :icon="mdiChartTimelineVariant"
          :number="899"
          label="Orders"
        />
      </div>

      <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
        <CardBoxTransaction />
        <CardBoxClient />
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
