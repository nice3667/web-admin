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
  mdiTrendingUp,
  mdiTrendingDown,
  mdiCurrencyUsd,
  mdiAccountGroup,
  mdiChartBar,
  mdiGift,
  mdiCash,
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

// Dashboard statistics
const stats = ref({
  totalClients: 122,
  totalAccounts: 200,
  totalRevenue: 15420.50,
  activeUsers: 89
});
</script>

<template>
  <Head :title="title" />

  <LayoutAuthenticated>
    <NotificationBar v-if="error" color="danger" :icon="mdiAlertCircle">
      {{ error }}
    </NotificationBar>

    <SectionMain>
      <!-- Welcome Section -->
      <div class="mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl p-8 text-white shadow-2xl">
          <div class="flex items-center justify-between">
            <div>
              <h1 class="text-3xl font-bold mb-2">ยินดีต้อนรับสู่ Admin Dashboard</h1>
              <p class="text-blue-100 text-lg">จัดการระบบและติดตามข้อมูลลูกค้า Exness</p>
            </div>
            <div class="hidden md:block">
              <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Exness Error Notification -->
      <NotificationBar v-if="exnessError" color="danger" :icon="mdiAlertBoxOutline" class="mb-6">
        <strong>ข้อผิดพลาด Exness:</strong> {{ exnessError }}
        <br>
        <small>กรุณาตรวจสอบว่าคุณมีบัญชี Exness และใช้ email/password เดียวกันกับบัญชี Exness ของคุณ</small>
      </NotificationBar>
      
      <!-- Enhanced Statistics Cards -->
      <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
        <!-- Total Clients Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Clients</span>
              <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.totalClients }}</span>
              <div class="flex items-center mt-2">
                <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-sm text-green-600 font-medium">+12%</span>
              </div>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Total Accounts Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-green-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Accounts</span>
              <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.totalAccounts }}</span>
              <div class="flex items-center mt-2">
                <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-sm text-green-600 font-medium">+8%</span>
              </div>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Total Revenue Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Total Revenue</span>
              <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">${{ stats.totalRevenue.toLocaleString() }}</span>
              <div class="flex items-center mt-2">
                <svg class="w-4 h-4 text-green-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
                <span class="text-sm text-green-600 font-medium">+15%</span>
              </div>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>

        <!-- Active Users Card -->
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Active Users</span>
              <span class="text-3xl font-bold text-orange-600 dark:text-orange-400">{{ stats.activeUsers }}</span>
              <div class="flex items-center mt-2">
                <svg class="w-4 h-4 text-red-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                </svg>
                <span class="text-sm text-red-600 font-medium">-3%</span>
              </div>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
      </div>

      <!-- Quick Actions -->
      <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-4">Quick Actions</h2>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer border border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">View Reports</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Client & Transaction reports</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer border border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Add Client</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Register new client</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer border border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Sync Data</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Update from Exness API</p>
              </div>
            </div>
          </div>

          <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 cursor-pointer border border-gray-100 dark:border-gray-700">
            <div class="flex items-center space-x-4">
              <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                </svg>
              </div>
              <div>
                <h3 class="font-semibold text-gray-900 dark:text-gray-100">Manage Promo</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">Promotions & rewards</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts and Data Section -->
      <div class="grid grid-cols-1 gap-6 mb-6 lg:grid-cols-2">
        <CardBoxTransaction />
        <CardBoxClient />
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template>
