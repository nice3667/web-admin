<script setup>
import { mdiForwardburger, mdiBackburger, mdiMenu } from "@mdi/js";
import { computed, reactive, ref } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import menuNavBar from "@/menuNavBar.js";
import { useDarkModeStore } from "@/Stores/darkMode.js";
import BaseIcon from "@/Components/BaseIcon.vue";
import Breadcrumb from "@/Components/Admin/Breadcrumb.vue";
import FormControl from "@/Components/FormControl.vue";
import NavBar from "@/Components/NavBar.vue";
import NavBarItemPlain from "@/Components/NavBarItemPlain.vue";
import AsideMenu from "@/Components/AsideMenu.vue";
import FooterBar from "@/Components/FooterBar.vue";

const layoutAsidePadding = "xl:pl-60";

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
  <div
    :class="{
      'overflow-hidden lg:overflow-visible': isAsideMobileExpanded,
    }"
  >
    <div
      :class="[layoutAsidePadding, { 'ml-60 lg:ml-0': isAsideMobileExpanded }]"
      class="w-screen min-h-screen pt-14 transition-position lg:w-auto bg-gray-50 dark:bg-slate-800 dark:text-slate-100"
    >
      <NavBar
        :menu="menuNavBar"
        :class="[
          layoutAsidePadding,
          { 'ml-60 lg:ml-0': isAsideMobileExpanded },
        ]"
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
        <NavBarItemPlain
          display="hidden lg:flex xl:hidden"
          @click.prevent="isAsideLgActive = true"
        >
          <BaseIcon :path="mdiMenu" size="24" />
        </NavBarItemPlain>
      </NavBar>
      <AsideMenu
        :is-aside-mobile-expanded="isAsideMobileExpanded"
        :is-aside-lg-active="isAsideLgActive"
        :menu="menuAside"
        @menu-click="menuClick"
        @aside-lg-close-click="isAsideLgActive = false"
      />
      <Breadcrumb />
      <slot />
      <FooterBar />
    </div>
  </div>
</template>
