<script setup>
import { ref } from "vue";
import { mdiMenu, mdiMonitor, mdiChartBar, mdiCashMultiple, mdiGift, mdiAccountGroup, mdiHelpCircle, mdiChevronDown, mdiViewDashboard, mdiAccountDetails, mdiClockOutline, mdiTicket, mdiFrequentlyAskedQuestions, mdiEmail } from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import NavBarMenuList from "@/Components/NavBarMenuList.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import { Link, usePage } from "@inertiajs/vue3";

const menuItems = [
  {
    label: "Dashboard",
    to: "/admin",
    icon: mdiViewDashboard
  },
  {
    label: "Report",
    icon: mdiChartBar,
    dropdown: true,
    items: [
      {
        label: "Clients",
        to: "/admin/reports/clients",
        icon: mdiAccountGroup
      },
      {
        label: "Client Account",
        to: "/admin/reports/client-account",
        icon: mdiAccountDetails
      },
      {
        label: "Client Transaction",
        to: "/admin/reports/client-transaction",
        icon: mdiCashMultiple
      },
      {
        label: "Transactions Pending",
        to: "/admin/reports/transactions-pending",
        icon: mdiClockOutline
      },
      {
        label: "Reward History",
        to: "/admin/reports/reward-history",
        icon: mdiGift
      }
    ]
  },
  {
    label: "Report1",
    icon: mdiChartBar,
    dropdown: true,
    items: [
      {
        label: "Clients",
        to: "/admin/reports1/clients1",
        icon: mdiAccountGroup
      },
      {
        label: "Client Account",
        to: "/admin/reports1/client-account1",
        icon: mdiAccountDetails
      },
      {
        label: "Client Transaction",
        to: "/admin/reports1/client-transaction1",
        icon: mdiCashMultiple
      },
      {
        label: "Transactions Pending",
        to: "/admin/reports1/transactions-pending1",
        icon: mdiClockOutline
      },
      {
        label: "Reward History",
        to: "/admin/reports1/reward-history1",
        icon: mdiGift
      }
    ]
  },
  {
    label: "Report2",
    icon: mdiChartBar,
    dropdown: true,
    items: [
      {
        label: "Clients",
        to: "/admin/reports2/clients2",
        icon: mdiAccountGroup
      },
      {
        label: "Client Account",
        to: "/admin/reports2/client-account2",
        icon: mdiAccountDetails
      },
      {
        label: "Client Transaction",
        to: "/admin/reports2/client-transaction2",
        icon: mdiCashMultiple
      },
      {
        label: "Transactions Pending",
        to: "/admin/reports2/transactions-pending2",
        icon: mdiClockOutline
      },
      {
        label: "Reward History",
        to: "/admin/reports2/reward-history2",
        icon: mdiGift
      }
    ]
  },
  {
    label: "Rebate",
    to: "/admin/rebate",
    icon: mdiCashMultiple
  },
  {
    label: "Promo",
    to: "/admin/promo",
    icon: mdiGift
  },
  {
    label: "Referral Agent",
    to: "/admin/referral",
    icon: mdiAccountGroup
  },
  {
    label: "Support",
    icon: mdiHelpCircle,
    dropdown: true,
    items: [
      {
        label: "Tickets",
        to: "/admin/support/tickets",
        icon: mdiTicket
      },
      {
        label: "FAQ",
        to: "/admin/support/faq",
        icon: mdiFrequentlyAskedQuestions
      },
      {
        label: "Contact Us",
        to: "/admin/support/contact",
        icon: mdiEmail
      }
    ]
  }
];

const props = defineProps({
  menu: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["menu-click"]);

const isMenuNavBarActive = ref(false);
const activeDropdown = ref(null);

const menuClick = (event, item) => {
  emit("menu-click", event, item);
};

const toggleDropdown = (index) => {
  activeDropdown.value = activeDropdown.value === index ? null : index;
};

const currentPath = usePage().url;

const isActive = (path) => {
  if (path === '/admin' && currentPath === '/admin') {
    return true;
  }
  return path !== '/admin' && currentPath.startsWith(path);
};
</script>

<template>
  <nav class="fixed top-16 left-0 right-0 z-20 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-800 dark:to-slate-900 h-16 border-b border-blue-200 dark:border-slate-700 shadow-lg backdrop-blur-sm">
    <div class="flex items-center justify-between h-16 px-6">
      <!-- Desktop Navigation -->
      <div class="hidden lg:flex items-center space-x-2">
        <template v-for="(item, index) in menuItems" :key="item.to || item.label">
          <!-- Regular Menu Item -->
          <Link 
            v-if="!item.dropdown"
            :href="item.to"
            :class="[
              'flex items-center px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-300 transform hover:scale-105',
              isActive(item.to)
                ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg'
                : 'text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 hover:bg-white/50 dark:hover:bg-slate-700/50'
            ]"
          >
            <BaseIcon
              :path="item.icon"
              class="flex-shrink-0"
              :size="18"
            />
            <span class="ml-2">{{ item.label }}</span>
          </Link>

          <!-- Dropdown Menu Item -->
          <div v-else class="relative">
            <button
              @click="toggleDropdown(index)"
              :class="[
                'flex items-center px-4 py-2 rounded-xl text-sm font-semibold transition-all duration-300 transform hover:scale-105',
                item.items.some(subItem => isActive(subItem.to))
                  ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg'
                  : 'text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 hover:bg-white/50 dark:hover:bg-slate-700/50'
              ]"
            >
              <BaseIcon
                :path="item.icon"
                class="flex-shrink-0"
                :size="18"
              />
              <span class="ml-2">{{ item.label }}</span>
              <BaseIcon
                :path="mdiChevronDown"
                class="ml-1 flex-shrink-0 transition-transform duration-300"
                :class="{ 'rotate-180': activeDropdown === index }"
                :size="18"
              />
            </button>

            <!-- Dropdown Content -->
            <div
              v-if="activeDropdown === index"
              class="absolute left-0 mt-3 w-56 rounded-2xl shadow-2xl bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm ring-1 ring-blue-200 dark:ring-slate-700 border border-blue-100 dark:border-slate-600 animate-fadeIn"
            >
              <div class="py-2">
                <Link
                  v-for="subItem in item.items"
                  :key="subItem.to"
                  :href="subItem.to"
                  :class="[
                    'flex items-center px-4 py-3 text-sm font-medium transition-all duration-200 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 dark:hover:from-slate-700 dark:hover:to-slate-600',
                    isActive(subItem.to)
                      ? 'bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 dark:from-slate-700 dark:to-slate-600 dark:text-blue-300'
                      : 'text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400'
                  ]"
                >
                  <BaseIcon
                    :path="subItem.icon"
                    class="flex-shrink-0 mr-3"
                    :size="18"
                  />
                  {{ subItem.label }}
                </Link>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Mobile Navigation -->
      <div class="flex lg:hidden items-center justify-between w-full">
        <!-- Mobile Menu Button -->
        <button
          @click="isMenuNavBarActive = !isMenuNavBarActive"
          class="flex items-center justify-center w-10 h-10 rounded-xl bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
        >
          <BaseIcon :path="mdiMenu" size="20" />
        </button>

        <!-- Mobile Menu -->
        <div
          v-if="isMenuNavBarActive"
          class="absolute top-16 left-0 right-0 bg-white/95 dark:bg-slate-800/95 backdrop-blur-sm border-b border-blue-200 dark:border-slate-700 shadow-2xl animate-slideDown"
        >
          <div class="px-4 pt-4 pb-6 space-y-2">
            <template v-for="(item, index) in menuItems" :key="item.to || item.label">
              <!-- Regular Menu Item -->
              <Link 
                v-if="!item.dropdown"
                :href="item.to"
                :class="[
                  'flex items-center px-4 py-3 rounded-xl text-base font-semibold transition-all duration-300',
                  isActive(item.to)
                    ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg'
                    : 'text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700'
                ]"
              >
                <BaseIcon
                  :path="item.icon"
                  class="flex-shrink-0"
                  :size="20"
                />
                <span class="ml-3">{{ item.label }}</span>
              </Link>

              <!-- Dropdown Menu Item -->
              <div v-else>
                <button
                  @click="toggleDropdown(index)"
                  :class="[
                    'flex items-center w-full px-4 py-3 rounded-xl text-base font-semibold transition-all duration-300',
                    item.items.some(subItem => isActive(subItem.to))
                      ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-lg'
                      : 'text-gray-700 hover:text-blue-600 dark:text-gray-300 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700'
                  ]"
                >
                  <BaseIcon
                    :path="item.icon"
                    class="flex-shrink-0"
                    :size="20"
                  />
                  <span class="ml-3">{{ item.label }}</span>
                  <BaseIcon
                    :path="mdiChevronDown"
                    class="ml-auto flex-shrink-0 transition-transform duration-300"
                    :class="{ 'rotate-180': activeDropdown === index }"
                    :size="20"
                  />
                </button>
                <div v-if="activeDropdown === index" class="mt-2 ml-4 pl-4 border-l-2 border-blue-200 dark:border-slate-700 space-y-1">
                  <Link
                    v-for="subItem in item.items"
                    :key="subItem.to"
                    :href="subItem.to"
                    :class="[
                      'flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
                      isActive(subItem.to)
                        ? 'bg-gradient-to-r from-blue-100 to-indigo-100 text-blue-700 dark:from-slate-700 dark:to-slate-600 dark:text-blue-300'
                        : 'text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-blue-50 dark:hover:bg-slate-700'
                    ]"
                  >
                    <BaseIcon
                      :path="subItem.icon"
                      class="flex-shrink-0 mr-3"
                      :size="18"
                    />
                    {{ subItem.label }}
                  </Link>
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeIn {
  animation: fadeIn 0.3s ease-out;
}

.animate-slideDown {
  animation: slideDown 0.3s ease-out;
}
</style> 