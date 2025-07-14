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
import axios from "axios";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import qrKen from "@/assets/qrcode/qrken.png";
import qrHam from "@/assets/qrcode/qrham.png";

const props = defineProps({
  title: {
    type: String,
    default: "Dashboard",
  },
});

const chartData = ref(null);
const totalAmount = ref(0);
const loading = ref(true);
const exnessError = ref("");
const walletAccounts = ref([]);
const error = ref(null);
const copyTooltip = ref(false);

const fillChartData = () => {
  chartData.value = chartConfig.sampleChartData();
};

const fetchWalletAccounts = async () => {
  try {
    const response = await axios.get("/api/wallet/accounts");
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
  totalRevenue: 15420.5,
  activeUsers: 89,
});

// Partner tab state
const partnerTab = ref("link"); // 'link' or 'code'
const partnerLink = "https://one.exnesstrack.org/a/sk1tysmrr8";
const partnerCode = "sk1tysmrr8";

const partnerDisplay = computed(() =>
  partnerTab.value === "link" ? partnerLink : partnerCode
);
const partnerLabel = computed(() =>
  partnerTab.value === "link" ? "Partner Link" : "Partner Code"
);

const isQrModalOpen = ref(false);
function openQrModal() {
  isQrModalOpen.value = true;
}
function closeQrModal() {
  isQrModalOpen.value = false;
}

async function copyPartnerInfo() {
  try {
    const textToCopy =
      partnerTab.value === "link"
        ? partnerLink + "\n" + partnerCode
        : partnerCode;
    await navigator.clipboard.writeText(textToCopy);
    copyTooltip.value = true;
    await nextTick();
    setTimeout(() => {
      copyTooltip.value = false;
    }, 1200);
  } catch (e) {
    alert("ไม่สามารถคัดลอกได้: " + e);
  }
}

function downloadQr() {
  const link = document.createElement("a");
  link.href = qrKen;
  link.download = "qr-ken.png";
  link.click();
}

// CardBox 2 state
const partnerTab2 = ref("link");
const partnerLink2 = "https://one.exnesstrack.org/a/peoodzo4g4";
const partnerCode2 = "peoodzo4g4";
const isQrModalOpen2 = ref(false);
const copyTooltip2 = ref(false);
async function copyPartnerInfo2() {
  try {
    const textToCopy =
      partnerTab2.value === "link"
        ? partnerLink2 + "\n" + partnerCode2
        : partnerCode2;
    await navigator.clipboard.writeText(textToCopy);
    copyTooltip2.value = true;
    await nextTick();
    setTimeout(() => {
      copyTooltip2.value = false;
    }, 1200);
  } catch (e) {
    alert("ไม่สามารถคัดลอกได้: " + e);
  }
}
function openQrModal2() {
  isQrModalOpen2.value = true;
}
function closeQrModal2() {
  isQrModalOpen2.value = false;
}

// CardBox 3 state
const partnerTab3 = ref("link");
const partnerLink3 = "https://one.exnesstrack.org/a/b5n86ddy8q";
const partnerCode3 = "b5n86ddy8q";
const copyTooltip3 = ref(false);
async function copyPartnerInfo3() {
  try {
    const textToCopy =
      partnerTab3.value === "link"
        ? partnerLink3 + "\n" + partnerCode3
        : partnerCode3;
    await navigator.clipboard.writeText(textToCopy);
    copyTooltip3.value = true;
    await nextTick();
    setTimeout(() => {
      copyTooltip3.value = false;
    }, 1200);
  } catch (e) {
    alert("ไม่สามารถคัดลอกได้: " + e);
  }
}
</script>

<template>
  <Head :title="title" />
  <LayoutAuthenticated>
    <div class="min-h-screen relative overflow-hidden">
      <!-- Professional Background with Geometric Patterns -->
      <div class="absolute inset-0 bg-gradient-to-br from-slate-950 via-blue-950 to-slate-900"></div>
      <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,_var(--tw-gradient-stops))] from-blue-900/30 via-transparent to-transparent"></div>
      <!-- Subtle Geometric Grid -->
      <div class="absolute inset-0 opacity-10">
        <div class="w-full h-full animate-grid-subtle"
             style="background-image: linear-gradient(rgba(59, 130, 246, 0.3) 1px, transparent 1px), linear-gradient(90deg, rgba(59, 130, 246, 0.3) 1px, transparent 1px); background-size: 60px 60px;"></div>
      </div>
      <!-- Professional Floating Elements (Hidden on mobile) -->
      <div class="absolute inset-0 opacity-20 pointer-events-none hidden md:block">
        <div class="absolute top-1/4 left-1/4 w-60 h-60 lg:w-80 lg:h-80 bg-gradient-to-r from-blue-600 to-blue-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-professional"></div>
        <div class="absolute top-1/3 right-1/4 w-40 h-40 lg:w-64 lg:h-64 bg-gradient-to-r from-slate-600 to-slate-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-professional animation-delay-3000"></div>
        <div class="absolute bottom-1/4 left-1/3 w-48 h-48 lg:w-72 lg:h-72 bg-gradient-to-r from-blue-500 to-slate-500 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-professional animation-delay-6000"></div>
      </div>
      <!-- Animated Dots (Reduced on mobile) -->
      <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div v-for="i in 6" :key="i"
             class="absolute animate-float-subtle opacity-20 hidden sm:block"
             :style="{
               left: Math.random() * 100 + '%',
               top: Math.random() * 100 + '%',
               animationDelay: Math.random() * 15 + 's',
               animationDuration: (Math.random() * 8 + 12) + 's'
             }">
          <div class="w-1 h-1 bg-blue-400 rounded-full"></div>
        </div>
      </div>
      <!-- Main Content -->
      <div class="relative z-10 min-h-screen flex flex-col items-center justify-start p-2 sm:p-3 md:p-4 bg-slate-900">
        <SectionMain class="w-full max-w-full">
          <!-- Welcome Section -->
          <div class="mb-3 sm:mb-5">
            <div class="p-3 sm:p-6 bg-white/5 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden">
              <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-blue-600 to-blue-400"></div>
              <div class="flex flex-col md:flex-row items-center justify-between space-y-3 md:space-y-0">
                <div class="text-center md:text-left">
                  <h1 class="mb-1 text-lg sm:text-xl lg:text-2xl font-bold text-white drop-shadow-lg">ทดสอบ update git web</h1>
                  <h1 class="mb-1 text-lg sm:text-xl lg:text-2xl font-bold text-white drop-shadow-lg">
                    ยินดีต้อนรับสู่ Admin Dashboard
                  </h1>
                  <p class="text-xs sm:text-sm md:text-base text-blue-200 font-semibold">
                    จัดการระบบและติดตามข้อมูลลูกค้า Exness
                  </p>
                </div>
                <div class="flex-shrink-0 mt-2 md:mt-0">
                  <div class="flex items-center justify-center w-12 h-12 sm:w-16 sm:h-16 md:w-20 md:h-20 rounded-full bg-white/10 backdrop-blur-xl border border-white/10 shadow-xl">
                    <svg class="w-6 h-6 sm:w-8 sm:h-8 md:w-10 md:h-10 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <!-- Exness Error Notification -->
          <NotificationBar v-if="exnessError" color="danger" :icon="mdiAlertBoxOutline" class="mb-4 sm:mb-6" />
          <!-- Enhanced Statistics Cards -->
          <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-2 gap-3 sm:gap-4 mb-3 sm:mb-6">
            <div class="flex flex-col gap-4 sm:gap-6">
              <CardBox class="bg-slate-900/80 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center">
                    <svg
                      class="w-5 h-5 mr-2 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13.828 14.828a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656 5.656m-6.364 6.364a9 9 0 1112.728-12.728 9 9 0 01-12.728 12.728z"
                      />
                    </svg>
                    <span class="font-medium text-gray-700">Your Partner Link</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      @click="partnerTab = 'link'"
                      :class="[
                        'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                        partnerTab === 'link'
                          ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                          : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200',
                      ]"
                    >
                      Partner link
                    </button>
                    <button
                      @click="partnerTab = 'code'"
                      :class="[
                        'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                        partnerTab === 'code'
                          ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                          : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200',
                      ]"
                    >
                      Partner code
                    </button>
                  </div>
                </div>
                <div class="mb-2">
                  <span v-if="partnerTab === 'link'">
                    <a
                      :href="partnerLink"
                      target="_blank"
                      class="block text-lg font-medium text-center text-blue-600 break-all hover:underline"
                    >
                      {{ partnerLink }}
                    </a>
                  </span>
                  <span
                    v-else
                    class="block p-2 text-lg font-medium text-center text-blue-600 break-all rounded cursor-pointer select-all hover:bg-blue-50"
                    @click="copyPartnerInfo"
                  >
                    {{ partnerCode }}
                  </span>
                </div>
                <div class="flex items-center justify-center gap-4 mt-4 mb-1">
                  <div class="relative flex items-center">
                    <button
                      @click="copyPartnerInfo"
                      class="flex items-center gap-2 px-4 py-2 text-base font-medium text-gray-700 transition bg-white border border-gray-200 rounded-full shadow hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2"
                        />
                      </svg>
                      <span>Copy</span>
                    </button>
                    <transition name="fade">
                      <div
                        v-if="copyTooltip"
                        class="absolute z-50 px-2 py-1 text-xs text-white -translate-x-1/2 bg-gray-900 rounded shadow -top-8 left-1/2 whitespace-nowrap"
                      >
                        Link copied successfully
                      </div>
                    </transition>
                  </div>
                  <button
                    @click="openQrModal"
                    class="flex items-center gap-2 px-4 py-2 text-base font-medium text-blue-700 transition border border-blue-200 rounded-full shadow bg-blue-50 hover:bg-blue-100 active:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <rect
                        x="4"
                        y="4"
                        width="16"
                        height="16"
                        rx="2"
                        stroke="currentColor"
                        stroke-width="2"
                        fill="none"
                      />
                      <path
                        d="M7 7h2v2H7zm4 0h2v2h-2V7zm4 0h2v2h-2V7zM7 11h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zM7 15h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z"
                      />
                    </svg>
                    <span>QR code</span>
                  </button>
                </div>
                <CardBoxModal
                  v-model="isQrModalOpen"
                  largeTitle="QR code"
                  :button="null"
                  :button-label="''"
                  :has-cancel="false"
                >
                  <div
                    class="flex flex-col items-center justify-between w-full h-full pt-2 pb-4"
                  >
                    <div
                      class="flex flex-col items-center justify-start flex-1 w-full"
                    >
                      <img
                        :src="qrKen"
                        alt="QR code"
                        class="w-[400px] h-[400px] max-w-full max-h-[400px] object-contain mt-2 mb-2"
                      />
                      <a
                        :href="partnerDisplay"
                        target="_blank"
                        class="block mb-6 text-sm font-medium text-center text-blue-600 break-all hover:underline"
                      >
                        {{ partnerDisplay }}
                      </a>
                    </div>
                    <div class="flex justify-center w-full gap-4 mt-auto">
                      <div class="relative flex items-center justify-center w-28">
                        <button
                          @click="copyPartnerInfo"
                          class="px-0 py-2 font-medium text-center text-gray-700 transition bg-white border border-gray-200 rounded-md shadow-sm w-28 hover:bg-gray-100 focus:outline-none"
                        >
                          Copy
                        </button>
                        <transition name="fade">
                          <div
                            v-if="copyTooltip"
                            class="absolute z-50 px-2 py-1 text-xs text-white -translate-x-1/2 bg-gray-900 rounded shadow -top-8 left-1/2 whitespace-nowrap"
                          >
                            Link copied successfully
                          </div>
                        </transition>
                      </div>
                      <button
                        @click="downloadQr"
                        class="px-0 py-2 font-medium text-center text-gray-900 transition bg-yellow-400 rounded-md shadow-sm w-28 hover:bg-yellow-500 focus:outline-none"
                      >
                        Download
                      </button>
                    </div>
                  </div>
                </CardBoxModal>
              </CardBox>
              <CardBox class="bg-slate-900/80 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center">
                    <svg
                      class="w-5 h-5 mr-2 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13.828 14.828a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656 5.656m-6.364 6.364a9 9 0 1112.728-12.728 9 9 0 01-12.728 12.728z"
                      />
                    </svg>
                    <span class="font-medium text-gray-700">Your Partner Link</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      @click="partnerTab2 = 'link'"
                      :class="[
                        'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                        partnerTab2 === 'link'
                          ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                          : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200',
                      ]"
                    >
                      Partner link
                    </button>
                    <button
                      @click="partnerTab2 = 'code'"
                      :class="[
                        'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                        partnerTab2 === 'code'
                          ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                          : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200',
                      ]"
                    >
                      Partner code
                    </button>
                  </div>
                </div>
                <div class="mb-2">
                  <span v-if="partnerTab2 === 'link'">
                    <a
                      :href="partnerLink2"
                      target="_blank"
                      class="block text-lg font-medium text-center text-blue-600 break-all hover:underline"
                    >
                      {{ partnerLink2 }}
                    </a>
                  </span>
                  <span
                    v-else
                    class="block p-2 text-lg font-medium text-center text-blue-600 break-all rounded cursor-pointer select-all hover:bg-blue-50"
                    @click="copyPartnerInfo2"
                  >
                    {{ partnerCode2 }}
                  </span>
                </div>
                <div class="flex items-center justify-center gap-4 mt-4 mb-1">
                  <div class="relative flex items-center">
                    <button
                      @click="copyPartnerInfo2"
                      class="flex items-center gap-2 px-4 py-2 text-base font-medium text-gray-700 transition bg-white border border-gray-200 rounded-full shadow hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2"
                        />
                      </svg>
                      <span>Copy</span>
                    </button>
                    <transition name="fade">
                      <div
                        v-if="copyTooltip2"
                        class="absolute z-50 px-2 py-1 text-xs text-white -translate-x-1/2 bg-gray-900 rounded shadow -top-8 left-1/2 whitespace-nowrap"
                      >
                        Link copied successfully
                      </div>
                    </transition>
                  </div>
                  <button
                    @click="openQrModal2"
                    class="flex items-center gap-2 px-4 py-2 text-base font-medium text-blue-700 transition border border-blue-200 rounded-full shadow bg-blue-50 hover:bg-blue-100 active:bg-blue-200 focus:outline-none focus:ring-2 focus:ring-blue-200"
                  >
                    <svg
                      class="w-5 h-5"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <rect
                        x="4"
                        y="4"
                        width="16"
                        height="16"
                        rx="2"
                        stroke="currentColor"
                        stroke-width="2"
                        fill="none"
                      />
                      <path
                        d="M7 7h2v2H7zm4 0h2v2h-2V7zm4 0h2v2h-2V7zM7 11h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2zM7 15h2v2H7v-2zm4 0h2v2h-2v-2zm4 0h2v2h-2v-2z"
                      />
                    </svg>
                    <span>QR code</span>
                  </button>
                </div>
                <CardBoxModal
                  v-model="isQrModalOpen2"
                  largeTitle="QR code"
                  :button="null"
                  :button-label="''"
                  :has-cancel="false"
                >
                  <div
                    class="flex flex-col items-center justify-between w-full h-full pt-2 pb-4"
                  >
                    <div
                      class="flex flex-col items-center justify-start flex-1 w-full"
                    >
                      <img
                        :src="qrHam"
                        alt="QR code"
                        class="w-[400px] h-[400px] max-w-full max-h-[400px] object-contain mt-2 mb-2"
                      />
                      <a
                        :href="partnerTab2 === 'link' ? partnerLink2 : partnerCode2"
                        target="_blank"
                        class="block mb-6 text-sm font-medium text-center text-blue-600 break-all hover:underline"
                      >
                        {{ partnerTab2 === "link" ? partnerLink2 : partnerCode2 }}
                      </a>
                    </div>
                    <div class="flex justify-center w-full gap-4 mt-auto">
                      <div class="relative flex items-center justify-center w-28">
                        <button
                          @click="copyPartnerInfo2"
                          class="px-0 py-2 font-medium text-center text-gray-700 transition bg-white border border-gray-200 rounded-md shadow-sm w-28 hover:bg-gray-100 focus:outline-none"
                        >
                          Copy
                        </button>
                        <transition name="fade">
                          <div
                            v-if="copyTooltip2"
                            class="absolute z-50 px-2 py-1 text-xs text-white -translate-x-1/2 bg-gray-900 rounded shadow -top-8 left-1/2 whitespace-nowrap"
                          >
                            Link copied successfully
                          </div>
                        </transition>
                      </div>
                      <button
                        @click="downloadQr"
                        class="px-0 py-2 font-medium text-center text-gray-900 transition bg-yellow-400 rounded-md shadow-sm w-28 hover:bg-yellow-500 focus:outline-none"
                      >
                        Download
                      </button>
                    </div>
                  </div>
                </CardBoxModal>
              </CardBox>
              <CardBox class="bg-slate-900/80 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center">
                    <svg
                      class="w-5 h-5 mr-2 text-gray-400"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M13.828 14.828a4 4 0 01-5.656-5.656m1.414-1.414a4 4 0 015.656 5.656m-6.364 6.364a9 9 0 1112.728-12.728 9 9 0 01-12.728 12.728z"
                      />
                    </svg>
                    <span class="font-medium text-gray-700">Your Partner Link</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <button
                      @click="partnerTab3 = 'link'"
                      :class="[
                        'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                        partnerTab3 === 'link'
                          ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                          : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200',
                      ]"
                    >
                      Partner link
                    </button>
                    <button
                      @click="partnerTab3 = 'code'"
                      :class="[
                        'px-4 py-2 rounded-full font-medium text-base border shadow-sm focus:outline-none transition',
                        partnerTab3 === 'code'
                          ? 'bg-violet-100 text-violet-700 border-violet-200 hover:bg-violet-200 active:bg-violet-300'
                          : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-100 active:bg-gray-200',
                      ]"
                    >
                      Partner code
                    </button>
                  </div>
                </div>

                <div>
                  <span v-if="partnerTab3 === 'link'">
                    <a
                      :href="partnerLink3"
                      target="_blank"
                      class="block text-lg font-medium text-center text-blue-600 break-all hover:underline"
                    >
                      {{ partnerLink3 }}
                    </a>
                  </span>
                  <span
                    v-else
                    class="block p-2 text-lg font-medium text-center text-blue-600 break-all rounded cursor-pointer select-all hover:bg-blue-50"
                    @click="copyPartnerInfo3"
                  >
                    {{ partnerCode3 }}
                  </span>
                </div>
                <div class="flex items-center justify-center gap-4 mt-4 mb-1">
                  <div class="relative flex items-center">
                    <button
                      @click="copyPartnerInfo3"
                      class="flex items-center gap-2 px-4 py-2 text-base font-medium text-gray-700 transition bg-white border border-gray-200 rounded-full shadow hover:bg-gray-50 active:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    >
                      <svg
                        class="w-5 h-5"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M8 16h8M8 12h8m-6 8h6a2 2 0 002-2V6a2 2 0 00-2-2H8a2 2 0 00-2 2v2"
                        />
                      </svg>
                      <span>Copy</span>
                    </button>
                    <transition name="fade">
                      <div
                        v-if="copyTooltip3"
                        class="absolute z-50 px-2 py-1 text-xs text-white -translate-x-1/2 bg-gray-900 rounded shadow -top-8 left-1/2 whitespace-nowrap"
                      >
                        Link copied successfully
                      </div>
                    </transition>
                  </div>
                </div>
              </CardBox>
            </div>
            <div class="flex flex-col gap-4 sm:gap-6">
              <CardBox class="bg-slate-900/80 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden min-h-[150px] sm:min-h-[200px] flex flex-col justify-center">
                <div
                  class="absolute inset-0 bg-gradient-to-br from-orange-500 to-orange-600 opacity-10"
                ></div>
                <div class="relative flex items-center justify-between">
                  <div class="flex flex-col">
                    <span
                      class="mb-1 text-sm font-medium text-gray-600"
                      >Active Users</span
                    >
                    <span
                      class="text-3xl font-bold text-orange-600"
                      >{{ stats.activeUsers }}</span
                    >
                    <div class="flex items-center mt-2">
                      <svg
                        class="w-4 h-4 mr-1 text-red-500"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"
                        ></path>
                      </svg>
                      <span class="text-sm font-medium text-red-600">-3%</span>
                    </div>
                  </div>
                  <div class="flex-shrink-0">
                    <div
                      class="flex items-center justify-center w-16 h-16 transition-transform duration-200 transform shadow-lg bg-gradient-to-br from-orange-500 to-orange-600 rounded-2xl hover:scale-110"
                    >
                      <svg
                        class="w-8 h-8 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <path
                          stroke-linecap="round"
                          stroke-linejoin="round"
                          stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                        ></path>
                      </svg>
                    </div>
                  </div>
                </div>
              </CardBox>
              <CardBox class="bg-slate-900/80 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden min-h-[150px] sm:min-h-[200px] flex flex-col justify-center">
                <div
                  class="absolute inset-0 bg-gradient-to-br from-pink-500 to-pink-600 opacity-10"
                ></div>
                <div class="relative flex items-center justify-between">
                  <div class="flex flex-col">
                    <span
                      class="mb-1 text-sm font-medium text-gray-600"
                      >Example 5</span
                    >
                    <span
                      class="text-3xl font-bold text-pink-600"
                      >123</span
                    >
                    <div class="flex items-center mt-2">
                      <span class="text-sm font-medium text-pink-600">+0%</span>
                    </div>
                  </div>
                  <div class="flex-shrink-0">
                    <div
                      class="flex items-center justify-center w-16 h-16 transition-transform duration-200 transform shadow-lg bg-gradient-to-br from-pink-500 to-pink-600 rounded-2xl hover:scale-110"
                    >
                      <svg
                        class="w-8 h-8 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="2"
                          fill="none"
                        />
                      </svg>
                    </div>
                  </div>
                </div>
              </CardBox>
              <CardBox class="bg-slate-900/80 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl relative overflow-hidden min-h-[150px] sm:min-h-[200px] flex flex-col justify-center">
                <div
                  class="absolute inset-0 bg-gradient-to-br from-gray-500 to-gray-600 opacity-10"
                ></div>
                <div class="relative flex items-center justify-between">
                  <div class="flex flex-col">
                    <span
                      class="mb-1 text-sm font-medium text-gray-600"
                      >Example 6</span
                    >
                    <span
                      class="text-3xl font-bold text-gray-600"
                      >456</span
                    >
                    <div class="flex items-center mt-2">
                      <span class="text-sm font-medium text-gray-600">+0%</span>
                    </div>
                  </div>
                  <div class="flex-shrink-0">
                    <div
                      class="flex items-center justify-center w-16 h-16 transition-transform duration-200 transform shadow-lg bg-gradient-to-br from-gray-500 to-gray-600 rounded-2xl hover:scale-110"
                    >
                      <svg
                        class="w-8 h-8 text-white"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                      >
                        <rect
                          x="4"
                          y="4"
                          width="16"
                          height="16"
                          rx="4"
                          stroke="currentColor"
                          stroke-width="2"
                          fill="none"
                        />
                      </svg>
                    </div>
                  </div>
                </div>
              </CardBox>
            </div>
          </div>
          <!-- Quick Actions -->
          <div class="mb-3 sm:mb-6">
            <h2 class="mb-2 sm:mb-3 text-base sm:text-lg font-semibold text-blue-200">Quick Actions</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-2 sm:gap-4">
              <div class="bg-white/5 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl p-3 sm:p-4 md:p-6 cursor-pointer hover:shadow-2xl hover:scale-105 transition-all">
                <div class="flex items-center space-x-3 sm:space-x-4">
                  <div class="flex items-center justify-center w-8 h-8 sm:w-10 sm:h-10 md:w-12 md:h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 md:w-6 md:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-white text-sm sm:text-base">View Reports</h3>
                    <p class="text-xs sm:text-sm text-blue-200">Client & Transaction reports</p>
                  </div>
                </div>
              </div>
              <div class="bg-white/5 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl p-3 sm:p-4 md:p-6 cursor-pointer hover:shadow-2xl hover:scale-105 transition-all">
                <div class="flex items-center space-x-3 sm:space-x-4">
                  <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-white text-sm sm:text-base">Add Client</h3>
                    <p class="text-xs sm:text-sm text-blue-200">Register new client</p>
                  </div>
                </div>
              </div>
              <div class="bg-white/5 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl p-3 sm:p-4 md:p-6 cursor-pointer hover:shadow-2xl hover:scale-105 transition-all">
                <div class="flex items-center space-x-3 sm:space-x-4">
                  <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-white text-sm sm:text-base">Sync Data</h3>
                    <p class="text-xs sm:text-sm text-blue-200">Update from Exness API</p>
                  </div>
                </div>
              </div>
              <div class="bg-white/5 backdrop-blur-xl border border-white/10 shadow-xl rounded-2xl p-3 sm:p-4 md:p-6 cursor-pointer hover:shadow-2xl hover:scale-105 transition-all">
                <div class="flex items-center space-x-3 sm:space-x-4">
                  <div class="flex items-center justify-center w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-white text-sm sm:text-base">Manage Promo</h3>
                    <p class="text-xs sm:text-sm text-blue-200">Promotions & rewards</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </SectionMain>
      </div>
    </div>
  </LayoutAuthenticated>
</template>

<style scoped>
@keyframes pulse-professional {
  0%, 100% {
    transform: scale(1);
    opacity: 0.2;
  }
  50% {
    transform: scale(1.05);
    opacity: 0.3;
  }
}
@keyframes float-subtle {
  0%, 100% { transform: translateY(0px) translateX(0px); }
  33% { transform: translateY(-15px) translateX(5px); }
  66% { transform: translateY(5px) translateX(-5px); }
}
@keyframes grid-subtle {
  0% { transform: translateX(0) translateY(0); }
  100% { transform: translateX(60px) translateY(60px); }
}
.animate-pulse-professional {
  animation: pulse-professional 8s ease-in-out infinite;
}
.animate-float-subtle {
  animation: float-subtle 12s ease-in-out infinite;
}
.animate-grid-subtle {
  animation: grid-subtle 30s linear infinite;
}
</style>
