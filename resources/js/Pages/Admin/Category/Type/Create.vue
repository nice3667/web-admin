<script setup>
import { Head, Link, useForm } from "@inertiajs/vue3"
import {
  mdiSelectGroup,
  mdiArrowLeftBoldOutline
} from "@mdi/js"
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue"
import SectionMain from "@/Components/SectionMain.vue"
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import CardBox from "@/Components/CardBox.vue"
import FormField from '@/Components/FormField.vue'
import FormControl from '@/Components/FormControl.vue'
import FormCheckRadio from '@/Components/FormCheckRadio.vue'
import BaseButton from '@/Components/BaseButton.vue'
import BaseButtons from '@/Components/BaseButtons.vue'

const form = useForm({
  name: '',
  machine_name: '',
  description: '',
  is_flat: ''
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Create Category Type" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiSelectGroup"
        title="Add Category Type"
        main
      >
        <BaseButton
          :route-name="route('admin.category.type.index')"
          :icon="mdiArrowLeftBoldOutline"
          label="Back"
          color="white"
          rounded-full
          small
        />
      </SectionTitleLineWithButton>
      <CardBox
        form
        @submit.prevent="form.post(route('admin.category.type.store'))"
      >
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
          label="Machine Name"
          :class="{ 'text-red-400': form.errors.machine_name }"
        >
          <FormControl
            v-model="form.machine_name"
            type="text"
            placeholder="Enter Machine Name"
            :error="form.errors.name"
          >
            <div class="text-red-400 text-sm" v-if="form.errors.machine_name">
              {{ form.errors.machine_name }}
            </div>
          </FormControl>
        </FormField>
        <FormField
          label="Description"
          :class="{ 'text-red-400': form.errors.description }"
        >
          <FormControl
            v-model="form.description"
            type="text"
            placeholder="Enter Description"
            :error="form.errors.description"
          >
            <div class="text-red-400 text-sm" v-if="form.errors.description">
              {{ form.errors.description }}
            </div>
          </FormControl>
        </FormField>
        <FormField
          :class="{ 'text-red-400': form.errors.is_flat }"
        >
          <FormCheckRadio
            v-model="form.is_flat"
            type="checkbox"
            name="form.is_flat"
            label="Use Flat Category"
            inputValue="1"
          />
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
