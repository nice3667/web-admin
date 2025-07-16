<template>
  <TopNavBar />
  <div class="min-h-screen py-12 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
    <div class=" mx-auto sm:px-6 lg:px-8">
      <!-- Page Title with Animation -->
      <div class="mb-8 text-center animate-fade-in">
        <h1 class="text-4xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
          XM Report Dashboard
        </h1>
        <p class="mt-3 text-lg text-gray-600 dark:text-gray-400">
          View and analyze XM trading statistics and performance metrics
        </p>
      </div>

      <!-- XM Partner Label -->
      <div class="mb-2 text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in">
        XM Partner
      </div>
      <!-- Date Range Selector -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-2xl p-8 mb-8 border border-white/20 dark:border-slate-700/20 transform hover:scale-[1.02] transition-all duration-300">
        <div class="flex flex-wrap gap-6 items-end">
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 9h2M12 17h.01" />
                </svg>
                Trader ID
              </span>
            </label>
            <input
              type="text"
              v-model="searchTraderId"
              placeholder="Search Trader ID"
              class="w-full px-4 py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200"
            >
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6" />
                </svg>
                Account Type
              </span>
            </label>
            <select
              v-model="searchAccountType"
              class="w-full px-4 py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200"
            >
              <option value="">All</option>
              <option v-for="type in accountTypeOptions" :key="type" :value="type">{{ type }}</option>
            </select>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Platform
              </span>
            </label>
            <select
              v-model="searchPlatform"
              class="w-full px-4 py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200"
            >
              <option value="">All</option>
              <option v-for="platform in platformOptions" :key="platform" :value="platform">{{ platform }}</option>
            </select>
          </div>
          <div class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Sign Up Date
              </span>
            </label>
            <select v-model="signUpDateFilter" class="w-full px-4 py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200">
              <option value="all">ทั้งหมด</option>
              <option value="1m">เปิดบัญชีเมื่อ 1 เดือนที่แล้ว</option>
              <option value="2m">เปิดบัญชีเมื่อ 2 เดือนที่แล้ว</option>
              <option value="custom">เลือกวันที่เอง</option>
            </select>
          </div>
          <div v-if="signUpDateFilter === 'custom'" class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Start Date
              </span>
            </label>
            <input
              type="date"
              v-model="startDate"
              class="w-full px-4 py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200"
            >
          </div>
          <div v-if="signUpDateFilter === 'custom'" class="flex-1 min-w-[200px]">
            <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
              <span class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                End Date
              </span>
            </label>
            <input
              type="date"
              v-model="endDate"
              class="w-full px-4 py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200"
            >
          </div>
          <div class="flex-none flex gap-2 ml-auto">
            <button
              @click="resetFilters"
              class="px-8 py-3 rounded-xl bg-gradient-to-r from-gray-300 via-gray-200 to-gray-100 text-gray-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Lots Card -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Lots</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ formatNumber(rebateStats.lots) }}
              </p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Active Traders Card -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Active Traders</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ rebateStats.activeTraders }}
              </p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Total Rebate Card -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Total Rebate</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                ${{ formatNumber(rebateStats.lotRebate) }}
              </p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
        </div>

        <!-- Conversion Rate Card -->
        <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-2xl p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-semibold text-gray-600 dark:text-gray-400">Conversion Rate</p>
              <p class="mt-2 text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                {{ (rebateStats.conversion * 100).toFixed(2) }}%
              </p>
            </div>
            <div class="p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
              <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Traders Table -->
      <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-2xl border border-white/20 dark:border-slate-700/20">
        <div class="px-8 py-6 flex flex-col md:flex-row md:items-center md:justify-between gap-2">

          <div>
            <h3 class="text-2xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
              Traders List
            </h3>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
              Detailed information about all traders and their current status
            </p>
          </div>
          <div class="flex gap-2 mt-2 md:mt-0">
            <button
              @click="fetchData"
              class="group px-8 py-3 rounded-xl bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800"
            >
              <span class="flex items-center gap-2">
                <svg class="w-5 h-5 transform group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                Fetch Data
              </span>
            </button>

          </div>
        </div>

        <div class="border-t border-blue-100/20 dark:border-slate-700/20">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-blue-100/20 dark:divide-slate-700/20">
              <thead class="bg-blue-50/50 dark:bg-slate-700/50">
                <tr>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Trader ID</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Client ID</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Country</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Account Type</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Campaign</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Platform</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Sign Up Date</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Activation Date</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Archived</th>
                  <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white/50 dark:bg-slate-800/50 divide-y divide-blue-100/20 dark:divide-slate-700/20">
                <!-- Loading State -->
                <tr v-if="isLoading" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50">
                  <td colspan="10" class="px-6 py-12 text-center">
                    <div class="flex items-center justify-center">
                      <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      <span class="ml-3 text-sm font-medium text-gray-600 dark:text-gray-400">Loading data...</span>
                    </div>
                  </td>
                </tr>

                <!-- No Data State -->
                <tr v-else-if="!paginatedTraders.length" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50">
                  <td colspan="10" class="px-6 py-12 text-center text-sm text-gray-600 dark:text-gray-400">
                    No traders found for the selected date range
                  </td>
                </tr>

                <!-- Data Rows -->
                <tr v-else v-for="trader in paginatedTraders" :key="trader.traderId" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ trader.traderId }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ trader.clientId }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ trader.country || '-' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ trader.accountType }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ trader.campaign }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ trader.tradingPlatform }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ formatDate(trader.signUpDate) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ formatDate(trader.activationDate) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                      trader.archived
                        ? 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                        : 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                    ]">
                      {{ trader.archived ? 'Archived' : 'Active' }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span :class="[
                      'px-4 py-1.5 inline-flex items-center gap-1.5 text-xs font-semibold rounded-full',
                      trader.valid
                        ? 'bg-green-100/80 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                        : 'bg-red-100/80 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                    ]">
                      <span class="relative flex h-2 w-2">
                        <span :class="[
                          'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                          trader.valid ? 'bg-green-400' : 'bg-red-400'
                        ]"></span>
                        <span :class="[
                          'relative inline-flex rounded-full h-2 w-2',
                          trader.valid ? 'bg-green-500' : 'bg-red-500'
                        ]"></span>
                      </span>
                      {{ trader.valid ? 'Valid' : 'Invalid' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div class="px-6 py-4 bg-white/50 dark:bg-slate-800/50 border-t border-blue-100/20 dark:border-slate-700/20">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                <span>Showing</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ startIndex + 1 }}</span>
                <span>to</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ endIndex }}</span>
                <span>of</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{ totalItems }}</span>
                <span>entries</span>
              </div>

              <div class="flex items-center gap-2">
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                    border border-blue-100 dark:border-slate-600"
                >
                  Previous
                </button>
                <div class="flex items-center gap-1">
                  <button
                    v-for="page in displayedPages"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                      'px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200',
                      currentPage === page
                        ? 'bg-blue-500 text-white hover:bg-blue-600'
                        : 'bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600'
                    ]"
                  >
                    {{ page }}
                  </button>
                </div>
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="px-4 py-2 text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                    bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                    border border-blue-100 dark:border-slate-600"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <BottomNavBar :menu="menuItems" />
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'
import TopNavBar from '@/Components/TopNavBar.vue';
import BottomNavBar from '@/Components/BottomNavBar.vue';

const startDate = ref(new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0])
const endDate = ref(new Date().toISOString().split('T')[0])
const traders = ref([])
const currentPage = ref(1)
const itemsPerPage = 10
const isLoading = ref(false)
const error = ref(null)
const rebateStats = ref({
  lots: 0,
  activeTraders: 0,
  lotRebate: 0,
  conversion: 0
})
const searchTraderId = ref("");
const searchAccountType = ref("");
const searchPlatform = ref("");
const signUpDateFilter = ref('all');

// Setup axios defaults
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

// Handle 401 responses globally
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      // Redirect to login page or refresh the page
      window.location.reload();
    }
    return Promise.reject(error);
  }
);

// Computed properties for pagination
const totalItems = computed(() => traders.value.length)
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage))
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage)
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, totalItems.value))
const filteredTraders = computed(() => {
  const now = new Date();
  let start = null;
  let end = null;
  if (signUpDateFilter.value === '1m') {
    // Previous calendar month
    const year = now.getMonth() === 0 ? now.getFullYear() - 1 : now.getFullYear();
    const month = now.getMonth() === 0 ? 11 : now.getMonth() - 1;
    start = new Date(year, month, 1);
    end = new Date(year, month + 1, 1);
  } else if (signUpDateFilter.value === '2m') {
    // Two months ago (the month before previous)
    let year = now.getFullYear();
    let month = now.getMonth() - 2;
    if (month < 0) {
      year -= 1;
      month += 12;
    }
    start = new Date(year, month, 1);
    end = new Date(year, month + 1, 1);
  } else if (signUpDateFilter.value === 'custom') {
    start = startDate.value ? new Date(startDate.value) : null;
    end = endDate.value ? new Date(endDate.value) : null;
    if (end) {
      end.setDate(end.getDate() + 1); // include the end date
    }
  }
  return traders.value.filter(trader => {
    const traderIdMatch = !searchTraderId.value || String(trader.traderId).toLowerCase().includes(searchTraderId.value.toLowerCase());
    const accountTypeMatch = !searchAccountType.value || (trader.accountType && trader.accountType.toLowerCase().includes(searchAccountType.value.toLowerCase()));
    const platformMatch = !searchPlatform.value || (trader.tradingPlatform && trader.tradingPlatform.toLowerCase().includes(searchPlatform.value.toLowerCase()));
    let signUpDateMatch = true;
    if (start && end && trader.signUpDate) {
      const signUp = new Date(trader.signUpDate);
      signUpDateMatch = signUp >= start && signUp < end;
    }
    return traderIdMatch && accountTypeMatch && platformMatch && signUpDateMatch;
  });
});

const paginatedTraders = computed(() => {
  return filteredTraders.value.slice(startIndex.value, endIndex.value)
})

// Calculate displayed page numbers
const displayedPages = computed(() => {
  const pages = []
  const maxDisplayedPages = 5
  let start = Math.max(1, currentPage.value - Math.floor(maxDisplayedPages / 2))
  let end = Math.min(totalPages.value, start + maxDisplayedPages - 1)

  if (end - start + 1 < maxDisplayedPages) {
    start = Math.max(1, end - maxDisplayedPages + 1)
  }

  for (let i = start; i <= end; i++) {
    pages.push(i)
  }
  return pages
})

// Pagination methods
const goToPage = (page) => {
  currentPage.value = page
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const fetchData = async () => {
  isLoading.value = true
  error.value = null

  try {
    console.log('Fetching data with params:', {
      startTime: startDate.value,
      endTime: endDate.value
    });

    const [tradersResponse, rebateResponse] = await Promise.all([
      axios.get('/admin/xm/traders', {
        params: {
          startTime: startDate.value,
          endTime: endDate.value
        }
      }),
      axios.get('/admin/xm/rebate', {
        params: {
          startTime: startDate.value,
          endTime: endDate.value
        }
      })
    ])

    console.log('Traders Response:', tradersResponse.data);
    console.log('Rebate Response:', rebateResponse.data);

    if (tradersResponse.data.error) {
      throw new Error(tradersResponse.data.message || 'Failed to fetch traders data')
    }

    if (rebateResponse.data.error) {
      throw new Error(rebateResponse.data.message || 'Failed to fetch rebate data')
    }

    traders.value = Array.isArray(tradersResponse.data) ? tradersResponse.data : [];
    console.log('Processed traders data:', traders.value);

    rebateStats.value = {
      lots: rebateResponse.data?.lots || 0,
      activeTraders: rebateResponse.data?.activeTraders || 0,
      lotRebate: rebateResponse.data?.lotRebate || 0,
      conversion: rebateResponse.data?.conversion || 0
    };
    console.log('Processed rebate stats:', rebateStats.value);

    currentPage.value = 1 // Reset to first page when new data is fetched
  } catch (e) {
    console.error('Error details:', {
      message: e.message,
      response: e.response?.data,
      status: e.response?.status,
      headers: e.response?.headers
    });

    error.value = e.message || 'An error occurred while fetching data'
    traders.value = []
    rebateStats.value = {
      lots: 0,
      activeTraders: 0,
      lotRebate: 0,
      conversion: 0
    }
  } finally {
    isLoading.value = false
  }
}

const formatNumber = (number) => {
  return new Intl.NumberFormat('en-US').format(number)
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const accountTypeOptions = computed(() => {
  const set = new Set();
  traders.value.forEach(t => {
    if (t.accountType) set.add(t.accountType);
  });
  return Array.from(set).sort();
});

const platformOptions = computed(() => {
  const set = new Set();
  traders.value.forEach(t => {
    if (t.tradingPlatform) set.add(t.tradingPlatform);
  });
  return Array.from(set).sort();
});

function resetFilters() {
  searchTraderId.value = "";
  searchAccountType.value = "";
  searchPlatform.value = "";
  signUpDateFilter.value = "all";
  startDate.value = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0];
  endDate.value = new Date().toISOString().split('T')[0];
}

onMounted(() => {
  fetchData()
})

const menuItems = [
  {
    label: "Dashboard",
    to: "/admin",
    icon: 'mdiViewDashboard'
  },
  {
    label: "Customers",
    to: "/admin/customers",
    icon: 'mdiAccountGroup'
  },
  {
    label: "XM",
    to: "/admin/xm",
    icon: 'mdiChartBar'
  },
  {
    label: "Exness 1",
    icon: 'mdiChartBar',
    dropdown: true,
    items: [
      {
        label: "Clients",
        to: "/admin/reports/clients",
        icon: 'mdiAccountGroup'
      },
      {
        label: "Client Account",
        to: "/admin/reports/client-account",
        icon: 'mdiAccountDetails'
      }
    ]
  },
  {
    label: "Exness 2",
    icon: 'mdiChartBar',
    dropdown: true,
    items: [
      {
        label: "Clients",
        to: "/admin/reports1/clients1",
        icon: 'mdiAccountGroup'
      },
      {
        label: "Client Account",
        to: "/admin/reports1/client-account1",
        icon: 'mdiAccountDetails'
      }
    ]
  },
  {
    label: "Exness 3",
    icon: 'mdiChartBar',
    dropdown: true,
    items: [
      {
        label: "Clients",
        to: "/admin/reports2/clients2",
        icon: 'mdiAccountGroup'
      },
      {
        label: "Client Account",
        to: "/admin/reports2/client-account2",
        icon: 'mdiAccountDetails'
      }
    ]
  }
];
</script>

<style scoped>
/* Glass morphism effect */
.backdrop-blur-lg {
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Enhanced shadows */
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

/* Hover effects */
.hover\:scale-105:hover {
  transform: scale(1.05);
}

/* Input focus styles */
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}

/* Dark mode adjustments */
.dark input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}

/* Animation keyframes */
@keyframes fade-in {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fade-in {
  animation: fade-in 0.6s ease-out forwards;
}

/* Gradient background animation */
@keyframes gradient {
  0% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
  100% {
    background-position: 0% 50%;
  }
}

.bg-gradient-animate {
  background-size: 200% 200%;
  animation: gradient 15s ease infinite;
}
</style>
