<script setup>
import { Head } from "@inertiajs/vue3";
import {
  mdiSelectGroup,
  mdiPlus,
  mdiAlertBoxOutline,
  mdiArrowLeftBoldOutline,
} from "@mdi/js";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";
import BaseButton from "@/Components/BaseButton.vue";
import CardBox from "@/Components/CardBox.vue";
import BaseButtons from "@/Components/BaseButtons.vue";
import NotificationBar from "@/Components/NotificationBar.vue";
import CategoryItemList from "@/Components/Admin/CategoryItemList.vue";

const props = defineProps({
  items: {
    type: Object,
    default: () => ({}),
  },
  categoryType: {
    type: Object,
    default: () => ({}),
  },
  can: {
    type: Object,
    default: () => ({}),
  },
});
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Categories" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiSelectGroup"
        title="Categories"
        main
      >
        <BaseButtons type="justify-start lg:justify-end" no-wrap>
          <BaseButton
            :route-name="route('admin.category.type.index')"
            :icon="mdiArrowLeftBoldOutline"
            label="Back"
            color="white"
            rounded-full
            small
          />
          <BaseButton
            v-if="can.delete"
            :route-name="
              route('admin.category.type.item.create', categoryType.id)
            "
            :icon="mdiPlus"
            label="Add"
            color="info"
            rounded-full
            small
          />
        </BaseButtons>
      </SectionTitleLineWithButton>
      <NotificationBar
        :key="Date.now()"
        v-if="$page.props.flash.message"
        color="success"
        :icon="mdiAlertBoxOutline"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>
      <CardBox class="mb-6" has-table>
        <table
          class="w-full text-sm bg-white border border-collapse shadow-sm border-slate-400 dark:border-slate-500 dark:bg-slate-800"
        >
          <tbody>
            <tr>
              <td
                class="hidden p-4 pl-8 text-slate-500 dark:text-slate-400 lg:block"
              >
                Name
              </td>
              <td data-label="Name">
                {{ categoryType.name }}
              </td>
            </tr>
            <tr>
              <td
                class="hidden p-4 pl-8 text-slate-500 dark:text-slate-400 lg:block"
              >
                Machine name
              </td>
              <td data-label="Machine Name">
                {{ categoryType.machine_name }}
              </td>
            </tr>
          </tbody>
        </table>
        <table>
          <thead>
            <tr>
              <th>Name</th>
              <th>Description</th>
              <th>Enable</th>
              <th v-if="can.edit || can.delete">Actions</th>
            </tr>
          </thead>

          <tbody>
            <template v-for="item in items">
              <CategoryItemList
                :item="item"
                :categoryType="categoryType"
                :can="can"
                :level="0"
              />
            </template>
          </tbody>
        </table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
