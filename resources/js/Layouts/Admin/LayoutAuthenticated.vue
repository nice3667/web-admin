<script setup>
import { mdiForwardburger, mdiBackburger, mdiMenu } from "@mdi/js";
import { computed, reactive, ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import menuNavBar from "@/menuNavBar.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import FormControl from "@/Components/FormControl.vue";
import TopNavBar from "@/Components/TopNavBar.vue";
import BottomNavBar from "@/Components/BottomNavBar.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import FooterBar from "@/Components/FooterBar.vue";

const layoutAsidePadding = "";

let menuAside = reactive({});
menuAside = computed(() => usePage().props.menu);

const menuClick = (event, item) => {
  if (item.isLogout) {
    router.post(route("logout"));
  }
};
</script>

<template>
  <div class="style-bluewhiteblack">
    <div>
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
      <div class="flex flex-col flex-1 w-full pt-12 sm:pt-14 md:pt-16 lg:pt-20">
        <main class="flex-1">
          <slot />
        </main>
        <FooterBar />
      </div>
    </div>
  </div>
</template>
