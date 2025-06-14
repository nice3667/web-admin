<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3"
import {
  mdiMultimedia,
  mdiArrowLeftBoldOutline
} from "@mdi/js"
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue"
import SectionMain from "@/Components/SectionMain.vue"
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import CardBox from "@/Components/CardBox.vue"
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'

const props = defineProps({
  media: {
    type: Object,
    default: () => ({}),
  },
  typeOptions: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  _method: 'put',
  type: props.media.type,
  name: props.media.filename,
  alt: props.media.alt,
  file: props.media.file,
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Update media" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiMultimedia"
        title="Update media"
        main
      >
        <BaseButton
          :route-name="route('admin.media.index')"
          :icon="mdiArrowLeftBoldOutline"
          label="Back"
          color="white"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>
      <CardBox
        form
        @submit.prevent="form.post(route('admin.media.update', props.media.id))"
      >
      <FormField
          label="Type"
          :class="{ 'text-red-400': form.errors.type }"
        >
          <FormControl
            v-model="form.type"
            type="select"
            placeholder="--Select Type--"
            :error="form.errors.type"
            :options="typeOptions"
          >
            <div class="text-red-400 text-sm" v-if="form.errors.type">
              {{ form.errors.type }}
            </div>
          </FormControl>
        </FormField>
        <FormField
          label="Name"
          :class="{ 'text-red-400': form.errors.name }"
        >
          <FormControl
            v-model="form.name"
            type="text"
            placeholder="Enter Name"
            :error="form.errors.name"
          >
            <div class="text-red-400 text-sm" v-if="form.errors.name">
              {{ form.errors.name }}
            </div>
          </FormControl>
        </FormField>

        <FormField
          label="Alt"
          :class="{ 'text-red-400': form.errors.alt }"
        >
          <FormControl
            v-model="form.alt"
            type="text"
            placeholder="Enter Alt"
            :error="form.errors.alt"
          >
            <div class="text-red-400 text-sm" v-if="form.errors.alt">
              {{ form.errors.alt }}
            </div>
          </FormControl>
        </FormField>
        <div class="w-32 rounded">
          <div v-if="media.aggregateType !== 'image'"  v-html="media.mediaTypeIcon">
          </div>
          <div v-else>
            <img :src="media.url" :alt="media.alt" class="block h-auto w-full max-w-full bg-gray-100 dark:bg-slate-800" />
          </div>
        </div>
        <FormField
          label="file"
          :class="{ 'text-red-400': form.errors.file }"
        >
          <FormControl
            v-model="form.file"
            type="file"
            placeholder="Select File"
            :error="form.errors.file"
            @input="form.file = $event.target.files[0]"
          >
            <div class="text-red-400 text-sm" v-if="form.errors.file">
              {{ form.errors.file }}
            </div>
          </FormControl>
        </FormField>
        <template #footer>
          <BaseButtons>
            <BaseButton
              type="submit"
              color="info"
              label="Submit"
              :class="{ 'opacity-25': form.processing }"
              :disabled="form.processing"
            />
          </BaseButtons>
        </template>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>
