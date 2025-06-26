<template>
  <LayoutAuthenticated>
    <Head title="ค้นหาข้อมูลลูกค้า" />
    <SectionMain>
      <SectionTitleLineWithButton title="ค้นหาข้อมูลลูกค้า" main>
        <template #button>
          <BaseButton
            color="success"
            :icon="mdiReload"
            label="รีเฟรช"
            :disabled="loading"
            @click="refreshData"
          />
        </template>
      </SectionTitleLineWithButton>

      <NotificationBar v-if="error" color="danger" :icon="mdiAlertBoxOutline">
        {{ error }}
      </NotificationBar>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4 xl:grid-cols-4">
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">ลูกค้าทั้งหมด</span>
              <span class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ stats.total_customers }}</span>
              <span class="text-xs text-gray-500 mt-1">ราย</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-green-500 to-green-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">ลูกค้าที่ใช้งาน</span>
              <span class="text-3xl font-bold text-green-600 dark:text-green-400">{{ stats.active_customers }}</span>
              <span class="text-xs text-gray-500 mt-1">ราย</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-yellow-500 to-yellow-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Volume (Lots)</span>
              <span class="text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ formatNumber(stats.total_volume_lots) }}</span>
              <span class="text-xs text-gray-500 mt-1">ยอดรวม</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-yellow-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
        <CardBox class="relative overflow-hidden transform hover:scale-105 transition-all duration-300 shadow-xl">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-600 opacity-10"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex flex-col">
              <span class="text-gray-600 dark:text-gray-400 text-sm font-medium mb-1">Reward (USD)</span>
              <span class="text-3xl font-bold text-purple-600 dark:text-purple-400">${{ formatNumber(stats.total_reward_usd) }}</span>
              <span class="text-xs text-gray-500 mt-1">กำไรรวม</span>
            </div>
            <div class="flex-shrink-0">
              <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                </svg>
              </div>
            </div>
          </div>
        </CardBox>
      </div>

      <!-- Search and Filter Section -->
      <CardBox class="mb-8 shadow-xl border-0 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700 transform hover:shadow-2xl transition-all duration-300">
        <div class="flex items-center justify-between mb-6">
          <div class="flex items-center space-x-3">
            <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">ค้นหาและกรองข้อมูล</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">กรองข้อมูลลูกค้าตามเงื่อนไขที่ต้องการ</p>
            </div>
          </div>
          <div class="flex items-center space-x-3">
            <span class="px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-full text-sm font-semibold shadow-lg">
              แสดง {{ customers.length }} รายการ
            </span>
          </div>
        </div>
        <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
              </svg>
              ค้นหา Client UID
            </label>
            <div class="relative">
              <input
                v-model="filters.search"
                type="text"
                placeholder="กรอก Client UID..."
                class="w-full pl-12 pr-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
                @keyup.enter="searchCustomers"
              />
              <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
              </div>
            </div>
          </div>
          <div class="space-y-3">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300">
              <svg class="inline w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
              สถานะ
            </label>
            <select
              v-model="filters.client_status"
              class="w-full px-4 py-4 border-2 border-gray-200 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-4 focus:ring-indigo-200 focus:ring-opacity-50 dark:bg-gray-700 dark:border-gray-600 dark:text-white transition-all duration-300 hover:border-indigo-300"
              @change="searchCustomers"
            >
              <option value="">ทุกสถานะ</option>
              <option value="ACTIVE">ACTIVE</option>
              <option value="INACTIVE">INACTIVE</option>
            </select>
          </div>
          <div class="flex items-end">
            <BaseButton
              color="gray"
              label="รีเซ็ต"
              :icon="mdiAlertBoxOutline"
              @click="clearFilters"
              class="w-full py-4 rounded-xl font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
            />
          </div>
        </div>
      </CardBox>

      <!-- Customers Table -->
      <CardBox class="shadow-2xl border-0 overflow-hidden transform hover:shadow-3xl transition-all duration-300" has-table>
        <div class="flex items-center justify-between mb-6 p-8 bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
          <div class="flex items-center space-x-4">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
            </div>
            <div>
              <h3 class="text-2xl font-bold text-gray-900 dark:text-white">รายการลูกค้า</h3>
              <p class="text-sm text-gray-600 dark:text-gray-400">ข้อมูลลูกค้าทั้งหมด {{ customers.length }} รายการ</p>
            </div>
          </div>
        </div>
        <div v-if="!loading && customers.length === 0" class="p-16 text-center">
          <div class="mx-auto w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 dark:from-gray-700 dark:to-gray-600 rounded-full flex items-center justify-center mb-8 shadow-lg">
            <svg class="w-16 h-16 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </div>
          <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">ไม่พบข้อมูล</h3>
          <p class="text-gray-600 dark:text-gray-400 mb-6 text-lg">ลองปรับเปลี่ยนเงื่อนไขการค้นหา</p>
          <div class="space-y-2 text-sm text-gray-500">
            <p>จำนวนข้อมูลทั้งหมด: 0 รายการ</p>
            <p>ข้อมูลที่กรองแล้ว: 0 รายการ</p>
          </div>
        </div>
        <div v-else class="overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
              <thead class="bg-gradient-to-r from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-700">
                <tr>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">Client UID</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">สถานะ</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">Reward (USD)</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                      </div>
                      <span class="text-sm font-bold">เจ้าของ</span>
                    </div>
                  </th>
                  <th class="px-8 py-6 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    <div class="flex items-center space-x-3">
                      <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      </div>
                      <span class="text-sm font-bold">การดำเนินการ</span>
                    </div>
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-for="customer in customers" :key="customer.client_uid"
                    class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 dark:hover:from-gray-700 dark:hover:to-gray-600 transition-all duration-300 transform hover:scale-[1.01]">
                  <td class="px-8 py-8 whitespace-nowrap">
                    <div>
                      <div class="text-lg font-bold text-gray-900 dark:text-white">
                        {{ customer.client_uid }}
                      </div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">
                        Account: {{ customer.client_id }}
                      </div>
                    </div>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap">
                    <span :class="[
                      'inline-flex items-center px-4 py-2 rounded-full text-xs font-semibold shadow-lg',
                      customer.client_status === 'ACTIVE' ? 'bg-gradient-to-r from-green-100 to-green-200 text-green-800 dark:from-green-900 dark:to-green-800 dark:text-green-200' :
                      customer.client_status === 'INACTIVE' ? 'bg-gradient-to-r from-red-100 to-red-200 text-red-800 dark:from-red-900 dark:to-red-800 dark:text-red-200' :
                      'bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 dark:from-gray-900 dark:to-gray-800 dark:text-gray-200'
                    ]">
                      <svg v-if="customer.client_status === 'ACTIVE'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                      </svg>
                      <svg v-else-if="customer.client_status === 'INACTIVE'" class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                      </svg>
                      {{ customer.client_status === 'ACTIVE' ? 'ใช้งาน' : 'ไม่ใช้งาน' }}
                    </span>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap">
                    <div class="text-lg font-bold text-green-600 dark:text-green-400">
                      ${{ formatNumber(customer.total_reward_usd) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                      Volume: {{ formatNumber(customer.total_volume_lots) }} lots
                    </div>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap">
                    <div v-if="customer.owner" class="flex items-center space-x-2">
                      <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 10a4 4 0 100-8 4 4 0 000 8zm0 2a6 6 0 00-5.197 2.978A2 2 0 006 18h8a2 2 0 001.197-3.022A6 6 0 0010 12z" /></svg>
                      <span class="font-medium text-blue-900 dark:text-blue-100">{{ customer.owner.name }}</span>
                    </div>
                    <div v-else class="flex items-center">
                      <span class="text-gray-400 dark:text-gray-500 italic">ยังไม่ได้กำหนด</span>
                    </div>
                  </td>
                  <td class="px-8 py-8 whitespace-nowrap text-sm font-medium">
                    <div class="flex space-x-2">
                      <BaseButton
                        color="info"
                        :icon="mdiAccountGroup"
                        label="กำหนดเจ้าของ"
                        @click="showAssignOwnerModal(customer)"
                        class="text-xs"
                      />
                      <BaseButton
                        color="success"
                        :icon="mdiChartLine"
                        label="ดูรายละเอียด"
                        @click="showCustomerDetails(customer)"
                        class="text-xs"
                      />
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </CardBox>

      <!-- Loading State -->
      <CardBox v-if="loading" class="text-center py-12 bg-gradient-to-br from-white to-blue-50 dark:from-gray-900 dark:to-blue-950">
        <svg class="animate-spin mx-auto h-12 w-12 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <p class="mt-2 text-sm text-blue-500 dark:text-blue-200">กำลังโหลดข้อมูล...</p>
      </CardBox>
    </SectionMain>
  </LayoutAuthenticated>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Head } from '@inertiajs/vue3'
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue'
import SectionMain from '@/Components/SectionMain.vue'
import SectionTitleLineWithButton from '@/Components/SectionTitleLineWithButton.vue'
import CardBox from '@/Components/CardBox.vue'
import NotificationBar from '@/Components/NotificationBar.vue'
import BaseButton from '@/Components/BaseButton.vue'
import { mdiReload, mdiAlertBoxOutline, mdiAccountGroup, mdiChartLine } from '@mdi/js'
import axios from 'axios'

const props = defineProps({
  customers: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_customers: 0,
      total_volume_lots: 0,
      total_volume_usd: 0,
      total_reward_usd: 0,
      total_rebate_usd: 0,
      active_customers: 0,
      inactive_customers: 0
    })
  },
  users: {
    type: Array,
    default: () => []
  },
  filters: {
    type: Object,
    default: () => ({})
  },
  error: {
    type: String,
    default: null
  }
})

// Reactive data
const loading = ref(false)
const error = ref(props.error)
const customers = ref(props.customers)
const stats = ref(props.stats)
const users = ref(props.users)
const filters = ref({ ...props.filters })

// Methods
const formatNumber = (value) => {
  if (!value) return '0'
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

const searchCustomers = async () => {
  loading.value = true
  error.value = null

  try {
    const response = await axios.get('/admin/customers', {
      params: filters.value
    })

    customers.value = response.data.data.customers
    stats.value = response.data.data.stats
    users.value = response.data.data.users
  } catch (err) {
    error.value = err.response?.data?.error || 'เกิดข้อผิดพลาดในการค้นหาข้อมูล'
  } finally {
    loading.value = false
  }
}

const refreshData = () => {
  searchCustomers()
}

const clearFilters = () => {
  filters.value = {}
  searchCustomers()
}

const showAssignOwnerModal = (customer) => {
  alert('ฟีเจอร์กำหนดเจ้าของจะเพิ่มในภายหลัง')
}

const showCustomerDetails = (customer) => {
  alert('ฟีเจอร์ดูรายละเอียดจะเพิ่มในภายหลัง')
}

// Initialize data on mount
onMounted(() => {
  if (customers.value.length === 0) {
    searchCustomers()
  }
})
</script>

