<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiAccountCash,
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

const accounts = ref([
  {
    id: 1,
    client_name: 'John Doe',
    account_number: 'ACC001',
    balance: 5000.00,
    currency: 'USD',
    status: 'Active',
    last_transaction: '2024-03-20'
  },
  // Add more sample data as needed
])

const filters = ref({
  search: '',
  status: 'all',
  balance_min: '',
  balance_max: ''
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Client Account Reports" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountCash"
        title="Client Account Reports"
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
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="Search by name or account..."
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="suspended">Suspended</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Min Balance</label>
            <input
              v-model="filters.balance_min"
              type="number"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="0.00"
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Max Balance</label>
            <input
              v-model="filters.balance_max"
              type="number"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="0.00"
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
            @click="filters = { search: '', status: 'all', balance_min: '', balance_max: '' }"
          />
        </BaseButtons>
      </CardBox>

      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th>Account #</th>
              <th>Client Name</th>
              <th>Balance</th>
              <th>Currency</th>
              <th>Status</th>
              <th>Last Transaction</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="account in accounts" :key="account.id">
              <td data-label="Account #">{{ account.account_number }}</td>
              <td data-label="Client Name">{{ account.client_name }}</td>
              <td data-label="Balance">{{ account.balance.toFixed(2) }}</td>
              <td data-label="Currency">{{ account.currency }}</td>
              <td data-label="Status">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-green-100 text-green-800': account.status === 'Active',
                    'bg-red-100 text-red-800': account.status === 'Inactive',
                    'bg-yellow-100 text-yellow-800': account.status === 'Suspended'
                  }"
                >
                  {{ account.status }}
                </span>
              </td>
              <td data-label="Last Transaction">{{ account.last_transaction }}</td>
              <td class="before:hidden lg:w-1">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    label="Details"
                  />
                  <BaseButton
                    color="success"
                    label="History"
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