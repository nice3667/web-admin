<script setup>
import { mdiLogout, mdiAccount } from "@mdi/js";
import { containerMaxW } from "@/config.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import { router, usePage } from "@inertiajs/vue3";

// Get user data from Inertia page props
const user = usePage().props.auth?.user || { name: 'Admin' };

const logout = () => {
  router.post(route("logout"));
};
</script>

<template>
  <nav
    class="fixed inset-x-0 z-30 w-screen bg-black h-12 sm:h-14 md:h-16 transition-position lg:w-auto shadow-2xl border-b border-blue-500/30"
  >
    <div class="flex lg:items-stretch" :class="containerMaxW">
      <div class="flex items-stretch flex-1 h-12 sm:h-14 md:h-16">
        <!-- Logo and Brand -->
        <div class="flex items-center px-2 sm:px-4 md:px-8">
          <div class="flex items-center space-x-2 sm:space-x-3">
            <!-- Removed blue right arrow SVG here -->
            <div class="hidden xs:block">
              <span class="text-lg sm:text-xl md:text-2xl font-bold bg-gradient-to-r from-blue-400 to-blue-300 bg-clip-text text-transparent">Admin Dashboard</span>
            </div>
          </div>
        </div>
        <slot />
      </div>

      <!-- Right Side Controls -->
      <div class="flex items-center space-x-2 sm:space-x-3 md:space-x-4 px-2 sm:px-4 md:px-6">
        <!-- User Profile & Logout -->
        <div class="flex items-center space-x-2 sm:space-x-3">
          <!-- User Avatar & Name -->
          <div class="flex items-center space-x-2 sm:space-x-3 bg-gray-800/80 px-2 sm:px-3 md:px-4 py-1 sm:py-2 rounded-lg sm:rounded-xl backdrop-blur-sm border border-blue-500/20 shadow-lg">
            <div class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 bg-gradient-to-br from-blue-500 to-blue-600 rounded-md sm:rounded-lg flex items-center justify-center shadow-lg ring-1 ring-blue-400/50">
              <BaseIcon
                :path="mdiAccount"
                size="12"
                class="sm:text-sm md:text-base text-white"
              />
            </div>
            <div class="hidden sm:block">
              <div class="text-xs sm:text-sm font-semibold text-white truncate max-w-20 sm:max-w-24 md:max-w-32">
                {{ user.name || 'Admin' }}
              </div>
              <div class="text-xs text-gray-400 hidden md:block truncate max-w-20 sm:max-w-24 md:max-w-32">
                {{ user.email || 'admin@exness.com' }}
              </div>
            </div>
          </div>

          <!-- Logout Button -->
          <button
            @click="logout"
            class="flex items-center justify-center w-8 h-8 sm:w-9 sm:h-9 md:w-10 md:h-10 rounded-lg sm:rounded-xl bg-gradient-to-r from-red-500 to-red-600 text-white shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 ring-2 ring-red-400/50"
            title="Logout"
          >
            <BaseIcon
              :path="mdiLogout"
              size="16"
              class="sm:text-lg md:text-xl"
            />
          </button>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Smooth transitions */
* {
  transition: all 0.3s ease;
}

/* Enhanced shadows */
.shadow-xl {
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Hover effects */
button:hover {
  transform: translateY(-2px);
}

/* Glass morphism effect */
.backdrop-blur-sm {
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}

/* Custom breakpoint for extra small screens */
@media (min-width: 475px) {
  .xs\:block {
    display: block;
  }
}

/* Responsive text sizing */
@media (max-width: 640px) {
  .text-lg {
    font-size: 0.875rem;
    line-height: 1.25rem;
  }
}

@media (min-width: 640px) and (max-width: 768px) {
  .text-xl {
    font-size: 1.125rem;
    line-height: 1.75rem;
  }
}
</style>
