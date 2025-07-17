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
  mdiFilterVariant,
  mdiChevronDown,
} from "@mdi/js";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import CardBox from "@/Components/CardBox.vue";
import CardBoxModal from "@/Components/CardBoxModal.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import BaseButton from "@/Components/BaseButton.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import { ref, onMounted, computed, watch } from "vue";
import FormControl from "@/Components/FormControl.vue";
import Pagination from "@/Components/Admin/Pagination.vue";
import BaseIcon from "@/Components/BaseIcon.vue";
import TopNavBar from "@/Components/TopNavBar.vue";
import BottomNavBar from '@/Components/BottomNavBar.vue';

const props = defineProps({
  clients: {
    type: Object,
    required: true,
    default: () => ({})
  },
  stats: {
    type: Object,
    required: true,
    default: () => ({
      total_accounts: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_profit: 0,
      total_client_uids: 0,
    }),
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
  error: {
    type: String,
    default: null
  },
  user_email: {
    type: String,
    default: null
  },
  data_source: {
    type: String,
    default: 'Database'
  }
});

const isModalActive = ref(false);
const showAdvancedSearch = ref(false);

const filters = ref({
  partner_account: props.filters.partner_account || "",
  client_uid: props.filters.client_uid || "",
  client_country: props.filters.client_country || "",
  client_status: props.filters.client_status || "",
  reg_date: props.filters.reg_date || "",
  kyc_passed: props.filters.kyc_passed || "",
});

// Initialize filters from props
onMounted(() => {
  filters.value = { ...props.filters };
});

// Computed properties for data
const accounts = computed(() => {
  return props.clients.data || [];
});

// Apply search filters
const applyFilters = () => {
  router.get(
    "/admin/reports/client-account",
    { ...filters.value },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
  showAdvancedSearch.value = false;
};

// Reset filters
const resetFilters = () => {
  filters.value = {
    partner_account: "",
    client_uid: "",
    client_country: "",
    client_status: "",
    reg_date: "",
    kyc_passed: "",
  };
  router.get(
    "/admin/reports/client-account",
    {},
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
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
  if (!date) return "-";
  return new Date(date).toLocaleDateString("th-TH", {
    year: "numeric",
    month: "long",
    day: "numeric",
  });
};

// Format country code to name
const formatCountry = (code) => {
  if (!code || code === "-" || code === "") return "-";
  // Return full country name if found, otherwise show the code with a prefix
  return countryNames[code] || `[${code}]`;
};

// Get status class
const getStatusClass = (status) => {
  const classes = {
    ACTIVE: "bg-green-100 text-green-800",
    INACTIVE: "bg-red-100 text-red-800",
    PENDING: "bg-yellow-100 text-yellow-800",
  };
  return classes[status] || "bg-gray-100 text-gray-800";
};

// Add country code to name mapping
const countryNames = {
  TH: "Thailand",
  US: "United States",
  GB: "United Kingdom",
  CN: "China",
  JP: "Japan",
  KR: "South Korea",
  SG: "Singapore",
  MY: "Malaysia",
  ID: "Indonesia",
  VN: "Vietnam",
  PH: "Philippines",
  MM: "Myanmar",
  KH: "Cambodia",
  LA: "Laos",
  BN: "Brunei",
  // European countries
  DE: "Germany",
  FR: "France",
  IT: "Italy",
  ES: "Spain",
  NL: "Netherlands",
  BE: "Belgium",
  AT: "Austria",
  CH: "Switzerland",
  SE: "Sweden",
  NO: "Norway",
  DK: "Denmark",
  FI: "Finland",
  PT: "Portugal",
  IE: "Ireland",
  PL: "Poland",
  CZ: "Czech Republic",
  HU: "Hungary",
  RO: "Romania",
  BG: "Bulgaria",
  HR: "Croatia",
  GR: "Greece",
  // Americas
  CA: "Canada",
  MX: "Mexico",
  BR: "Brazil",
  AR: "Argentina",
  CL: "Chile",
  CO: "Colombia",
  PE: "Peru",
  // Middle East & Africa
  AE: "United Arab Emirates",
  SA: "Saudi Arabia",
  EG: "Egypt",
  ZA: "South Africa",
  NG: "Nigeria",
  AO: "Angola",
  KE: "Kenya",
  GH: "Ghana",
  MA: "Morocco",
  TN: "Tunisia",
  ET: "Ethiopia",
  UG: "Uganda",
  TZ: "Tanzania",
  ZW: "Zimbabwe",
  BW: "Botswana",
  MW: "Malawi",
  IL: "Israel",
  TR: "Turkey",
  // Asia Pacific
  AU: "Australia",
  NZ: "New Zealand",
  IN: "India",
  PK: "Pakistan",
  BD: "Bangladesh",
  LK: "Sri Lanka",
  HK: "Hong Kong",
  TW: "Taiwan",
  // Additional Southeast Asian countries
  BT: "Bhutan",
  NP: "Nepal",
  MV: "Maldives",
  // Other common countries
  RU: "Russia",
  UA: "Ukraine",
  BY: "Belarus",
  KZ: "Kazakhstan",
  UZ: "Uzbekistan",
};

// Computed properties for stats
const totalVolumeLots = computed(() => {
  return Number(props.stats?.total_volume_lots || 0);
});

const totalVolumeUsd = computed(() => {
  return Number(props.stats?.total_volume_usd || 0);
});

const totalReward = computed(() => {
  return Number(props.stats?.total_profit || 0);
});

const formattedStats = computed(() => {
  const stats = props.stats || {};
  return {
    total_accounts: stats.total_accounts ?? 0,
    total_volume_lots: Number(stats.total_volume_lots ?? 0).toFixed(4),
    total_volume_usd: Number(stats.total_volume_usd ?? 0).toFixed(4),
    total_profit: Number(stats.total_profit ?? 0).toFixed(4),
    total_client_uids: stats.total_client_uids ?? 0
  };
});

// Watch for props changes to update temp filters
watch(
  () => props.filters,
  (newFilters) => {
    filters.value = {
      partner_account: newFilters.partner_account || "",
      client_uid: newFilters.client_uid || "",
      client_country: newFilters.client_country || "",
      client_status: newFilters.client_status || "",
      reg_date: newFilters.reg_date || "",
      kyc_passed: newFilters.kyc_passed || "",
    };
  },
  { immediate: true }
);

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

// Add after filteredAccounts computed
const itemsPerPage =10
const currentPage = ref(props.clients.current_page || 1);
const startIndex = computed(() => (currentPage.value -1 * itemsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, accounts.value.length));
const paginatedAccounts = computed(() => {
  return accounts.value.slice(startIndex.value, endIndex.value);
});

watch(() => props.clients.current_page, (val) => {
  currentPage.value = val || 1;
});

// Pagination logic (XM style)
const goToPage = (page) => {
  if (page <1| page > Math.ceil(accounts.value.length / itemsPerPage)) return;
  currentPage.value = page;
};

const displayedPages = computed(() =>[object Object]if (!props.clients || !props.clients.last_page) return  const totalPages = props.clients.last_page;
  const currentPage = props.clients.current_page;
  const pages = [];
  if (totalPages <=7) [object Object]
    for (let i = 1; i <= totalPages; i++) [object Object]      pages.push(i);
    }
  } else[object Object]  let start = Math.max(currentPage - 31    let end = Math.min(currentPage + 3, totalPages);
    if (start > 1) [object Object]
      pages.push(1);
      if (start > 2        pages.push('...);
      }
    }
    for (let i = start; i <= end; i++) [object Object]      pages.push(i);
    }
    if (end < totalPages)[object Object]   if (end < totalPages - 1        pages.push('...');
      }
      pages.push(totalPages);
    }
  }
  return pages;
});
