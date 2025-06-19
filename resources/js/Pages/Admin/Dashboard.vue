<script setup>
import { Head } from "@inertiajs/vue3";
import { computed, ref, onMounted, nextTick } from "vue";
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
import CardBoxModal from '@/Components/CardBoxModal.vue';
import qrKen from '@/assets/qrcode/qrken.png';
import qrHam from '@/assets/qrcode/qrham.png';

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
const copyTooltip = ref(false);

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

// Partner tab state
const partnerTab = ref('link'); // 'link' or 'code'
const partnerLink = 'https://one.exnesstrack.org/a/sk1tysmrr8';
const partnerCode = 'sk1tysmrr8';

const partnerDisplay = computed(() => partnerTab.value === 'link' ? partnerLink : partnerCode);
const partnerLabel = computed(() => partnerTab.value === 'link' ? 'Partner Link' : 'Partner Code');

const isQrModalOpen = ref(false);
function openQrModal() { isQrModalOpen.value = true; }
function closeQrModal() { isQrModalOpen.value = false; }

async function copyPartnerInfo() {
  try {
    const textToCopy = partnerTab.value === 'link' ? partnerLink + '\n' + partnerCode : partnerCode;
    await navigator.clipboard.writeText(textToCopy);
    copyTooltip.value = true;
    await nextTick();
    setTimeout(() => { copyTooltip.value = false; }, 1200);
  } catch (e) {
    alert('ไม่สามารถคัดลอกได้: ' + e);
  }
}

function downloadQr() {
  const link = document.createElement('a');
  link.href = qrKen;
  link.download = 'qr-ken.png';
  link.click();
}

// CardBox 2 state
const partnerTab2 = ref('link');
const partnerLink2 = 'https://one.exnesstrack.org/a/peoodzo4g4';
const partnerCode2 = 'peoodzo4g4';
const isQrModalOpen2 = ref(false);
const copyTooltip2 = ref(false);
async function copyPartnerInfo2() {
  try {
    const textToCopy = partnerTab2.value === 'link' ? partnerLink2 + '\n' + partnerCode2 : partnerCode2;
    await navigator.clipboard.writeText(textToCopy);
    copyTooltip2.value = true;
    await nextTick();
    setTimeout(() => { copyTooltip2.value = false; }, 1200);
  } catch (e) {
    alert('ไม่สามารถคัดลอกได้: ' + e);
  }
}
function openQrModal2() { isQrModalOpen2.value = true; }
function closeQrModal2() { isQrModalOpen2.value = false; }

// CardBox 3 state
const partnerTab3 = ref('link');
const partnerLink3 = 'https://one.exnesstrack.org/a/b5n86ddy8q';
const partnerCode3 = 'b5n86ddy8q';
const copyTooltip3 = ref(false);
async function copyPartnerInfo3() {
  try {
    const textToCopy = partnerTab3.value === 'link' ? partnerLink3 + '\n' + partnerCode3 : partnerCode3;
    await navigator.clipboard.writeText(textToCopy);
    copyTooltip3.value = true;
    await nextTick();
    setTimeout(() => { copyTooltip3.value = false; }, 1200);
  } catch (e) {
    alert('ไม่สามารถคัดลอกได้: ' + e);
  }
}
</script>

<template>
  <Head :title="title" />

  <LayoutAuthenticated>
    <!-- <NotificationBar v-if="error" color="danger" :icon="mdiAlertCircle">
      {{ error }}
    </NotificationBar> -->

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
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        <!-- ซ้าย -->
        <div class="flex flex-col gap-6">
          <!-- CardBox 1: Partner Link Card (Styled to match the image, with tab switch) -->
          <CardBox class="bg-white rounded-xl shadow p-4 border border-gray-100 flex flex-col gap-2">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 14.828a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656 5.656m-6.364 6.364a9 9 0 1112.728-12.728 9 9 0 01-12.728 12.728z" />
                </svg>
                <span class="font-medium text-gray-700">Your Partner Link</span>
              </div>
              <div class="flex items-center gap-2">
                <button @click="partnerTab = 'link'"
                  :class="[
                    'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                    partnerTab === 'link'
                      ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200'
                  ]"
                >Partner link</button>
                <button @click="partnerTab = 'code'"
                  :class="[
                    'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                    partnerTab === 'code'
                      ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200'
                  ]"
                >Partner code</button>
              </div>
            </div>
            <div class="mb-2">
              <span v-if="partnerTab === 'link'">
                <a :href="partnerLink" target="_blank" class="block text-blue-600 font-medium text-lg break-all hover:underline text-center">
                  {{ partnerLink }}
                </a>
              </span>
              <span v-else class="block text-blue-600 font-medium text-lg break-all text-center select-all cursor-pointer hover:bg-blue-50 p-2 rounded" @click="copyPartnerInfo">
                {{ partnerCode }}
              </span>
            </div>
            <div class="flex items-center justify-center gap-4 mb-1 mt-4">
              <div class="relative flex items-center">
                <button @click="copyPartnerInfo" class="flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-gray-200 shadow text-gray-700 font-medium text-base hover:bg-gray-50 active:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-blue-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2" />
                  </svg>
                  <span>Copy</span>
                </button>
                <transition name="fade">
                  <div v-if="copyTooltip" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 shadow z-50 whitespace-nowrap">
                    Link copied successfully
                  </div>
                </transition>
              </div>
              <button @click="openQrModal" class="flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 border border-blue-200 shadow text-blue-700 font-medium text-base hover:bg-blue-100 active:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-blue-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2" fill="none" />
                  <path d="M7 7h2v2H7zm4 0h2v2h-2V7zm4 0h2v2h-2V7zM7 11h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zM7 15h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z" />
                </svg>
                <span>QR code</span>
              </button>
            </div>
           
            <!-- QR Code Modal -->
            <CardBoxModal v-model="isQrModalOpen" largeTitle="QR code" :button="null" :button-label="''" :has-cancel="false">
              <div class="flex flex-col items-center justify-between h-full w-full pt-2 pb-4">
                <div class="flex-1 flex flex-col items-center justify-start w-full">
                  <img :src="qrKen" alt="QR code" class="w-[400px] h-[400px] max-w-full max-h-[400px] object-contain mt-2 mb-2" />
                  <a :href="partnerDisplay" target="_blank" class="block text-blue-600 font-medium text-sm break-all hover:underline text-center mb-6">
                    {{ partnerDisplay }}
                  </a>
                </div>
                <div class="flex gap-4 w-full justify-center mt-auto">
                  <div class="relative flex items-center w-28 justify-center">
                    <button @click="copyPartnerInfo" class="w-28 px-0 py-2 rounded-md bg-white border border-gray-200 text-gray-700 font-medium shadow-sm hover:bg-gray-100 focus:outline-none transition text-center">Copy</button>
                    <transition name="fade">
                      <div v-if="copyTooltip" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 shadow z-50 whitespace-nowrap">
                        Link copied successfully
                      </div>
                    </transition>
                  </div>
                  <button @click="downloadQr" class="w-28 px-0 py-2 rounded-md bg-yellow-400 text-gray-900 font-medium shadow-sm hover:bg-yellow-500 focus:outline-none transition text-center">Download</button>
                </div>
              </div>
            </CardBoxModal>
          </CardBox>
          <!-- CardBox 2: Partner Link Card (ข้อมูลใหม่) -->
          <CardBox class="bg-white rounded-xl shadow p-4 border border-gray-100 flex flex-col gap-2">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 14.828a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656 5.656m-6.364 6.364a9 9 0 1112.728-12.728 9 9 0 01-12.728 12.728z" />
                </svg>
                <span class="font-medium text-gray-700">Your Partner Link</span>
              </div>
              <div class="flex items-center gap-2">
                <button @click="partnerTab2 = 'link'"
                  :class="[
                    'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                    partnerTab2 === 'link'
                      ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200'
                  ]"
                >Partner link</button>
                <button @click="partnerTab2 = 'code'"
                  :class="[
                    'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                    partnerTab2 === 'code'
                      ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200'
                  ]"
                >Partner code</button>
              </div>
            </div>
            <div class="mb-2">
              <span v-if="partnerTab2 === 'link'">
                <a :href="partnerLink2" target="_blank" class="block text-blue-600 font-medium text-lg break-all hover:underline text-center">
                  {{ partnerLink2 }}
                </a>
              </span>
              <span v-else class="block text-blue-600 font-medium text-lg break-all text-center select-all cursor-pointer hover:bg-blue-50 p-2 rounded" @click="copyPartnerInfo2">
                {{ partnerCode2 }}
              </span>
            </div>
            <div class="flex items-center justify-center gap-4 mb-1 mt-4">
              <div class="relative flex items-center">
                <button @click="copyPartnerInfo2" class="flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-gray-200 shadow text-gray-700 font-medium text-base hover:bg-gray-50 active:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-blue-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2" />
                  </svg>
                  <span>Copy</span>
                </button>
                <transition name="fade">
                  <div v-if="copyTooltip2" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 shadow z-50 whitespace-nowrap">
                    Link copied successfully
                  </div>
                </transition>
              </div>
              <button @click="openQrModal2" class="flex items-center gap-2 px-4 py-2 rounded-full bg-blue-50 border border-blue-200 shadow text-blue-700 font-medium text-base hover:bg-blue-100 active:bg-blue-200 transition focus:outline-none focus:ring-2 focus:ring-blue-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <rect x="4" y="4" width="16" height="16" rx="2" stroke="currentColor" stroke-width="2" fill="none" />
                  <path d="M7 7h2v2H7zm4 0h2v2h-2V7zm4 0h2v2h-2V7zM7 11h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zM7 15h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z" />
                </svg>
                <span>QR code</span>
              </button>
            </div>
            <CardBoxModal v-model="isQrModalOpen2" largeTitle="QR code" :button="null" :button-label="''" :has-cancel="false">
              <div class="flex flex-col items-center justify-between h-full w-full pt-2 pb-4">
                <div class="flex-1 flex flex-col items-center justify-start w-full">
                  <img :src="qrHam" alt="QR code" class="w-[400px] h-[400px] max-w-full max-h-[400px] object-contain mt-2 mb-2" />
                  <a :href="partnerTab2 === 'link' ? partnerLink2 : partnerCode2" target="_blank" class="block text-blue-600 font-medium text-sm break-all hover:underline text-center mb-6">
                    {{ partnerTab2 === 'link' ? partnerLink2 : partnerCode2 }}
                  </a>
                </div>
                <div class="flex gap-4 w-full justify-center mt-auto">
                  <div class="relative flex items-center w-28 justify-center">
                    <button @click="copyPartnerInfo2" class="w-28 px-0 py-2 rounded-md bg-white border border-gray-200 text-gray-700 font-medium shadow-sm hover:bg-gray-100 focus:outline-none transition text-center">Copy</button>
                    <transition name="fade">
                      <div v-if="copyTooltip2" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 shadow z-50 whitespace-nowrap">
                        Link copied successfully
                      </div>
                    </transition>
                  </div>
                  <button @click="downloadQr" class="w-28 px-0 py-2 rounded-md bg-yellow-400 text-gray-900 font-medium shadow-sm hover:bg-yellow-500 focus:outline-none transition text-center">Download</button>
                </div>
              </div>
            </CardBoxModal>
          </CardBox>
          <!-- CardBox 3: Partner Link Card -->
          <CardBox class="bg-white rounded-xl shadow p-4 border border-gray-100 flex flex-col gap-2">
            <div class="flex items-center justify-between mb-2">
              <div class="flex items-center">
                <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 14.828a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656 5.656m-6.364 6.364a9 9 0 1112.728-12.728 9 9 0 01-12.728 12.728z" />
                </svg>
                <span class="font-medium text-gray-700">Your Partner Link</span>
              </div>
              <div class="flex items-center gap-2">
                <button @click="partnerTab3 = 'link'"
                  :class="[
                    'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                    partnerTab3 === 'link'
                      ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200'
                  ]"
                >Partner link</button>
                <button @click="partnerTab3 = 'code'"
                  :class="[
                    'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                    partnerTab3 === 'code'
                      ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                      : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200'
                  ]"
                >Partner code</button>
              </div>
            </div>
            
            <div>
              <span v-if="partnerTab3 === 'link'">
                <a :href="partnerLink3" target="_blank" class="block text-blue-600 font-medium text-lg break-all hover:underline text-center">
                  {{ partnerLink3 }}
                </a>
              </span>
              <span v-else class="block text-blue-600 font-medium text-lg break-all text-center select-all cursor-pointer hover:bg-blue-50 p-2 rounded" @click="copyPartnerInfo3">
                {{ partnerCode3 }}
              </span>
            </div>
            <div class="flex items-center justify-center gap-4 mb-1 mt-4">
              <div class="relative flex items-center">
                <button @click="copyPartnerInfo3" class="flex items-center gap-2 px-4 py-2 rounded-full bg-white border border-gray-200 shadow text-gray-700 font-medium text-base hover:bg-gray-50 active:bg-gray-100 transition focus:outline-none focus:ring-2 focus:ring-blue-200">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2" />
                  </svg>
                  <span>Copy</span>
                </button>
                <transition name="fade">
                  <div v-if="copyTooltip3" class="absolute -top-8 left-1/2 -translate-x-1/2 bg-gray-900 text-white text-xs rounded px-2 py-1 shadow z-50 whitespace-nowrap">
                    Link copied successfully
                  </div>
                </transition>
              </div>
            </div>
          </CardBox>
        </div>
        <!-- ขวา -->
        <div class="flex flex-col gap-6">
          <!-- CardBox 4 -->
          <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl min-h-[200px] flex flex-col justify-center">
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
          <!-- CardBox 5 -->
          <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl min-h-[200px] flex flex-col justify-center">
            <div class="absolute inset-0 bg-gradient-to-br from-pink-500 to-pink-600 opacity-10"></div>
            <div class="relative flex items-center justify-between">
              <div class="flex flex-col">
                <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Example 5</span>
                <span class="text-3xl font-bold text-pink-600 dark:text-pink-400">123</span>
                <div class="flex items-center mt-2">
                  <span class="text-sm text-pink-600 font-medium">+0%</span>
                </div>
              </div>
              <div class="flex-shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                  </svg>
                </div>
              </div>
            </div>
          </CardBox>
          <!-- CardBox 6 -->
          <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl min-h-[200px] flex flex-col justify-center">
            <div class="absolute inset-0 bg-gradient-to-br from-gray-500 to-gray-600 opacity-10"></div>
            <div class="relative flex items-center justify-between">
              <div class="flex flex-col">
                <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Example 6</span>
                <span class="text-3xl font-bold text-gray-600 dark:text-gray-400">456</span>
                <div class="flex items-center mt-2">
                  <span class="text-sm text-gray-600 font-medium">+0%</span>
                </div>
              </div>
              <div class="flex-shrink-0">
                <div class="w-16 h-16 bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-transform duration-200">
                  <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <rect x="4" y="4" width="16" height="16" rx="4" stroke="currentColor" stroke-width="2" fill="none" />
                  </svg>
                </div>
              </div>
            </div>
          </CardBox>
        </div>
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

      
    </SectionMain>
  </LayoutAuthenticated>
</template>

<style>
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>
