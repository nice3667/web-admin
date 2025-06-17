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
  <nav class="fixed top-16 left-0 right-0 z-20 bg-gray-100 dark:bg-slate-800 h-16 border-b border-gray-200 dark:border-slate-700">
    <div class="flex items-center h-16">
      <!-- Navigation Links -->
      <div class="flex items-center px-4 space-x-4">
        <template v-for="(item, index) in menuItems" :key="item.to || item.label">
          <!-- Regular Menu Item -->
          <Link 
            v-if="!item.dropdown"
            :href="item.to"
            :class="[
              'flex items-center px-3 py-2 rounded-md text-sm font-medium',
              isActive(item.to)
                ? 'bg-gray-200 text-gray-900 dark:bg-slate-700 dark:text-white'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'
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
                'flex items-center px-3 py-2 rounded-md text-sm font-medium',
                item.items.some(subItem => isActive(subItem.to))
                  ? 'bg-gray-200 text-gray-900 dark:bg-slate-700 dark:text-white'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'
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
                class="ml-1 flex-shrink-0"
                :size="18"
              />
            </button>

            <!-- Dropdown Content -->
            <div
              v-if="activeDropdown === index"
              class="absolute left-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-slate-800 ring-1 ring-black ring-opacity-5 border border-gray-200 dark:border-slate-700"
            >
              <div class="py-1">
                <Link
                  v-for="subItem in item.items"
                  :key="subItem.to"
                  :href="subItem.to"
                  :class="[
                    'flex items-center px-4 py-2 text-sm',
                    isActive(subItem.to)
                      ? 'bg-gray-100 text-gray-900 dark:bg-slate-700 dark:text-white'
                      : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700'
                  ]"
                >
                  <BaseIcon
                    :path="subItem.icon"
                    class="flex-shrink-0 mr-2"
                    :size="18"
                  />
                  {{ subItem.label }}
                </Link>
              </div>
            </div>
          </div>
        </template>
      </div>

      <!-- Menu Toggle Button -->
      <div class="flex items-center">
        <NavBarItemPlain
          display="flex lg:hidden"
          @click.prevent="isMenuNavBarActive = !isMenuNavBarActive"
        >
          <BaseIcon :path="mdiMenu" size="24" />
        </NavBarItemPlain>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="isMenuNavBarActive" class="lg:hidden border-t border-gray-200 dark:border-slate-700">
      <div class="px-2 pt-2 pb-3 space-y-1">
        <template v-for="(item, index) in menuItems" :key="item.to || item.label">
          <!-- Regular Menu Item -->
          <Link 
            v-if="!item.dropdown"
            :href="item.to"
            :class="[
              'flex items-center px-3 py-2 rounded-md text-base font-medium',
              isActive(item.to)
                ? 'bg-gray-200 text-gray-900 dark:bg-slate-700 dark:text-white'
                : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'
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
          <div v-else>
            <button
              @click="toggleDropdown(index)"
              :class="[
                'flex items-center w-full px-3 py-2 rounded-md text-base font-medium',
                item.items.some(subItem => isActive(subItem.to))
                  ? 'bg-gray-200 text-gray-900 dark:bg-slate-700 dark:text-white'
                  : 'text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white'
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
                class="ml-1 flex-shrink-0"
                :size="18"
              />
            </button>
            <div v-if="activeDropdown === index" class="pl-4 border-l-2 border-gray-200 dark:border-slate-700">
              <Link
                v-for="subItem in item.items"
                :key="subItem.to"
                :href="subItem.to"
                :class="[
                  'flex items-center px-4 py-2 text-sm',
                  isActive(subItem.to)
                    ? 'bg-gray-100 text-gray-900 dark:bg-slate-700 dark:text-white'
                    : 'text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-slate-700'
                ]"
              >
                <BaseIcon
                  :path="subItem.icon"
                  class="flex-shrink-0 mr-2"
                  :size="18"
                />
                {{ subItem.label }}
              </Link>
            </div>
          </div>
        </template>
      </div>
    </div>
  </nav>
</template> 