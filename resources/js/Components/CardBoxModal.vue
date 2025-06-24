<script setup>
import { computed } from 'vue'
import { mdiClose } from '@mdi/js'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'
import CardBox from '@/Components/CardBox.vue'
import OverlayLayer from '@/Components/OverlayLayer.vue'

const props = defineProps({
  title: {
    type: String,
    default: null
  },
  largeTitle: {
    type: String,
    default: null
  },
  button: {
    type: String,
    default: 'info'
  },
  buttonLabel: {
    type: String,
    default: 'Done'
  },
  hasCancel: Boolean,
  modelValue: {
    type: [String, Number, Boolean],
    default: null
  }
})

const emit = defineEmits(['update:modelValue', 'cancel', 'confirm'])

const value = computed({
  get: () => props.modelValue,
  set: value => emit('update:modelValue', value)
})

const confirmCancel = mode => {
  value.value = false
  emit(mode)
}

const confirm = () => confirmCancel('confirm')

const cancel = () => confirmCancel('cancel')
</script>

<template>
  <OverlayLayer
    :modelValue="value"
    @overlay-click="cancel"
  >
    <div class="flex justify-center items-center min-h-screen">
      <div class="relative w-[480px] h-[664px] max-w-full p-2 md:p-6 flex flex-col justify-center items-center">
        <!-- Absolute X button -->
        <button
          class="absolute top-4 right-4 z-50 bg-white/80 hover:bg-gray-100 rounded-full p-2 shadow focus:outline-none"
          @click="cancel"
          aria-label="Close"
        >
          <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
        <CardBox
          v-show="value"
          :title="title"
          class="w-[480px] h-[664px] flex flex-col justify-center items-center shadow-2xl border border-gray-200 dark:border-slate-700 bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-slate-800 p-0 md:p-4 rounded-3xl z-40 transition-all duration-300"
          modal
        >
          <div class="space-y-6">
            <h1 v-if="largeTitle" class="text-2xl md:text-3xl font-bold text-blue-700 dark:text-blue-200 mb-4">{{ largeTitle }}</h1>
            <slot />
          </div>
          <template #footer>
            <div v-if="buttonLabel && button" class="flex flex-col md:flex-row justify-end items-center gap-3 px-6 pb-6 md:px-16 md:pb-10 border-t border-gray-100 dark:border-slate-700 bg-gradient-to-r from-white/80 to-blue-50/80 dark:from-slate-900/80 dark:to-slate-800/80">
              <BaseButton
                :label="buttonLabel"
                :color="button"
                class="w-full md:w-auto px-8 py-3 font-semibold text-lg shadow-md hover:shadow-xl transition-all duration-200"
                @click="confirm"
              />
              <BaseButton
                v-if="hasCancel"
                label="Cancel"
                color="gray"
                outline
                class="w-full md:w-auto px-8 py-3 font-semibold text-lg"
                @click="cancel"
              />
            </div>
          </template>
        </CardBox>
      </div>
    </div>
  </OverlayLayer>
</template>
