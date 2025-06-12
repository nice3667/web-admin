<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiClockOutline,
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

const pendingTransactions = ref([
  {
    id: 1,
    client_name: 'John Doe',
    transaction_id: 'TRX001',
    type: 'Withdrawal',
    amount: 1000.00,
    currency: 'USD',
    payment_method: 'Bank Transfer',
    requested_date: '2024-03-20',
    due_date: '2024-03-22'
  },
  // Add more sample data as needed
])

const filters = ref({
  search: '',
  payment_method: 'all',
  date_from: '',
  date_to: '',
  amount_min: '',
  amount_max: ''
})

const stats = ref({
  total_pending: 45,
  total_amount: 75000.00,
  due_today: 15,
  overdue: 5
})
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Transactions Pending Payment" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiClockOutline"
        title="Transactions Pending Payment"
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
            <span class="text-gray-500 dark:text-gray-400">Total Pending</span>
            <span class="text-2xl font-bold">{{ stats.total_pending }}</span>
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
            <span class="text-gray-500 dark:text-gray-400">Due Today</span>
            <span class="text-2xl font-bold text-yellow-600">{{ stats.due_today }}</span>
          </div>
        </CardBox>
        <CardBox>
          <div class="flex flex-col">
            <span class="text-gray-500 dark:text-gray-400">Overdue</span>
            <span class="text-2xl font-bold text-red-600">{{ stats.overdue }}</span>
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
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Payment Method</label>
            <select
              v-model="filters.payment_method"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
            >
              <option value="all">All Methods</option>
              <option value="bank">Bank Transfer</option>
              <option value="crypto">Cryptocurrency</option>
              <option value="card">Card Payment</option>
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
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Due Date Range</label>
            <div class="flex space-x-2">
              <input
                v-model="filters.date_from"
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
              <input
                v-model="filters.date_to"
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
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
            @click="filters = { search: '', payment_method: 'all', date_from: '', date_to: '', amount_min: '', amount_max: '' }"
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
              <th>Amount</th>
              <th>Currency</th>
              <th>Payment Method</th>
              <th>Requested Date</th>
              <th>Due Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="transaction in pendingTransactions" :key="transaction.id">
              <td data-label="Transaction ID">{{ transaction.transaction_id }}</td>
              <td data-label="Client Name">{{ transaction.client_name }}</td>
              <td data-label="Amount">{{ transaction.amount.toFixed(2) }}</td>
              <td data-label="Currency">{{ transaction.currency }}</td>
              <td data-label="Payment Method">{{ transaction.payment_method }}</td>
              <td data-label="Requested Date">{{ transaction.requested_date }}</td>
              <td data-label="Due Date">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-yellow-100 text-yellow-800': new Date(transaction.due_date) === new Date(),
                    'bg-red-100 text-red-800': new Date(transaction.due_date) < new Date(),
                    'bg-green-100 text-green-800': new Date(transaction.due_date) > new Date()
                  }"
                >
                  {{ transaction.due_date }}
                </span>
              </td>
              <td class="before:hidden lg:w-1">
                <BaseButtons type="justify-start lg:justify-end" no-wrap>
                  <BaseButton
                    color="info"
                    label="Details"
                  />
                  <BaseButton
                    color="success"
                    label="Process"
                  />
                  <BaseButton
                    color="danger"
                    label="Cancel"
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