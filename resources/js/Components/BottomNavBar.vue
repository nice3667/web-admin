<script setup>
import { ref } from "vue";
import { mdiMenu, mdiMonitor, mdiChartBar, mdiCashMultiple, mdiGift, mdiAccountGroup, mdiHelpCircle, mdiChevronDown, mdiViewDashboard, mdiAccountDetails, mdiClockOutline, mdiTicket, mdiFrequentlyAskedQuestions, mdiEmail } from "@mdi/js";
import BaseIcon from "@/Components/BaseIcon.vue";
import NavBarMenuList from "@/Components/NavBarMenuList.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import { Link, usePage } from "@inertiajs/vue3";

const menuItems = [
//   {
//     label: "Dashboard",
//     to: "/admin",
//     icon: mdiViewDashboard
//   },
  {
    label: "Customers",
    to: "/admin/customers",
    icon: mdiAccountGroup
  },
  {
    label: "XM",
    to: "/admin/xm",
    icon: mdiChartBar
  },
  {
    label: "Exness 1",
    icon: mdiAccountGroup,
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
      }
    ]
  },
  {
    label: "Exness 2",
    icon: mdiAccountGroup,
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
      }
    ]
  },
  {
    label: "Exness 3",
    icon: mdiAccountGroup,
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
      }
    ]
  },
//     ,
//   {
//     label: "Rebate",
//     to: "/admin/rebate",
//     icon: mdiCashMultiple
//     }
//     ,
//   {
//     label: "Promo",
//     to: "/admin/promo",
//     icon: mdiGift
//     }
//     ,
//   {
//     label: "Referral Agent",
//     to: "/admin/referral",
//     icon: mdiAccountGroup
//     }
//     ,
//   {
//     label: "Support",
//     icon: mdiHelpCircle,
//     dropdown: true,
//     items: [
//       {
//         label: "Tickets",
//         to: "/admin/support/tickets",
//         icon: mdiTicket
//       },
//       {
//         label: "FAQ",
//         to: "/admin/support/faq",
//         icon: mdiFrequentlyAskedQuestions
//       },
//       {
//         label: "Contact Us",
//         to: "/admin/support/contact",
//         icon: mdiEmail
//       }
//     ]
//   }
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
  <nav class="fixed top-12 sm:top-14 md:top-16 left-0 right-0 z-20 bg-gradient-to-r from-gray-900 via-black to-gray-900 h-12 sm:h-14 md:h-16 border-b border-blue-500/30 shadow-2xl backdrop-blur-sm">
    <div class="flex items-center justify-between h-12 sm:h-14 md:h-16 px-2 sm:px-4 md:px-6">
      <!-- Desktop Navigation -->
      <div class="hidden lg:flex items-center space-x-1 md:space-x-2">
        <template v-for="(item, index) in menuItems" :key="item.to || item.label">
          <!-- Regular Menu Item -->
          <Link
            v-if="!item.dropdown"
            :href="item.to"
            :class="[
              'flex items-center px-2 md:px-4 py-1 md:py-2 rounded-lg md:rounded-xl text-xs md:text-sm font-semibold transition-all duration-300 transform hover:scale-105',
              isActive(item.to)
                ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg ring-2 ring-blue-400/50'
                : 'text-gray-300 hover:text-blue-400 hover:bg-gray-800/50'
            ]"
          >
            <template v-if="item.icon && (item.icon.endsWith('.jpg') || item.icon.endsWith('.png') || item.icon.endsWith('.jpeg'))">
              <img :src="item.icon" alt="exness logo" class="flex-shrink-0" style="width: 18px; height: 18px; border-radius: 3px;" />
            </template>
            <template v-else>
              <BaseIcon :path="item.icon" class="flex-shrink-0 md:text-lg" :size="16" />
            </template>
            <span class="ml-1 md:ml-2 hidden xl:block">{{ item.label }}</span>
          </Link>

          <!-- Dropdown Menu Item -->
          <div v-else class="relative">
            <button
              @click="toggleDropdown(index)"
              :class="[
                'flex items-center px-2 md:px-4 py-1 md:py-2 rounded-lg md:rounded-xl text-xs md:text-sm font-semibold transition-all duration-300 transform hover:scale-105',
                item.items.some(subItem => isActive(subItem.to))
                  ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg ring-2 ring-blue-400/50'
                  : 'text-gray-300 hover:text-blue-400 hover:bg-gray-800/50'
              ]"
            >
              <BaseIcon
                :path="item.icon"
                class="flex-shrink-0 md:text-lg"
                :size="16"
              />
              <span class="ml-1 md:ml-2 hidden xl:block">{{ item.label }}</span>
              <BaseIcon
                :path="mdiChevronDown"
                class="ml-1 flex-shrink-0 transition-transform duration-300 hidden xl:block md:text-lg"
                :class="{ 'rotate-180': activeDropdown === index }"
                :size="16"
              />
            </button>

            <!-- Dropdown Content -->
            <div
              v-if="activeDropdown === index"
              class="absolute left-0 mt-2 md:mt-3 w-48 md:w-56 rounded-xl md:rounded-2xl shadow-2xl bg-gray-900/95 backdrop-blur-sm ring-1 ring-blue-500/30 border border-blue-500/20 animate-fadeIn"
            >
              <div class="py-1 md:py-2">
                <Link
                  v-for="subItem in item.items"
                  :key="subItem.to"
                  :href="subItem.to"
                  :class="[
                    'flex items-center px-3 md:px-4 py-2 md:py-3 text-xs md:text-sm font-medium transition-all duration-200 hover:bg-gray-800/50',
                    isActive(subItem.to)
                      ? 'bg-gradient-to-r from-blue-600/20 to-blue-500/20 text-blue-400 border-l-2 border-blue-500'
                      : 'text-gray-300 hover:text-blue-400'
                  ]"
                >
                  <template v-if="subItem.icon && (subItem.icon.endsWith && (subItem.icon.endsWith('.jpg') || subItem.icon.endsWith('.png') || subItem.icon.endsWith('.jpeg')))">
                    <img :src="subItem.icon" alt="icon" class="flex-shrink-0 mr-2 md:mr-3 md:text-lg" style="width: 16px; height: 16px; border-radius: 3px;" />
                  </template>
                  <template v-else>
                    <BaseIcon :path="subItem.icon" class="flex-shrink-0 mr-2 md:mr-3 md:text-lg" :size="16" />
                  </template>
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
          class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 rounded-lg sm:rounded-xl bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 ring-2 ring-blue-400/50"
        >
          <BaseIcon :path="mdiMenu" size="16" class="sm:text-lg md:text-xl" />
        </button>

        <!-- Mobile Menu -->
        <div
          v-if="isMenuNavBarActive"
          class="absolute top-12 sm:top-14 md:top-16 left-0 right-0 bg-gray-900/95 backdrop-blur-sm border-b border-blue-500/30 shadow-2xl animate-slideDown max-h-[calc(100vh-6rem)] overflow-y-auto"
        >
          <div class="px-2 sm:px-4 pt-2 sm:pt-4 pb-4 sm:pb-6 space-y-1 sm:space-y-2">
            <template v-for="(item, index) in menuItems" :key="item.to || item.label">
              <!-- Regular Menu Item -->
              <Link
                v-if="!item.dropdown"
                :href="item.to"
                :class="[
                  'flex items-center px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base font-semibold transition-all duration-300',
                  isActive(item.to)
                    ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg ring-2 ring-blue-400/50'
                    : 'text-gray-300 hover:text-blue-400 hover:bg-gray-800/50'
                ]"
              >
                <BaseIcon
                  :path="item.icon"
                  class="flex-shrink-0 sm:text-xl"
                  :size="18"
                />
                <span class="ml-2 sm:ml-3">{{ item.label }}</span>
              </Link>

              <!-- Dropdown Menu Item -->
              <div v-else>
                <button
                  @click="toggleDropdown(index)"
                  :class="[
                    'flex items-center w-full px-3 sm:px-4 py-2 sm:py-3 rounded-lg sm:rounded-xl text-sm sm:text-base font-semibold transition-all duration-300',
                    item.items.some(subItem => isActive(subItem.to))
                      ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow-lg ring-2 ring-blue-400/50'
                      : 'text-gray-300 hover:text-blue-400 hover:bg-gray-800/50'
                  ]"
                >
                  <BaseIcon
                    :path="item.icon"
                    class="flex-shrink-0 sm:text-xl"
                    :size="18"
                  />
                  <span class="ml-2 sm:ml-3 flex-1 text-left">{{ item.label }}</span>
                  <BaseIcon
                    :path="mdiChevronDown"
                    class="flex-shrink-0 transition-transform duration-300 sm:text-xl"
                    :class="{ 'rotate-180': activeDropdown === index }"
                    :size="18"
                  />
                </button>
                <div v-if="activeDropdown === index" class="mt-1 sm:mt-2 ml-3 sm:ml-4 pl-3 sm:pl-4 border-l-2 border-blue-500/50 space-y-1">
                  <Link
                    v-for="subItem in item.items"
                    :key="subItem.to"
                    :href="subItem.to"
                    :class="[
                      'flex items-center px-3 sm:px-4 py-2 rounded-md sm:rounded-lg text-xs sm:text-sm font-medium transition-all duration-200',
                      isActive(subItem.to)
                        ? 'bg-gradient-to-r from-blue-600/20 to-blue-500/20 text-blue-400'
                        : 'text-gray-400 hover:text-blue-400 hover:bg-gray-800/50'
                    ]"
                  >
                    <BaseIcon
                      :path="subItem.icon"
                      class="flex-shrink-0 mr-2 sm:mr-3 sm:text-lg"
                      :size="16"
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

/* Responsive adjustments */
@media (max-width: 640px) {
  .text-xs {
    font-size: 0.75rem;
    line-height: 1rem;
  }

  .text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
}

@media (min-width: 640px) and (max-width: 768px) {
  .text-sm {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }

  .text-base {
    font-size: 1rem;
    line-height: 1.5rem;
  }
}

/* Custom breakpoint for extra large screens */
@media (min-width: 1280px) {
  .xl\:block {
    display: block;
  }
}
</style>
