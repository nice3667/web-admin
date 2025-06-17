<script setup>
import { mdiForwardburger, mdiBackburger, mdiMenu } from "@mdi/js";
import { computed, reactive, ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import menuNavBar from "@/menuNavBar.js";
import { useDarkModeStore } from "@/Stores/darkMode.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import FormControl from "@/Components/FormControl.vue";
import TopNavBar from "@/Components/TopNavBar.vue";
import BottomNavBar from "@/Components/BottomNavBar.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import FooterBar from "@/Components/FooterBar.vue";

const layoutAsidePadding = "";

const darkModeStore = useDarkModeStore();

const isAsideMobileExpanded = ref(false);
const isAsideLgActive = ref(false);

let menuAside = reactive({});
menuAside = computed(() => usePage().props.menu);

const menuClick = (event, item) => {
  if (item.isToggleLightDark) {
    darkModeStore.set();
  }

  if (item.isLogout) {
    router.post(route("logout"));
  }
};
</script>

<template>
  <div>
    <div class="w-screen min-h-screen bg-gray-50 dark:bg-slate-800 dark:text-slate-100">
      <!-- Top NavBar -->
      <TopNavBar
        :menu="menuNavBar"
        @menu-click="menuClick"
      >
        <NavBarItemPlain
          display="flex lg:hidden"
          @click.prevent="isAsideMobileExpanded = !isAsideMobileExpanded"
        >
          <BaseIcon
            :path="isAsideMobileExpanded ? mdiBackburger : mdiForwardburger"
            size="24"
          />
        </NavBarItemPlain>
      </TopNavBar>

      <!-- Bottom NavBar -->
      <BottomNavBar
        :menu="menuNavBar"
        @menu-click="menuClick"
      />

      <div class="flex flex-col flex-1 w-full pt-32">
        <main class="flex-1">
          <slot />
        </main>
        <FooterBar />
      </div>
    </div>
  </div>
</template>
