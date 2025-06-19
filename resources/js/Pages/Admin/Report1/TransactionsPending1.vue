<script setup>
import { Head } from "@inertiajs/vue3";
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue";
import SectionMain from "@/Components/SectionMain.vue";
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue";

const props = defineProps({
  transactions: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_pending: 0,
      total_amount: 0
    })
  },
  filters: {
    type: Object,
    default: () => ({})
  }
});
</script>

<template>
  <LayoutAuthenticated>
    <Head title="ธุรกรรมที่รอดำเนินการ" />
    
    <SectionMain>
      <SectionTitleLineWithButton
        title="ธุรกรรมที่รอดำเนินการ"
        main
      >
      </SectionTitleLineWithButton>

      <!-- Content here -->
      <div class="mt-6">
        <p>อยู่ระหว่างการพัฒนา...</p>
      </div>
    </SectionMain>
  </LayoutAuthenticated>
</template> 