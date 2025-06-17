<script setup>
import { ref } from "vue";
import { mdiClose, mdiDotsVertical } from "@mdi/js";
import { containerMaxW } from "@/config.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import NavBarMenuList from "@/Components/NavBarMenuList.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";

defineProps({
  menu: {
    type: Array,
    required: true,
  },
});

const emit = defineEmits(["menu-click"]);

const menuClick = (event, item) => {
  emit("menu-click", event, item);
};

const isMenuNavBarActive = ref(false);
</script>

<template>
  <nav
    class="fixed inset-x-0 z-30 w-screen bg-white h-16 transition-position lg:w-auto dark:bg-slate-900"
  >
    <div class="flex lg:items-stretch" :class="containerMaxW">
      <div class="flex items-stretch flex-1 h-16">
        <!-- Text on the left -->
        <div class="flex items-center px-8">
          <span class="text-2xl font-bold text-gray-700 dark:text-gray-200">Exness Dashboard</span>
        </div>
        <slot />
      </div>
      <div class="flex items-stretch flex-none h-16 lg:hidden">
        <NavBarItemPlain
          @click.prevent="isMenuNavBarActive = !isMenuNavBarActive"
        >
          <BaseIcon
            :path="isMenuNavBarActive ? mdiClose : mdiDotsVertical"
            size="24"
          />
        </NavBarItemPlain>
      </div>
      <div
        class="absolute left-0 w-screen overflow-y-auto shadow-lg max-h-screen-menu lg:overflow-visible top-16 bg-white lg:w-auto lg:flex lg:static lg:shadow-none dark:bg-slate-900"
        :class="[isMenuNavBarActive ? 'block' : 'hidden']"
      >
        <NavBarMenuList :menu="menu" @menu-click="menuClick" />
      </div>
    </div>
  </nav>
</template> 