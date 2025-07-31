<template>
  <TopNavBar />
  <Head :title="`ค้นหาข้อมูลลูกค้า${searchAccount.trim() || selectedOwner !== 'all' ? ' (Filtered)' : ''}`" />
  <div
    class="min-h-screen py-4 sm:py-8 lg:py-12 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900"
  >
    <div class="mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Title with Animation -->
      <div class="mb-6 lg:mb-8 text-center animate-fade-in">
        <h1
          class="text-2xl sm:text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
        >
          Customer Dashboard
          <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-sm font-normal text-blue-600 dark:text-blue-400">
            (Filtered)
          </span>
        </h1>
        <p
          class="mt-2 lg:mt-3 text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400"
        >
          View and analyze customer statistics and performance metrics
          <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
            (แสดงเฉพาะผลลัพธ์ที่กรอง)
          </span>
        </p>
      </div>

      <!-- Customer Partner Label -->
      <div
        class="mb-4 lg:mb-2 text-xl sm:text-2xl lg:text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in text-center lg:text-left"
      >
        Customer Management
        <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-sm font-normal text-blue-600 dark:text-blue-400">
          (Filtered)
        </span>
      </div>

      <!-- Filter Section (XM style) -->
      <div
        class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl p-4 sm:p-6 lg:p-8 mb-6 lg:mb-8 border border-white/20 dark:border-slate-700/20 transform hover:scale-[1.01] lg:hover:scale-[1.02] transition-all duration-300"
      >
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4 lg:gap-6 items-end"
        >
          <div class="col-span-1">
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"
                  />
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M11 9h2M12 17h.01"
                  />
                </svg>
                Client Account
              </span>
            </label>
            <input
              type="text"
              v-model="searchAccount"
              placeholder="ค้นหา Client Account, Client UID, หรือข้อมูลอื่นๆ"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
            />
          </div>
          <div class="col-span-1">
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"
                  />
                </svg>
                Owner
              </span>
            </label>
            <select
              v-model="selectedOwner"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
            >
              <option value="all">ทั้งหมด</option>
              <option value="ham">Ham</option>
              <option value="janischa">Janischa</option>

            </select>
          </div>
          <div class="col-span-1">
            <label
              class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2"
            >
              <span class="flex items-center gap-2">
                <svg
                  class="w-4 h-4"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
                Actions
              </span>
            </label>
            <button
              @click="resetFilters"
              class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl bg-gradient-to-r from-gray-300 via-gray-200 to-gray-100 text-gray-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm sm:text-base"
            >
              Reset
            </button>
          </div>
        </div>
      </div>

      <!-- Statistics Cards (XM style) -->
                <div
            class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 lg:gap-6 mb-6 lg:mb-8"
          >
        <!-- Debug Info -->
        <div
          class="col-span-full bg-yellow-50/80 dark:bg-yellow-900/20 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-yellow-200/20 dark:border-yellow-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <h3 class="text-lg font-bold text-yellow-900 dark:text-yellow-100 mb-4">
            Debug Info - ข้อมูลจากแต่ละแหล่ง
          </h3>
          <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
            <div class="text-center">
              <p class="text-sm font-semibold text-yellow-700 dark:text-yellow-300">Local DB</p>
              <p class="text-xl font-bold text-yellow-600">{{ debugInfo.local_db || 0 }}</p>
            </div>
            <div class="text-center">
              <p class="text-sm font-semibold text-blue-700 dark:text-blue-300">XM (Ham)</p>
              <p class="text-xl font-bold text-blue-600">{{ debugInfo.xm || 0 }}</p>
            </div>
            <div class="text-center">
              <p class="text-sm font-semibold text-purple-700 dark:text-purple-300">Client Account 1 (Ham)</p>
              <p class="text-xl font-bold text-purple-600">{{ debugInfo.client_account1 || 0 }}</p>
            </div>
            <div class="text-center">
              <p class="text-sm font-semibold text-green-700 dark:text-green-300">Client Account (Janischa)</p>
              <p class="text-xl font-bold text-green-600">{{ debugInfo.client_account || 0 }}</p>
            </div>
          </div>
          <div class="mt-4 text-center">
            <p class="text-sm text-yellow-700 dark:text-yellow-300">
              รวมทั้งหมด: {{ Object.values(debugInfo).reduce((sum, val) => sum + (val || 0), 0) }} รายการ
            </p>
          </div>
        </div>
        <!-- Sync Status -->
        <div
          class="col-span-full bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            สถานะการ Sync ข้อมูล (รวม XM)
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="text-center">
              <div class="flex items-center justify-center mb-2">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Ham (รวม XM)</span>
              </div>
              <p class="text-2xl font-bold text-blue-600">{{ 
                customers.filter(c => getOwnerInfo(c).type === 'ham').length 
              }}</p>
              <p class="text-xs text-gray-500">ในฐานข้อมูล</p>
              <p class="text-xs text-blue-600 dark:text-blue-400">
                ~1,363 จาก Exness API + ~1,216 จาก XM API + Client Account Data
              </p>
            </div>
            <div class="text-center">
              <div class="flex items-center justify-center mb-2">
                <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Janischa</span>
              </div>
              <p class="text-2xl font-bold text-purple-600">{{ 
                customers.filter(c => getOwnerInfo(c).type === 'janischa').length 
              }}</p>
              <p class="text-xs text-gray-500">ในฐานข้อมูล</p>
              <p class="text-xs text-purple-600 dark:text-purple-400">
                ~357 จาก Exness API + Client Account Data
              </p>
            </div>
          </div>
          <div class="mt-4 text-center">
            <button
              @click="syncData"
              class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-sm"
            >
              Sync ข้อมูลใหม่
            </button>
          </div>
        </div>
        <!-- Owner Statistics -->
        <div
          class="col-span-full bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">
            สถิติแยกตาม Owner (รวม XM)
            <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-sm font-normal text-blue-600 dark:text-blue-400">
              (แสดงเฉพาะผลลัพธ์ที่กรอง)
            </span>
          </h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="text-center">
              <div class="flex items-center justify-center mb-2">
                <span class="w-3 h-3 bg-blue-500 rounded-full mr-2"></span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Ham (รวม XM)</span>
              </div>
              <p class="text-2xl font-bold text-blue-600">{{ 
                customers.filter(c => getOwnerInfo(c).type === 'ham').length 
              }}</p>
              <p class="text-xs text-gray-500">ลูกค้า</p>
              <p class="text-xs text-blue-600 dark:text-blue-400">
                (Exness + XM + Client Account)
              </p>
              <p v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-xs text-blue-600 dark:text-blue-400">
                {{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'ham').length }} ในผลลัพธ์
              </p>
            </div>
            <div class="text-center">
              <div class="flex items-center justify-center mb-2">
                <span class="w-3 h-3 bg-purple-500 rounded-full mr-2"></span>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Janischa</span>
              </div>
              <p class="text-2xl font-bold text-purple-600">{{ 
                customers.filter(c => getOwnerInfo(c).type === 'janischa').length 
              }}</p>
              <p class="text-xs text-gray-500">ลูกค้า</p>
              <p class="text-xs text-purple-600 dark:text-purple-400">
                (Exness + Client Account)
              </p>
              <p v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-xs text-purple-600 dark:text-purple-400">
                {{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'janischa').length }} ในผลลัพธ์
              </p>
            </div>

          </div>
        </div>
        <div
          class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p
                class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate"
              >
                Total Customers
                <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                  (Filtered)
                </span>
              </p>
              <p
                class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
              >
                {{ formatNumber(filteredStats.total_customers) }}
              </p>
              <p v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                จาก {{ customers.length }} รายการทั้งหมด
              </p>
            </div>
            <div
              class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0"
            >
              <svg
                class="w-6 h-6 lg:w-8 lg:h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                ></path>
              </svg>
            </div>
          </div>
        </div>
        <div
          class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p
                class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate"
              >
                Active Customers
                <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                  (Filtered)
                </span>
              </p>
              <p
                class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
              >
                {{ formatNumber(filteredStats.active_customers) }}
              </p>
              <p v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                จาก {{ customers.filter(c => c.status === "ACTIVE" || c.status === "Valid" || c.status === "UNKNOWN" || c.client_status === "ACTIVE" || c.raw_data?.client_status === "ACTIVE" || c.valid === true).length }} รายการทั้งหมด
              </p>
            </div>
            <div
              class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0"
            >
              <svg
                class="w-6 h-6 lg:w-8 lg:h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                ></path>
              </svg>
            </div>
          </div>
        </div>
        <div
          class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p
                class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate"
              >
                Total Reward (USD)
                <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                  (Filtered)
                </span>
              </p>
              <p
                class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
              >
                ${{ formatNumber(filteredStats.total_reward_usd) }}
              </p>
              <p v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                จาก ${{ formatNumber(customers.reduce((sum, c) => {
                  const rewardUsd = parseFloat(c.reward_usd) || 
                                  parseFloat(c.total_reward_usd) || 
                                  parseFloat(c.raw_data?.reward_usd) || 
                                  parseFloat(c.raw_data?.total_reward_usd) || 
                                  parseFloat(c.commission) ||
                                  parseFloat(c.profit) ||
                                  0;
                  return sum + (isNaN(rewardUsd) ? 0 : rewardUsd);
                }, 0)) }} ทั้งหมด
              </p>
            </div>
            <div
              class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0"
            >
              <svg
                class="w-6 h-6 lg:w-8 lg:h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                ></path>
              </svg>
            </div>
          </div>
        </div>
        <div
          class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1 min-w-0">
              <p
                class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate"
              >
                Total Rebate (USD)
                <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                  (Filtered)
                </span>
              </p>
              <p
                class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
              >
                ${{ formatNumber(filteredStats.total_rebate_usd) }}
              </p>
              <p v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-xs text-blue-600 dark:text-blue-400 mt-1">
                จาก ${{ formatNumber(customers.reduce((sum, c) => {
                  const rebateUsd = parseFloat(c.rebate_amount_usd) || 
                                  parseFloat(c.raw_data?.rebate_amount_usd) || 
                                  parseFloat(c.rebate) ||
                                  parseFloat(c.cashback) ||
                                  0;
                  return sum + (isNaN(rebateUsd) ? 0 : rebateUsd);
                }, 0)) }} ทั้งหมด
              </p>
            </div>
            <div
              class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6 flex-shrink-0"
            >
              <svg
                class="w-6 h-6 lg:w-8 lg:h-8 text-white"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                ></path>
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Customers Table (XM style) -->
      <div
        class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl border border-white/20 dark:border-slate-700/20"
      >
        <div
          class="px-4 sm:px-6 lg:px-8 py-4 lg:py-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4"
        >
          <div>
            <h3
              class="text-xl lg:text-2xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent"
            >
              Customers List
              <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-sm font-normal text-blue-600 dark:text-blue-400">
                (Filtered Results)
              </span>
            </h3>
            <p
              class="mt-1 lg:mt-2 text-sm lg:text-base text-gray-600 dark:text-gray-400"
            >
              Detailed information about all customers and their current status
              <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                (แสดงเฉพาะผลลัพธ์ที่กรอง)
              </span>
            </p>
            <!-- แสดงผลการค้นหาและกรอง -->
            <div v-if="searchAccount.trim() || selectedOwner !== 'all'" class="mt-2">
              <p class="text-sm text-blue-600 dark:text-blue-400">
                <span v-if="searchAccount.trim()" class="font-semibold">ค้นหา:</span> 
                <span v-if="searchAccount.trim()">{{ searchAccount.trim() }}</span>
                <span v-if="searchAccount.trim() && selectedOwner !== 'all'" class="mx-2">•</span>
                <span v-if="selectedOwner !== 'all'" class="font-semibold">Owner:</span> 
                <span v-if="selectedOwner !== 'all'">
                  {{ selectedOwner === "ham" ? "Ham" : selectedOwner === "janischa" ? "Janischa" : "ไม่ระบุ" }}
                </span>
              </p>
              <p class="text-sm text-green-600 dark:text-green-400 mt-1">
                พบ {{ filteredCustomers.length }} รายการ
                <span v-if="filteredCustomers.length > 0">
                  ({{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'ham').length }} Ham (รวม XM), 
                  {{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'janischa').length }} Janischa, 
                   
                  {{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'unknown').length }} ไม่ระบุ)
                </span>
              </p>
            </div>
          </div>
          <div class="flex gap-2">
            <button
              @click="fetchData"
              class="group w-full sm:w-auto px-6 lg:px-8 py-2.5 lg:py-3 rounded-xl bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm lg:text-base"
            >
              <span class="flex items-center justify-center gap-2">
                <svg
                  class="w-4 h-4 lg:w-5 lg:h-5 transform group-hover:rotate-180 transition-transform duration-500"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                  />
                </svg>
                <span class="hidden sm:inline">Fetch Data</span>
                <span class="sm:hidden">Fetch</span>
              </span>
            </button>
          </div>
        </div>

        <div class="border-t border-blue-100/20 dark:border-slate-700/20">
          <div class="overflow-x-auto">
            <table
              class="min-w-full divide-y divide-blue-100/20 dark:divide-slate-700/20"
            >
              <thead class="bg-blue-50/50 dark:bg-slate-700/50">
                <tr>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Client UID
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Client Account
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Country
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Status
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Reward (USD)
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Rebate (USD)
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Owner
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Registration Date
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Source
                  </th>
                  <th
                    scope="col"
                    class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                  >
                    Partner Account
                  </th>

                </tr>
              </thead>
              <tbody
                class="bg-white/50 dark:bg-slate-800/50 divide-y divide-blue-100/20 dark:divide-slate-700/20"
              >
                <!-- Show prompt if no customers found -->
                <tr v-if="!searchAccount.trim() && customers.length === 0">
                  <td
                    colspan="10"
                    class="px-3 sm:px-6 py-12 text-center text-gray-400 dark:text-gray-500"
                  >
                    ไม่มีข้อมูลลูกค้า กรุณากดปุ่ม "Fetch Data" เพื่อโหลดข้อมูล
                    <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                      (แสดงเฉพาะผลลัพธ์ที่กรอง)
                    </span>
                  </td>
                </tr>
                <!-- Loading State -->
                <tr
                  v-else-if="isLoading"
                  class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50"
                >
                  <td
                    colspan="10"
                    class="px-3 sm:px-6 py-8 lg:py-12 text-center"
                  >
                    <div class="flex items-center justify-center">
                      <svg
                        class="animate-spin h-6 w-6 lg:h-8 lg:w-8 text-blue-500"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                      >
                        <circle
                          class="opacity-25"
                          cx="12"
                          cy="12"
                          r="10"
                          stroke="currentColor"
                          stroke-width="4"
                        ></circle>
                        <path
                          class="opacity-75"
                          fill="currentColor"
                          d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                      </svg>
                      <span
                        class="ml-3 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400"
                        >Loading data...<span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400"> (Filtered)</span></span
                      >
                    </div>
                  </td>
                </tr>
                <!-- No Data State -->
                <tr
                  v-else-if="paginatedCustomers.length === 0"
                  class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50"
                >
                  <td
                    colspan="10"
                    class="px-3 sm:px-6 py-8 lg:py-12 text-center text-xs sm:text-sm text-gray-600 dark:text-gray-400"
                  >
                    {{
                      customers.length === 0
                        ? 'ไม่มีข้อมูลลูกค้า กรุณากดปุ่ม "Fetch Data" เพื่อโหลดข้อมูล'
                        : searchAccount.trim() || selectedOwner !== "all"
                        ? `ไม่พบลูกค้าที่ตรงกับเงื่อนไขการค้นหา${searchAccount.trim() ? ` "${searchAccount.trim()}"` : ""}${selectedOwner !== "all" ? ` และ Owner: ${selectedOwner === "ham" ? "Ham" : selectedOwner === "janischa" ? "Janischa" : "ไม่ระบุ"}` : ""}`
                        : "ไม่พบลูกค้าที่ตรงกับเงื่อนไขการค้นหา"
                    }}
                    <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-blue-600 dark:text-blue-400">
                      (แสดงเฉพาะผลลัพธ์ที่กรอง)
                    </span>
                  </td>
                </tr>
                <!-- Data Rows -->
                <tr
                  v-else
                  v-for="customer in paginatedCustomers"
                  :key="
                    customer.client_uid ||
                    customer.client_id ||
                    customer.traderId ||
                    customer.raw_data?.client_uid ||
                    customer.raw_data?.client_id ||
                    customer.raw_data?.traderId ||
                    customer.login ||
                    customer.account_number ||
                    customer.source ||
                    Math.random()
                  "
                  class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors duration-200"
                >
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-white"
                  >
                    {{
                      customer.client_uid ||
                      customer.client_id ||
                      customer.traderId ||
                      customer.raw_data?.client_uid ||
                      customer.raw_data?.client_id ||
                      customer.raw_data?.traderId ||
                      "-"
                    }}
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    {{
                      customer.raw_data?.client_account ||
                      customer.client_account ||
                      customer.account_number ||
                      customer.login ||
                      customer.traderId ||
                      customer.client_uid ||
                      customer.client_id ||
                      "-"
                    }}
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    {{ 
                      customer.country || 
                      customer.client_country || 
                      customer.raw_data?.country ||
                      customer.raw_data?.client_country ||
                      "-" 
                    }}
                  </td>
                  <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'px-2 sm:px-4 py-1 sm:py-1.5 inline-flex items-center gap-1 sm:gap-1.5 text-xs font-semibold rounded-full',
                        customer.status === 'ACTIVE' ||
                        customer.status === 'Valid' ||
                        customer.status === 'UNKNOWN' ||
                        customer.client_status === 'ACTIVE' ||
                        customer.raw_data?.client_status === 'ACTIVE' ||
                        customer.valid === true
                          ? 'bg-green-100/80 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                          : 'bg-red-100/80 text-red-800 dark:bg-red-800/20 dark:text-red-400',
                      ]"
                    >
                      <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span
                          :class="[
                            'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                            customer.status === 'ACTIVE' ||
                            customer.status === 'Valid' ||
                            customer.status === 'UNKNOWN' ||
                            customer.client_status === 'ACTIVE' ||
                            customer.raw_data?.client_status === 'ACTIVE' ||
                            customer.valid === true
                              ? 'bg-green-400'
                              : 'bg-red-400',
                          ]"
                        ></span>
                        <span
                          :class="[
                            'relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2',
                            customer.status === 'ACTIVE' ||
                            customer.status === 'Valid' ||
                            customer.status === 'UNKNOWN' ||
                            customer.client_status === 'ACTIVE' ||
                            customer.raw_data?.client_status === 'ACTIVE' ||
                            customer.valid === true
                              ? 'bg-green-500'
                              : 'bg-red-500',
                          ]"
                        ></span>
                      </span>
                      <span class="hidden sm:inline">{{
                        customer.status === "ACTIVE" ||
                        customer.status === "Valid" ||
                        customer.status === "UNKNOWN" ||
                        customer.client_status === "ACTIVE" ||
                        customer.raw_data?.client_status === "ACTIVE" ||
                        customer.valid === true
                          ? "Active"
                          : "Inactive"
                      }}</span>
                      <span class="sm:hidden">{{
                        customer.status === "ACTIVE" ||
                        customer.status === "Valid" ||
                        customer.status === "UNKNOWN" ||
                        customer.client_status === "ACTIVE" ||
                        customer.raw_data?.client_status === "ACTIVE" ||
                        customer.valid === true
                          ? "A"
                          : "I"
                      }}</span>
                    </span>
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    ${{
                      formatNumber(
                        (() => {
                          const rewardUsd = parseFloat(customer.reward_usd) || 
                                          parseFloat(customer.total_reward_usd) || 
                                          parseFloat(customer.raw_data?.reward_usd) || 
                                          parseFloat(customer.raw_data?.total_reward_usd) || 
                                          parseFloat(customer.commission) ||
                                          parseFloat(customer.profit) ||
                                          0;
                          return isNaN(rewardUsd) ? 0 : rewardUsd;
                        })()
                      )
                    }}
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    ${{ formatNumber((() => {
                      const rebateUsd = parseFloat(customer.rebate_amount_usd) || 
                                      parseFloat(customer.raw_data?.rebate_amount_usd) || 
                                      parseFloat(customer.rebate) ||
                                      parseFloat(customer.cashback) ||
                                      0;
                      return isNaN(rebateUsd) ? 0 : rebateUsd;
                    })()) }}
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm"
                  >
                    <span
                      :class="[
                        'px-2 sm:px-3 py-1 sm:py-1.5 inline-flex items-center gap-1 sm:gap-1.5 text-xs font-semibold rounded-full',
                        getOwnerInfo(customer).type === 'ham'
                          ? 'bg-blue-100/80 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400'
                          : getOwnerInfo(customer).type === 'janischa'
                          ? 'bg-purple-100/80 text-purple-800 dark:bg-purple-800/20 dark:text-purple-400'
                          : 'bg-gray-100/80 text-gray-800 dark:bg-gray-800/20 dark:text-gray-400'
                      ]"
                    >
                      <span class="relative flex h-1.5 w-1.5 sm:h-2 sm:w-2">
                        <span
                          :class="[
                            'animate-ping absolute inline-flex h-full w-full rounded-full opacity-75',
                            getOwnerInfo(customer).type === 'ham'
                              ? 'bg-blue-400'
                              : getOwnerInfo(customer).type === 'janischa'
                              ? 'bg-purple-400'
                              : 'bg-gray-400'
                          ]"
                        ></span>
                        <span
                          :class="[
                            'relative inline-flex rounded-full h-1.5 w-1.5 sm:h-2 sm:w-2',
                            getOwnerInfo(customer).type === 'ham'
                              ? 'bg-blue-500'
                              : getOwnerInfo(customer).type === 'janischa'
                              ? 'bg-purple-500'
                              : 'bg-gray-500'
                          ]"
                        ></span>
                      </span>
                      {{ getOwnerInfo(customer).name }}
                      <span v-if="customer.source === 'XM'" class="text-xs text-gray-500 ml-1">(XM)</span>
                      <span v-else-if="customer.source === 'Exness1' || customer.source === 'Exness1_ClientAccount'" class="text-xs text-gray-500 ml-1">(Exness1)</span>
                      <span v-else-if="customer.source === 'Exness2' || customer.source === 'Exness2_ClientAccount'" class="text-xs text-gray-500 ml-1">(Exness2)</span>
                      <span v-else-if="customer.traderId || customer.raw_data?.traderId" class="text-xs text-gray-500 ml-1">(XM)</span>
                      <span v-else-if="customer.partner_account && customer.partner_account.includes('Janischa')" class="text-xs text-gray-500 ml-1">(Exness1)</span>
                      <span v-else-if="customer.partner_account && customer.partner_account.includes('ham')" class="text-xs text-gray-500 ml-1">(Exness2)</span>
                    </span>
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    {{ 
                      customer.reg_date || 
                      customer.raw_data?.reg_date ||
                      customer.signUpDate ||
                      customer.raw_data?.signUpDate ||
                      customer.created_at ||
                      customer.registration_date ||
                      "-" 
                    }}
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    {{ 
                      customer.source || 
                      customer.raw_data?.source ||
                      (customer.traderId || customer.raw_data?.traderId ? 'XM' : 
                       customer.partner_account && customer.partner_account.includes('Janischa') ? 'Exness1' :
                       customer.partner_account && customer.partner_account.includes('ham') ? 'Exness2' : '-')
                    }}
                  </td>
                  <td
                    class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300"
                  >
                    {{ 
                      customer.partner_account || 
                      customer.raw_data?.partner_account ||
                      customer.campaign ||
                      customer.raw_data?.campaign ||
                      customer.partner ||
                      customer.raw_data?.partner ||
                      "-" 
                    }}
                  </td>

                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination Controls -->
          <div
            class="px-4 sm:px-6 py-4 bg-white/50 dark:bg-slate-800/50 border-t border-blue-100/20 dark:border-slate-700/20"
          >
            <div
              class="flex flex-col sm:flex-row items-center justify-between gap-4"
            >
              <div
                class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left"
              >
                <span>Showing</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  startIndex + 1
                }}</span>
                <span>to</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  endIndex
                }}</span>
                <span>of</span>
                <span class="font-semibold text-gray-900 dark:text-white">{{
                  totalItems
                }}</span>
                <span class="hidden sm:inline">entries</span>
                <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="ml-2 text-blue-600 dark:text-blue-400">
                  (จาก {{ filteredCustomers.length }} รายการที่กรอง)
                  <span class="text-xs">
                    ({{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'ham').length }} Ham (รวม XM), 
                    {{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'janischa').length }} Janischa, 

                    {{ filteredCustomers.filter(c => getOwnerInfo(c).type === 'unknown').length }} ไม่ระบุ)
                  </span>
                </span>
              </div>

              <div class="flex items-center gap-1 sm:gap-2">
                <button
                  @click="previousPage"
                  :disabled="currentPage === 1"
                  class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600 border border-blue-100 dark:border-slate-600"
                >
                  <span class="hidden sm:inline">Previous</span>
                  <span class="sm:hidden">Prev</span>
                </button>
                <div class="flex items-center gap-1">
                  <button
                    v-for="page in displayedPages"
                    :key="page"
                    @click="goToPage(page)"
                    :class="[
                      'px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200',
                      currentPage === page
                        ? 'bg-blue-500 text-white hover:bg-blue-600'
                        : 'bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600',
                    ]"
                  >
                    {{ page }}
                  </button>
                </div>
                <button
                  @click="nextPage"
                  :disabled="currentPage === totalPages"
                  class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600 border border-blue-100 dark:border-slate-600"
                >
                  <span class="hidden sm:inline">Next</span>
                  <span class="sm:hidden">→</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error Section -->
    <div
      v-if="error"
      class="mt-8 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4"
    >
      <h4 class="text-lg font-semibold text-red-800 dark:text-red-200 mb-2">
        Error Loading Data
        <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-sm font-normal text-red-600 dark:text-red-400">
          (Filtered)
        </span>
      </h4>
              <p class="text-red-700 dark:text-red-300">
          {{ error }}
          <span v-if="searchAccount.trim() || selectedOwner !== 'all'" class="text-red-600 dark:text-red-400">
            (แสดงเฉพาะผลลัพธ์ที่กรอง)
          </span>
        </p>
      <button
        @click="fetchData"
        class="mt-2 px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700"
      >
        Retry
      </button>
    </div>
  </div>
  <BottomNavBar :menu="menuItems" />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import axios from "axios";
import { Head } from "@inertiajs/vue3";
import TopNavBar from "@/Components/TopNavBar.vue";
import BottomNavBar from "@/Components/BottomNavBar.vue";

const customers = ref([]);
const currentPage = ref(1);
const itemsPerPage = 10;
const isLoading = ref(false);
const error = ref(null);
const debugInfo = ref({}); // เพิ่ม debug info
const stats = ref({
  total_customers: 0,
  active_customers: 0,
  total_reward_usd: 0,
  total_rebate_usd: 0,
});
const searchAccount = ref("");
const selectedOwner = ref("all"); // เพิ่มตัวแปรสำหรับกรองตาม Owner
const startDate = ref(
  new Date(Date.now() - 30 * 24 * 60 * 60 * 1000).toISOString().split("T")[0]
);
const endDate = ref(new Date().toISOString().split("T")[0]);

// Setup axios defaults
axios.defaults.headers.common["X-Requested-With"] = "XMLHttpRequest";
axios.defaults.withCredentials = true;

axios.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      window.location.reload();
    }
    return Promise.reject(error);
  }
);

const totalItems = computed(() => customers.value.length);
const totalPages = computed(() => Math.ceil(totalItems.value / itemsPerPage));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() =>
  Math.min(startIndex.value + itemsPerPage, totalItems.value)
);
const filteredCustomers = computed(() => {
  const search = searchAccount.value.trim();
  const ownerFilter = selectedOwner.value;
  
  let filtered = customers.value;

  // กรองตาม Owner
  if (ownerFilter !== "all") {
    filtered = filtered.filter((customer) => {
      const ownerInfo = getOwnerInfo(customer);
      return ownerInfo.type === ownerFilter;
    });
  }

  // กรองตามการค้นหา (เฉพาะ Client Account)
  if (search) {
    const searchLower = search.toLowerCase();
    return filtered.filter((customer) => {
      const clientAccount =
        customer.client_account ||
        customer.raw_data?.client_account ||
        customer.account_number ||
        customer.login ||
        customer.traderId ||
        customer.client_uid ||
        customer.client_id;
      return clientAccount && String(clientAccount).toLowerCase().includes(searchLower);
    });
  }

  return filtered;
});

const paginatedCustomers = computed(() => {
  return filteredCustomers.value.slice(startIndex.value, endIndex.value);
});

const filteredStats = computed(() => {
  const customersToShow = filteredCustomers.value;

  // Debug: Log some customer data to understand the structure
  if (customersToShow.length > 0) {
    console.log("Debug filteredStats - First customer:", customersToShow[0]);
    console.log("Debug filteredStats - reward_usd types:", customersToShow.slice(0, 3).map(c => ({
      reward_usd: c.reward_usd,
      total_reward_usd: c.total_reward_usd,
      rebate_amount_usd: c.rebate_amount_usd,
      raw_data_reward_usd: c.raw_data?.reward_usd,
      raw_data_total_reward_usd: c.raw_data?.total_reward_usd,
      raw_data_rebate_amount_usd: c.raw_data?.rebate_amount_usd,
      types: {
        reward_usd: typeof c.reward_usd,
        total_reward_usd: typeof c.total_reward_usd,
        rebate_amount_usd: typeof c.rebate_amount_usd,
        raw_data_reward_usd: typeof c.raw_data?.reward_usd,
        raw_data_total_reward_usd: typeof c.raw_data?.total_reward_usd,
        raw_data_rebate_amount_usd: typeof c.raw_data?.rebate_amount_usd
      }
    })));
  }

  const totalReward = customersToShow.reduce(
    (sum, c) => {
      // Check both direct properties and raw_data properties
      const rewardUsd = parseFloat(c.reward_usd) || 
                       parseFloat(c.total_reward_usd) || 
                       parseFloat(c.raw_data?.reward_usd) || 
                       parseFloat(c.raw_data?.total_reward_usd) || 
                       parseFloat(c.commission) ||
                       parseFloat(c.profit) ||
                       0;
      const result = sum + (isNaN(rewardUsd) ? 0 : rewardUsd);
      console.log(`Customer reward calculation: ${c.reward_usd} || ${c.total_reward_usd} || ${c.raw_data?.reward_usd} || ${c.raw_data?.total_reward_usd} || ${c.commission} || ${c.profit} = ${rewardUsd}, sum = ${result}`);
      return result;
    },
    0
  );

  const totalRebate = customersToShow.reduce(
    (sum, c) => {
      const rebateUsd = parseFloat(c.rebate_amount_usd) || 
                       parseFloat(c.raw_data?.rebate_amount_usd) || 
                       parseFloat(c.rebate) ||
                       parseFloat(c.cashback) ||
                       0;
      const result = sum + (isNaN(rebateUsd) ? 0 : rebateUsd);
              console.log(`Customer rebate calculation: ${c.rebate_amount_usd} || ${c.raw_data?.rebate_amount_usd} || ${c.rebate} || ${c.cashback} = ${rebateUsd}, sum = ${result}`);
      return result;
    },
    0
  );

  console.log("Debug filteredStats - Final totals:", { totalReward, totalRebate });
  console.log("Debug filteredStats - isNaN check:", { 
    totalRewardIsNaN: isNaN(totalReward), 
    totalRebateIsNaN: isNaN(totalRebate),
    totalRewardType: typeof totalReward,
    totalRebateType: typeof totalRebate
  });

  return {
    total_customers: customersToShow.length,
    active_customers: customersToShow.filter(
      (c) =>
        c.status === "ACTIVE" ||
        c.status === "Valid" ||
        c.status === "UNKNOWN" ||
        c.client_status === "ACTIVE" ||
        c.raw_data?.client_status === "ACTIVE" ||
        c.valid === true
    ).length,
    total_reward_usd: isNaN(totalReward) ? 0 : totalReward,
    total_rebate_usd: isNaN(totalRebate) ? 0 : totalRebate,
  };
});

const displayedPages = computed(() => {
  const pages = [];
  const maxDisplayedPages = 5;
  let start = Math.max(
    1,
    currentPage.value - Math.floor(maxDisplayedPages / 2)
  );
  let end = Math.min(totalPages.value, start + maxDisplayedPages - 1);

  if (end - start + 1 < maxDisplayedPages) {
    start = Math.max(1, end - maxDisplayedPages + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  return pages;
});

const goToPage = (page) => {
  currentPage.value = page;
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const fetchData = async () => {
  isLoading.value = true;
  error.value = null;

  try {
    console.log("Fetching data from /admin/all-customers...");

    // Call the all-customers endpoint that we just fixed
    const response = await axios.get("/admin/all-customers");

    console.log("API Response:", response.data);
    console.log("Response status:", response.status);

    // Handle different response structures
    let customersData = [];
    if (Array.isArray(response.data)) {
      customersData = response.data;
    } else if (Array.isArray(response.data?.data)) {
      customersData = response.data.data;
    } else if (Array.isArray(response.data?.data?.customers)) {
      customersData = response.data.data.customers;
    } else if (Array.isArray(response.data?.customers)) {
      customersData = response.data.customers;
    }

    customers.value = customersData;

    // Handle debug info
    if (response.data?.data?.debug_info) {
      debugInfo.value = response.data.data.debug_info;
      console.log("Debug info:", debugInfo.value);
    }

    // Handle stats
    if (response.data?.data?.stats) {
      stats.value = response.data.data.stats;
    } else if (response.data?.stats) {
      stats.value = response.data.stats;
    }

    currentPage.value = 1;

    // Debug: log the structure of the first customer and all field names
    if (customers.value.length > 0) {
      console.log("ตัวอย่างข้อมูลลูกค้า:", customers.value[0]);
      console.log("ชื่อฟิลด์ทั้งหมด:", Object.keys(customers.value[0]));

      // Log first few customers for debugging
      customers.value.slice(0, 3).forEach((customer, index) => {
        console.log(`ลูกค้าที่ ${index + 1}:`, customer);
        console.log(`ลูกค้าที่ ${index + 1} reward_usd:`, customer.reward_usd, typeof customer.reward_usd);
        console.log(`ลูกค้าที่ ${index + 1} total_reward_usd:`, customer.total_reward_usd, typeof customer.total_reward_usd);
        console.log(`ลูกค้าที่ ${index + 1} rebate_amount_usd:`, customer.rebate_amount_usd, typeof customer.rebate_amount_usd);
        console.log(`ลูกค้าที่ ${index + 1} raw_data:`, customer.raw_data);
        console.log(`ลูกค้าที่ ${index + 1} raw_data.reward_usd:`, customer.raw_data?.reward_usd, typeof customer.raw_data?.reward_usd);
        console.log(`ลูกค้าที่ ${index + 1} raw_data.total_reward_usd:`, customer.raw_data?.total_reward_usd, typeof customer.raw_data?.total_reward_usd);
        console.log(`ลูกค้าที่ ${index + 1} raw_data.rebate_amount_usd:`, customer.raw_data?.rebate_amount_usd, typeof customer.raw_data?.rebate_amount_usd);
      });
    } else {
      console.log("ไม่มีข้อมูลลูกค้า");
      console.log("Response structure:", response.data);
    }
  } catch (e) {
    console.error("Error fetching data:", e);
    console.error("Error details:", {
      message: e.message,
      response: e.response?.data,
      status: e.response?.status,
      headers: e.response?.headers,
    });
    error.value = e.message || "An error occurred while fetching data";
    customers.value = [];
  } finally {
    isLoading.value = false;
  }
};

const formatNumber = (number) => {
  // Check if number is NaN or invalid
  if (isNaN(number) || number === null || number === undefined) {
    return "0";
  }
  return new Intl.NumberFormat("en-US").format(number);
};

// ฟังก์ชันเพื่อระบุว่าเป็นข้อมูลของ Ham, Janischa หรือ Kantapong
const getOwnerInfo = (customer) => {
  const partnerAccount = customer.partner_account || customer.raw_data?.partner_account;
  const clientAccount = customer.client_account || customer.raw_data?.client_account || customer.account_number || customer.login;
  const source = customer.source || customer.raw_data?.source;
  const dataSource = customer.data_source || customer.raw_data?.data_source;
  
  // ตรวจสอบจาก source และ data_source ก่อน (ใหม่)
  if (source === 'XM' || dataSource === 'Ham' && (customer.traderId || customer.raw_data?.traderId)) {
    return { name: "Ham", type: "ham" };
  }
  
  if (source === 'Exness1_ClientAccount' || dataSource === 'Ham') {
    return { name: "Ham", type: "ham" };
  }
  
  if (source === 'Exness2_ClientAccount' || dataSource === 'Janischa') {
    return { name: "Janischa", type: "janischa" };
  }
  
  if (!partnerAccount) return { name: "ไม่ระบุ", type: "unknown" };
  
  // ตรวจสอบ partner_account เพื่อระบุว่าเป็นของใคร
  if (partnerAccount === "1172984151037556173") {
    return { name: "Ham", type: "ham" };
  } else if (partnerAccount === "1167686758601662826") {
    return { name: "Janischa", type: "janischa" };
  } else if (partnerAccount === "1130924909696967913") {
    return { name: "Janischa", type: "janischa" };
  } else if (partnerAccount === "1129255941915600142") {
    return { name: "Ham", type: "ham" };
  } else if (partnerAccount === "XM_HAM") {
    return { name: "Ham", type: "ham" };
  } else if (partnerAccount === "low") {
    return { name: "Ham", type: "ham" };
  } else if (partnerAccount === "pay U") {
    return { name: "Ham", type: "ham" };
  } else if (partnerAccount === "RB you") {
    return { name: "Ham", type: "ham" };
  } else if (partnerAccount === "K.kan") {
    return { name: "Ham", type: "ham" };
  }
  
  // ตรวจสอบจาก client_account หรือ login เพื่อระบุว่าเป็น XM หรือ Exness
  if (clientAccount) {
    // ถ้าเป็น XM (มี traderId หรือเป็น XM account)
    if (customer.traderId || customer.raw_data?.traderId || 
        (typeof clientAccount === 'string' && clientAccount.includes('XM'))) {
      return { name: "Ham", type: "ham" };
    }
    
    // ถ้าเป็น Exness account ของ Janischa (ตรวจสอบจาก partner_account หรือ email)
    if (partnerAccount === "Janischa.trade@gmail.com" || 
        partnerAccount === "janischa.exness@gmail.com" ||
        (typeof partnerAccount === 'string' && partnerAccount.includes('Janischa'))) {
      return { name: "Janischa", type: "janischa" };
    }
    
    // ถ้าเป็น Exness account ของ Ham (ตรวจสอบจาก partner_account หรือ email)
    if (partnerAccount === "hamsftmo@gmail.com" || 
        partnerAccount === "ham.exness@gmail.com" ||
        (typeof partnerAccount === 'string' && partnerAccount.includes('ham'))) {
      return { name: "Ham", type: "ham" };
    }
  }
  
  // Fallback: Check if customer has owner field from backend
  if (customer.owner) {
    if (customer.owner === "Ham") {
      return { name: "Ham", type: "ham" };
    } else if (customer.owner === "Janischa") {
      return { name: "Janischa", type: "janischa" };
    }
  }
  
  return { name: "ไม่ระบุ", type: "unknown" };
};

function resetFilters() {
  searchAccount.value = "";
  selectedOwner.value = "all";
}

const syncData = async () => {
  try {
    console.log("Starting data sync...");
    
    // Call the sync command via API
    const response = await axios.post("/admin/sync-data");
    
    if (response.data.success) {
      console.log("Sync completed successfully");
      // Refresh data after sync
      await fetchData();
    } else {
      console.error("Sync failed:", response.data.message);
    }
  } catch (error) {
    console.error("Sync error:", error);
  }
};

onMounted(() => {
  fetchData();
});

const menuItems = [
  {
    label: "Dashboard",
    to: "/admin",
    icon: "mdiViewDashboard",
  },
  {
    label: "Customers",
    to: "/admin/customers",
    icon: "mdiAccountGroup",
  },
  {
    label: "XM",
    to: "/admin/xm",
    icon: "mdiChartBar",
  },
  // ... add more as needed
];
</script>

<style scoped>
.backdrop-blur-lg {
  backdrop-filter: blur(16px);
  -webkit-backdrop-filter: blur(16px);
}
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}
.shadow-2xl {
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}
.hover\:scale-105:hover {
  transform: scale(1.05);
}
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.5);
}
.dark input:focus {
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.3);
}
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
</style>

