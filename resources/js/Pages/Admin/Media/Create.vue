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
  typeOptions: {
    type: Object,
    default: () => ({}),
  },
})

const form = useForm({
  type: null,
  name: null,
  alt: null,
  file: null
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Create media" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiMultimedia"
        title="Add media"
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
        @submit.prevent="form.post(route('admin.media.store'))"
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
