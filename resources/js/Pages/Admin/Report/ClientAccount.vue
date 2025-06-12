<script setup>
import { Head } from "@inertiajs/vue3"
import {
  mdiAccountMultiple,
  mdiAccountStar,
  mdiAccountGroup,
  mdiCash,
  mdiCurrencyUsd,
  mdiTrendingUp,
  mdiAlertBoxOutline,
  mdiMagnify,
} from "@mdi/js"
import LayoutAuthenticated from "@/Layouts/Admin/LayoutAuthenticated.vue"
import SectionMain from "@/Components/SectionMain.vue"
import SectionTitleLineWithButton from "@/Components/SectionTitleLineWithButton.vue"
import CardBox from "@/Components/CardBox.vue"
import CardBoxWidget from "@/Components/CardBoxWidget.vue"
import CardBoxModal from "@/Components/CardBoxModal.vue"
import NotificationBar from "@/Components/NotificationBar.vue"
import BaseButton from "@/Components/BaseButton.vue"
import BaseButtons from "@/Components/BaseButtons.vue"
import { ref } from 'vue'

const stats = ref({
  level1_clients: 25,
  total_accounts: 150,
  total_volume_lots: 2500.5,
  total_volume_usd: 2500000,
  total_profit: 125000
})

// Sample accounts data
const accounts = ref([
  {
    id: 1,
    partner_account_name: 'Partner A',
    my_partner_account: 'MP001',
    country: 'Thailand',
    client_id: 'CLI001',
    client_account: '12345678',
    account_type: 'Standard',
    signup_date: '2024-01-15',
    volume: 125.5,
    profit: 2500.75,
    last_trading_date: '2024-03-15',
    comment: 'Active trader'
  },
  {
    id: 2,
    partner_account_name: 'Partner B',
    my_partner_account: 'MP002',
    country: 'Singapore',
    client_id: 'CLI002',
    client_account: '12345679',
    account_type: 'Premium',
    signup_date: '2024-02-01',
    volume: 250.75,
    profit: 4750.50,
    last_trading_date: '2024-03-14',
    comment: 'High volume trader'
  },
  {
    id: 3,
    partner_account_name: 'Partner C',
    my_partner_account: 'MP003',
    country: 'Malaysia',
    client_id: 'CLI003',
    client_account: '12345680',
    account_type: 'VIP',
    signup_date: '2024-02-15',
    volume: 75.25,
    profit: 1200.25,
    last_trading_date: '2024-03-10',
    comment: 'New account'
  }
])

// Search modal state
const isModalActive = ref(false)

// Update filters object
const filters = ref({
  partner_account_name: '',
  my_partner_account: '',
  country: '',
  client_id: '',
  client_account: '',
  account_type: 'all',
  signup_date_from: '',
  signup_date_to: '',
  volume_min: '',
  volume_max: '',
  profit_min: '',
  profit_max: '',
  last_trading_date_from: '',
  last_trading_date_to: '',
  comment: ''
})

// Reset filters
const resetFilters = () => {
  filters.value = {
    partner_account_name: '',
    my_partner_account: '',
    country: '',
    client_id: '',
    client_account: '',
    account_type: 'all',
    signup_date_from: '',
    signup_date_to: '',
    volume_min: '',
    volume_max: '',
    profit_min: '',
    profit_max: '',
    last_trading_date_from: '',
    last_trading_date_to: '',
    comment: ''
  }
}

// Apply search
const applySearch = () => {
  // Here you would implement the actual search logic
  isModalActive.value = false
}
</script>

<template>
  <LayoutAuthenticated>
    <Head title="Client Account Reports" />
    <SectionMain>
      <SectionTitleLineWithButton
        :icon="mdiAccountGroup"
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

      <!-- Statistics Widgets -->
      <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-6">
        <CardBoxWidget
          :icon="mdiAccountStar"
          :number="stats.level1_clients"
          label="Level 1 Clients"
          color="text-purple-500"
        />
        <CardBoxWidget
          :icon="mdiAccountMultiple"
          :number="stats.total_accounts"
          label="Clients' accounts"
          color="text-blue-500"
        />
        <CardBoxWidget
          :icon="mdiCash"
          :number="stats.total_volume_lots"
          label="Volume (lots)"
          :suffix="'lots'"
          color="text-green-500"
        />
        <CardBoxWidget
          :icon="mdiCurrencyUsd"
          :number="stats.total_volume_usd"
          label="Volume (Min. USD)"
          prefix="$"
          color="text-yellow-500"
        />
        <CardBoxWidget
          :icon="mdiTrendingUp"
          :number="stats.total_profit"
          label="Profit (USD)"
          prefix="$"
          color="text-emerald-500"
        />
      </div>
      <div class="flex justify-between items-center mb-6">
          <BaseButton
            :icon="mdiMagnify"
            label="ค้นหา"
            color="info"
            @click="isModalActive = true"
          />
        </div>
      <!-- Table Section -->
      <CardBox class="mb-6" has-table>
       

        <table>
          <thead>
            <tr>
              <th>Partner account name</th>
              <th>My partner account</th>
              <th>Country</th>
              <th>Client ID</th>
              <th>Client account</th>
              <th>Account type</th>
              <th>Sign-up date</th>
              <th>Volume</th>
              <th>Profit</th>
              <th>Last trading date</th>
              <th>Comment</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="account in accounts" :key="account.id">
              <td data-label="Partner account name">{{ account.partner_account_name }}</td>
              <td data-label="My partner account">{{ account.my_partner_account }}</td>
              <td data-label="Country">{{ account.country }}</td>
              <td data-label="Client ID">{{ account.client_id }}</td>
              <td data-label="Client account">{{ account.client_account }}</td>
              <td data-label="Account type">
                <span
                  class="px-2 py-1 rounded-full text-xs"
                  :class="{
                    'bg-green-100 text-green-800': account.account_type === 'VIP',
                    'bg-blue-100 text-blue-800': account.account_type === 'Premium',
                    'bg-gray-100 text-gray-800': account.account_type === 'Standard'
                  }"
                >
                  {{ account.account_type }}
                </span>
              </td>
              <td data-label="Sign-up date">{{ account.signup_date }}</td>
              <td data-label="Volume">{{ account.volume.toFixed(2) }}</td>
              <td data-label="Profit" :class="account.profit >= 0 ? 'text-green-600' : 'text-red-600'">
                {{ account.profit >= 0 ? '+' : '' }}{{ account.profit.toFixed(2) }}
              </td>
              <td data-label="Last trading date">{{ account.last_trading_date }}</td>
              <td data-label="Comment">{{ account.comment }}</td>
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

      <!-- Search Modal -->
      <CardBoxModal
        v-model="isModalActive"
        title="ค้นหาข้อมูล"
        button="info"
        has-cancel
        @confirm="applySearch"
        @cancel="isModalActive = false"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Partner Information -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Partner account name</label>
              <input
                v-model="filters.partner_account_name"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">My partner account</label>
              <input
                v-model="filters.my_partner_account"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Country</label>
              <input
                v-model="filters.country"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
          </div>

          <!-- Client Information -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client ID</label>
              <input
                v-model="filters.client_id"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Client account</label>
              <input
                v-model="filters.client_account"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Account type</label>
              <select
                v-model="filters.account_type"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
              >
                <option value="all">ทั้งหมด</option>
                <option value="Standard">Standard</option>
                <option value="Premium">Premium</option>
                <option value="VIP">VIP</option>
              </select>
            </div>
          </div>

          <!-- Dates -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sign-up date range</label>
              <div class="grid grid-cols-2 gap-2">
                <input
                  v-model="filters.signup_date_from"
                  type="date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="From"
                >
                <input
                  v-model="filters.signup_date_to"
                  type="date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="To"
                >
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Last trading date range</label>
              <div class="grid grid-cols-2 gap-2">
                <input
                  v-model="filters.last_trading_date_from"
                  type="date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="From"
                >
                <input
                  v-model="filters.last_trading_date_to"
                  type="date"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="To"
                >
              </div>
            </div>
          </div>

          <!-- Numbers -->
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Volume range</label>
              <div class="grid grid-cols-2 gap-2">
                <input
                  v-model="filters.volume_min"
                  type="number"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="Min"
                >
                <input
                  v-model="filters.volume_max"
                  type="number"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="Max"
                >
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Profit range</label>
              <div class="grid grid-cols-2 gap-2">
                <input
                  v-model="filters.profit_min"
                  type="number"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="Min"
                >
                <input
                  v-model="filters.profit_max"
                  type="number"
                  step="0.01"
                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                  placeholder="Max"
                >
              </div>
            </div>
          </div>

          <!-- Comment -->
          <div class="md:col-span-2">
            <div>
              <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Comment</label>
              <input
                v-model="filters.comment"
                type="text"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-slate-800"
                placeholder="Search in comments..."
              >
            </div>
          </div>

          <!-- Buttons -->
          <div class="md:col-span-2 flex justify-end space-x-2">
            <BaseButton
              color="warning"
              label="รีเซ็ต"
              @click="resetFilters"
            />
          </div>
        </div>
      </CardBoxModal>
    </SectionMain>
  </LayoutAuthenticated>
</template> 