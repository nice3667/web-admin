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
    v-show="value"
    @overlay-click="cancel"
  >
    <div class="flex justify-center items-center min-h-screen">
      <CardBox
        v-show="value"
        :title="title"
        class="shadow-2xl border border-gray-200 dark:border-slate-700 max-w-4xl w-full bg-gradient-to-br from-white to-blue-50 dark:from-slate-900 dark:to-slate-800 p-0 md:p-4 rounded-3xl z-50 transition-all duration-300"
        :header-icon="mdiClose"
        modal
        @header-icon-click="cancel"
      >
        <div class="space-y-6">
          <h1 v-if="largeTitle" class="text-2xl md:text-3xl font-bold text-blue-700 dark:text-blue-200 mb-4">{{ largeTitle }}</h1>
          <slot />
        </div>
        <template #footer>
          <div class="flex flex-col md:flex-row justify-end items-center gap-3 px-6 pb-6 md:px-16 md:pb-10 border-t border-gray-100 dark:border-slate-700 bg-gradient-to-r from-white/80 to-blue-50/80 dark:from-slate-900/80 dark:to-slate-800/80">
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
  </OverlayLayer>
</template>
