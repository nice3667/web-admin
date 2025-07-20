<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import TopNavBar from '@/Components/TopNavBar.vue';
import BottomNavBar from '@/Components/BottomNavBar.vue';
import axios from 'axios';

const clients = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');
const searchAccountType = ref('');
const searchStatus = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;

// Computed properties for filtered data
const filteredClients = computed(() => {
    let filtered = clients.value;
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(client => 
            client.client_id?.toLowerCase().includes(query) ||
            client.name?.toLowerCase().includes(query) ||
            client.email?.toLowerCase().includes(query)
        );
    }
    
    if (searchAccountType.value) {
        filtered = filtered.filter(client => 
            client.account_type?.toLowerCase().includes(searchAccountType.value.toLowerCase())
        );
    }
    
    if (searchStatus.value) {
        filtered = filtered.filter(client => 
            client.status?.toLowerCase() === searchStatus.value.toLowerCase()
        );
    }
    
    return filtered;
});

const totalCount = computed(() => clients.value.length);
const filteredCount = computed(() => filteredClients.value.length);

// Pagination
const totalPages = computed(() => Math.ceil(filteredCount.value / itemsPerPage));
const startIndex = computed(() => (currentPage.value - 1) * itemsPerPage);
const endIndex = computed(() => Math.min(startIndex.value + itemsPerPage, filteredCount.value));
const paginatedClients = computed(() => 
    filteredClients.value.slice(startIndex.value, endIndex.value)
);

// Statistics
const clientStats = computed(() => {
    const activeClients = clients.value.filter(client => client.status === 'active').length;
    const totalBalance = clients.value.reduce((sum, client) => sum + (parseFloat(client.balance) || 0), 0);
    const averageBalance = totalBalance / clients.value.length || 0;
    
    return {
        total: clients.value.length,
        active: activeClients,
        totalBalance: totalBalance,
        averageBalance: averageBalance
    };
});

// Pagination methods
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

const displayedPages = computed(() => {
    const pages = [];
    const maxDisplayedPages = 5;
    let start = Math.max(1, currentPage.value - Math.floor(maxDisplayedPages / 2));
    let end = Math.min(totalPages.value, start + maxDisplayedPages - 1);

    if (end - start + 1 < maxDisplayedPages) {
        start = Math.max(1, end - maxDisplayedPages + 1);
    }

    for (let i = start; i <= end; i++) {
        pages.push(i);
    }
    return pages;
});

// Reset filters
const resetFilters = () => {
    searchQuery.value = '';
    searchAccountType.value = '';
    searchStatus.value = '';
    currentPage.value = 1;
};

// Format number
const formatNumber = (number) => {
    return new Intl.NumberFormat('en-US').format(number);
};

// Format date
const formatDate = (dateString) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleDateString();
};

// Get unique account types
const accountTypeOptions = computed(() => {
    const types = new Set();
    clients.value.forEach(client => {
        if (client.account_type) types.add(client.account_type);
    });
    return Array.from(types).sort();
});

onMounted(async () => {
    try {
        const response = await axios.get('/api/clients');
        console.log('API Response:', response.data);
        clients.value = Array.isArray(response.data) ? response.data : [];
        loading.value = false;
    } catch (err) {
        console.error('Error fetching clients:', err);
        error.value = 'ไม่สามารถโหลดข้อมูลลูกค้าได้';
        loading.value = false;
    }
});

const menuItems = [
    {
        label: "Dashboard",
        to: "/admin",
        icon: 'mdiViewDashboard'
    },
    {
        label: "Client Account",
        to: "/client-account",
        icon: 'mdiAccountGroup'
    },
    {
        label: "XM Reports",
        to: "/admin/xm",
        icon: 'mdiChartBar'
    }
];
</script>

<template>
    <Head title="Client Account Dashboard" />
    
    <TopNavBar />
    <div class="min-h-screen py-4 sm:py-8 lg:py-12 bg-gradient-to-br from-blue-50 via-white to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Page Title with Animation -->
            <div class="mb-6 lg:mb-8 text-center animate-fade-in">
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                    Client Account Dashboard
                </h1>
                <p class="mt-2 lg:mt-3 text-sm sm:text-base lg:text-lg text-gray-600 dark:text-gray-400">
                    Manage and monitor all client accounts and their activities
                </p>
            </div>

            <!-- Client Partner Label -->
            <div class="mb-4 lg:mb-2 text-xl sm:text-2xl lg:text-3xl font-extrabold text-blue-700 dark:text-blue-300 animate-fade-in text-center">
                Client Management
            </div>
            
            <!-- Filter Section -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl p-4 sm:p-6 lg:p-8 mb-6 lg:mb-8 border border-white/20 dark:border-slate-700/20 transform hover:scale-[1.01] lg:hover:scale-[1.02] transition-all duration-300">
                <!-- Main Filters -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 lg:gap-6 mb-4 lg:mb-6">
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                Search Client
                            </span>
                        </label>
                        <input
                            type="text"
                            v-model="searchQuery"
                            placeholder="Search by ID, name, email..."
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                        >
                    </div>
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Account Type
                            </span>
                        </label>
                        <select
                            v-model="searchAccountType"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                        >
                            <option value="">All Types</option>
                            <option v-for="type in accountTypeOptions" :key="type" :value="type">{{ type }}</option>
                        </select>
                    </div>
                    <div class="col-span-1">
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Status
                            </span>
                        </label>
                        <select
                            v-model="searchStatus"
                            class="w-full px-3 sm:px-4 py-2.5 sm:py-3 rounded-xl border-2 border-blue-100 dark:border-slate-600 bg-white dark:bg-slate-800 text-gray-700 dark:text-gray-300 focus:border-blue-500 dark:focus:border-blue-400 focus:ring focus:ring-blue-200 dark:focus:ring-blue-800 transition duration-200 text-sm sm:text-base"
                        >
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                
                <!-- Reset Button Row -->
                <div class="flex justify-center lg:justify-end">
                    <button
                        @click="resetFilters"
                        class="w-full sm:w-auto px-6 lg:px-8 py-2.5 lg:py-3 rounded-xl bg-gradient-to-r from-gray-300 via-gray-200 to-gray-100 text-gray-700 font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm sm:text-base"
                    >
                        Reset Filters
                    </button>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 lg:gap-6 mb-6 lg:mb-8">
                <!-- Total Clients Card -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Total Clients</p>
                            <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                                {{ formatNumber(clientStats.total) }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="p-3 lg:p-4 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
                                <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Clients Card -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Active Clients</p>
                            <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-green-600 via-emerald-600 to-teal-600 bg-clip-text text-transparent">
                                {{ formatNumber(clientStats.active) }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="p-3 lg:p-4 bg-gradient-to-br from-green-500 via-emerald-500 to-teal-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
                                <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Total Balance Card -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Total Balance</p>
                            <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-yellow-600 via-orange-600 to-red-600 bg-clip-text text-transparent">
                                ${{ formatNumber(clientStats.totalBalance) }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="p-3 lg:p-4 bg-gradient-to-br from-yellow-500 via-orange-500 to-red-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
                                <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Average Balance Card -->
                <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-xl rounded-xl lg:rounded-2xl p-4 lg:p-6 border border-white/20 dark:border-slate-700/20 transform transition-all duration-300 hover:scale-105 hover:rotate-1">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 min-w-0">
                            <p class="text-xs sm:text-sm font-semibold text-gray-600 dark:text-gray-400 truncate">Average Balance</p>
                            <p class="mt-1 lg:mt-2 text-xl sm:text-2xl lg:text-3xl font-extrabold bg-gradient-to-r from-purple-600 via-pink-600 to-rose-600 bg-clip-text text-transparent">
                                ${{ formatNumber(clientStats.averageBalance.toFixed(2)) }}
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="p-3 lg:p-4 bg-gradient-to-br from-purple-500 via-pink-500 to-rose-500 rounded-xl lg:rounded-2xl rotate-3 transform transition-transform duration-300 hover:rotate-6">
                                <svg class="w-6 h-6 lg:w-8 lg:h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Clients Table -->
            <div class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-lg overflow-hidden shadow-2xl rounded-xl lg:rounded-2xl border border-white/20 dark:border-slate-700/20">
                <div class="px-4 sm:px-6 lg:px-8 py-4 lg:py-6 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                    <div>
                        <h3 class="text-xl lg:text-2xl font-bold bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-600 bg-clip-text text-transparent">
                            Client Accounts
                        </h3>
                        <p class="mt-1 lg:mt-2 text-sm lg:text-base text-gray-600 dark:text-gray-400">
                            Detailed information about all client accounts and their status
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            @click="currentPage = 1"
                            class="group w-full sm:w-auto px-6 lg:px-8 py-2.5 lg:py-3 rounded-xl bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-slate-800 text-sm lg:text-base"
                        >
                            <span class="flex items-center justify-center gap-2">
                                <svg class="w-4 h-4 lg:w-5 lg:h-5 transform group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="hidden sm:inline">Refresh Data</span>
                                <span class="sm:hidden">Refresh</span>
                            </span>
                        </button>
                    </div>
                </div>

                <div class="border-t border-blue-100/20 dark:border-slate-700/20">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-blue-100/20 dark:divide-slate-700/20">
                            <thead class="bg-blue-50/50 dark:bg-slate-700/50">
                                <tr>
                                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Client ID</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden sm:table-cell">Name</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">Email</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">Balance</th>
                                    <th scope="col" class="px-3 sm:px-6 py-3 sm:py-4 text-left text-xs font-semibold text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden xl:table-cell">Source</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white/50 dark:bg-slate-800/50 divide-y divide-blue-100/20 dark:divide-slate-700/20">
                                <!-- Loading State -->
                                <tr v-if="loading" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50">
                                    <td colspan="6" class="px-3 sm:px-6 py-8 lg:py-12 text-center">
                                        <div class="flex items-center justify-center">
                                            <svg class="animate-spin h-6 w-6 lg:h-8 lg:w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="ml-3 text-xs sm:text-sm font-medium text-gray-600 dark:text-gray-400">Loading data...</span>
                                        </div>
                                    </td>
                                </tr>

                                <!-- No Data State -->
                                <tr v-else-if="!paginatedClients.length" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50">
                                    <td colspan="6" class="px-3 sm:px-6 py-8 lg:py-12 text-center text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                                        No clients found for the selected filters
                                    </td>
                                </tr>

                                <!-- Data Rows -->
                                <tr v-else v-for="client in paginatedClients" :key="client.id" class="hover:bg-blue-50/50 dark:hover:bg-slate-700/50 transition-colors duration-200">
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm font-medium text-gray-900 dark:text-white">{{ client.client_id }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden sm:table-cell">{{ client.name }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden md:table-cell">{{ client.email }}</td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap">
                                        <span :class="[
                                            'px-2 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            client.status === 'active'
                                                ? 'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400'
                                                : 'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400'
                                        ]">
                                            {{ client.status }}
                                        </span>
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden lg:table-cell">
                                        {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(client.balance) }}
                                    </td>
                                    <td class="px-3 sm:px-6 py-3 sm:py-4 whitespace-nowrap text-xs sm:text-sm text-gray-600 dark:text-gray-300 hidden xl:table-cell">
                                        <span :class="[
                                            'px-2 sm:px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full',
                                            client.source === 'V1'
                                                ? 'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400'
                                                : 'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-400'
                                        ]">
                                            {{ client.source }}
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Controls -->
                    <div class="px-4 sm:px-6 py-4 bg-white/50 dark:bg-slate-800/50 border-t border-blue-100/20 dark:border-slate-700/20">
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
                            <div class="flex items-center gap-2 text-xs sm:text-sm text-gray-600 dark:text-gray-400 text-center sm:text-left">
                                <span>Showing</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ startIndex + 1 }}</span>
                                <span>to</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ endIndex }}</span>
                                <span>of</span>
                                <span class="font-semibold text-gray-900 dark:text-white">{{ filteredCount }}</span>
                                <span class="hidden sm:inline">entries</span>
                            </div>

                            <div class="flex items-center gap-1 sm:gap-2">
                                <button
                                    @click="previousPage"
                                    :disabled="currentPage === 1"
                                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                                        bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                                        border border-blue-100 dark:border-slate-600"
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
                                                : 'bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600'
                                        ]"
                                    >
                                        {{ page }}
                                    </button>
                                </div>
                                <button
                                    @click="nextPage"
                                    :disabled="currentPage === totalPages"
                                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed
                                        bg-white dark:bg-slate-700 text-gray-700 dark:text-gray-300 hover:bg-blue-50 dark:hover:bg-slate-600
                                        border border-blue-100 dark:border-slate-600"
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
    </div>
    <BottomNavBar :menu="menuItems" />
</template>

<style scoped>
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
</style> 