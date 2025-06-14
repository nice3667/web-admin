<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiGift,
  mdiAlertBoxOutline,
} from "@mdi/js"
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue"
import SectionMain from "@/Components/SectionMain.vue"
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import CardBox from "@/Components/CardBox.vue"
import NotificationBar from "@/Components/NotificationBar.vue"
import BaseButton from "@/Components/BaseButton.vue"
import BaseButtons from "@/Components/BaseButtons.vue"
import { ref } from 'vue'

const rewards = ref([
  {
    id: 1,
    client_name: 'John Doe',
    reward_type: 'Bonus',
    amount: 100.00,
    status: 'Claimed',
    expiry_date: '2024-04-20',
    date_awarded: '2024-03-20'
  },
  // Add more sample data as needed
])

const filters = ref({
  search: '',
  reward_type: 'all',
  status: 'all',
  date_from: '',
  date_to: ''
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Reward History" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiGift"
        title="Reward History"
        main
      >
      </SectionTitleLineWithButton>

      <NotificationBar
        v-if="$page.props.flash.message"
        color="success"
        :icon="mdiAlertBoxOutline"
      >
        {{ $page.props.flash.message }}
      </NotificationBar>

      <CardBox class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="Search by client..."
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Reward Type</label>
            <select
              v-model="filters.reward_type"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Types</option>
              <option value="bonus">Bonus</option>
              <option value="cashback">Cashback</option>
              <option value="points">Points</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Status</option>
              <option value="claimed">Claimed</option>
              <option value="pending">Pending</option>
              <option value="expired">Expired</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">From Date</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">To Date</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
          </div>
        </div>
        <BaseButtons>
          <BaseButton
            color="info"
            label="Apply Filters"
          />
          <BaseButton
            color="warning"
            label="Reset"
            @click="filters = { search: '', reward_type: 'all', status: 'all', date_from: '', date_to: '' }"
          />
        </BaseButtons>
      </CardBox>

      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Client Name</th>
              <th>Reward Type</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Date Awarded</th>
              <th>Expiry Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="reward in rewards" :key="reward.id">
              <td data-label="ID">{{ reward.id }}</td>
              <td data-label="Client Name">{{ reward.client_name }}</td>
              <td data-label="Reward Type">{{ reward.reward_type }}</td>
              <td data-label="Amount">{{ reward.amount.toFixed(2) }}</td>
              <td data-label="Status">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-green-100 text-green-800': reward.status === 'Claimed',
                    'bg-yellow-100 text-yellow-800': reward.status === 'Pending',
                    'bg-red-100 text-red-800': reward.status === 'Expired'
                  }"
                >
                  {{ reward.status }}
                </span>
              </td>
              <td data-label="Date Awarded">{{ reward.date_awarded }}</td>
              <td data-label="Expiry Date">{{ reward.expiry_date }}</td>
              <td class="before:hidden lg:w-1">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    label="Details"
                  />
                  <BaseButton
                    color="success"
                    label="Export"
                  />
                </BaseButtons>
              </td>
            </tr>
          </tbody>
        </table>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template> 