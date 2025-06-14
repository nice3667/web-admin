<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiCashMultiple,
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

const transactions = ref([
  {
    id: 1,
    client_name: 'John Doe',
    transaction_id: 'TRX001',
    type: 'Deposit',
    amount: 1000.00,
    currency: 'USD',
    status: 'Completed',
    date: '2024-03-20'
  },
  // Add more sample data as needed
])

const filters = ref({
  search: '',
  type: 'all',
  status: 'all',
  date_from: '',
  date_to: '',
  amount_min: '',
  amount_max: ''
})

const stats = ref({
  total_transactions: 150,
  total_amount: 25000.00,
  completed_transactions: 120,
  pending_transactions: 30
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Client Transactions" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiCashMultiple"
        title="Client Transactions"
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

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Total Transactions</span>
            <span class="text-2xl font-bold">{{ stats.total_transactions }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Total Amount</span>
            <span class="text-2xl font-bold">${{ stats.total_amount.toFixed(2) }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Completed</span>
            <span class="text-2xl font-bold text-green-600">{{ stats.completed_transactions }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Pending</span>
            <span class="text-2xl font-bold text-yellow-600">{{ stats.pending_transactions }}</span>
          </div>
        </CardBox>
      </div>

      <CardBox class="mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              placeholder="Search transactions..."
            >
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Type</label>
            <select
              v-model="filters.type"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Types</option>
              <option value="deposit">Deposit</option>
              <option value="withdrawal">Withdrawal</option>
              <option value="transfer">Transfer</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Status</option>
              <option value="completed">Completed</option>
              <option value="pending">Pending</option>
              <option value="failed">Failed</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Amount Range</label>
            <div class="flex space-x-2">
              <input
                v-model="filters.amount_min"
                type="number"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                placeholder="Min"
              >
              <input
                v-model="filters.amount_max"
                type="number"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                placeholder="Max"
              >
            </div>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
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
            @click="filters = { search: '', type: 'all', status: 'all', date_from: '', date_to: '', amount_min: '', amount_max: '' }"
          />
          <BaseButton
            color="success"
            label="Export"
          />
        </BaseButtons>
      </CardBox>

      <CardBox class="mb-6" has-table>
        <table>
          <thead>
            <tr>
              <th>Transaction ID</th>
              <th>Client Name</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Currency</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in transactions" :key="transaction.id">
              <td data-label="Transaction ID">{{ transaction.transaction_id }}</td>
              <td data-label="Client Name">{{ transaction.client_name }}</td>
              <td data-label="Type">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-green-100 text-green-800': transaction.type === 'Deposit',
                    'bg-red-100 text-red-800': transaction.type === 'Withdrawal',
                    'bg-blue-100 text-blue-800': transaction.type === 'Transfer'
                  }"
                >
                  {{ transaction.type }}
                </span>
              </td>
              <td data-label="Amount">{{ transaction.amount.toFixed(2) }}</td>
              <td data-label="Currency">{{ transaction.currency }}</td>
              <td data-label="Status">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-green-100 text-green-800': transaction.status === 'Completed',
                    'bg-yellow-100 text-yellow-800': transaction.status === 'Pending',
                    'bg-red-100 text-red-800': transaction.status === 'Failed'
                  }"
                >
                  {{ transaction.status }}
                </span>
              </td>
              <td data-label="Date">{{ transaction.date }}</td>
              <td class="before:hidden lg:w-1">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    label="Details"
                  />
                  <BaseButton
                    color="success"
                    label="Receipt"
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