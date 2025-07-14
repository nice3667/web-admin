<script setup>
import { mdiCog } from '@mdi/js'
import { useSlots, computed } from 'vue'
import BaseIcon from '@/Components/BaseIcon.vue'
import BaseButton from '@/Components/BaseButton.vue'
import IconRounded from '@/Components/IconRounded.vue'

defineProps({
  icon: {
    type: String,
    default: null
  },
  title: {
    type: String,
    required: true
  },
  main: Boolean
})

const hasSlot = computed(() => useSlots().default)
</script>

<template>
  <section
    :class="{'pt-6':!main}"
    class="mb-6 flex items-center justify-between"
  >
    <div class="flex items-center justify-start">
      <IconRounded
        v-if="icon && main"
        :icon="icon"
        type="light"
        class="mr-3"
        bg
      />
      <BaseIcon
        v-else-if="icon"
        :path="icon"
        class="mr-2"
        size="20"
      />
      <h1
        :class="[
          main ? 'text-3xl font-bold tracking-wide' : 'text-2xl',
          'leading-tight',
          main ? 'text-black dark:text-white' : 'text-gray-900 dark:text-gray-100'
        ]"
      >
        {{ title }}
      </h1>
    </div>
    <slot v-if="hasSlot" />
  </section>
</template>
