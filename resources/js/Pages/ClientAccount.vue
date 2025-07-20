<script setup>
import { ref, onMounted, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const clients = ref([]);
const loading = ref(true);
const error = ref(null);
const searchQuery = ref('');

// Computed properties for filtered data
const filteredClients = computed(() => {
    if (!searchQuery.value) return clients.value;
    
    const query = searchQuery.value.toLowerCase();
    return clients.value.filter(client => 
        client.client_id?.toLowerCase().includes(query) ||
        client.name?.toLowerCase().includes(query) ||
        client.email?.toLowerCase().includes(query) ||
        client.status?.toLowerCase().includes(query)
    );
});

const totalCount = computed(() => clients.value.length);
const filteredCount = computed(() => filteredClients.value.length);

onMounted(async () => {
    try {
        // Get client data from session
        const response = await axios.get('/api/clients');
        console.log('API Response:', response.data); // Debug log
        clients.value = response.data;
        loading.value = false;
    } catch (err) {
        console.error('Error fetching clients:', err); // Debug log
        error.value = 'ไม่สามารถโหลดข้อมูลลูกค้าได้';
        loading.value = false;
    }
});
</script>

<template>
    <Head title="Client Account" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-lg sm:text-xl text-gray-800 leading-tight">Client Account</h2>
        </template>

        <div class="py-6 sm:py-8 lg:py-12">
            <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-slate-800 overflow-hidden shadow-sm sm:rounded-lg lg:rounded-xl">
                    <div class="p-4 sm:p-6 lg:p-8 text-gray-900 dark:text-gray-100">
                        <!-- Header Section -->
                        <div class="mb-6 sm:mb-8">
                            <h1 class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-2">
                                Client Management
                            </h1>
                            <p class="text-sm sm:text-base text-gray-600 dark:text-gray-400">
                                View and manage all client accounts and their information
                            </p>
                        </div>

                        <!-- Search and Stats Section -->
                        <div class="mb-6 sm:mb-8">
                            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 lg:gap-6 mb-4 sm:mb-6">
                                <!-- Search Section -->
                                <div class="flex-1 max-w-md">
                                    <label for="search" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        ค้นหาลูกค้า
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="search"
                                            v-model="searchQuery"
                                            type="text"
                                            placeholder="ค้นหาด้วย Client ID, ชื่อ, อีเมล หรือสถานะ..."
                                            class="w-full px-4 py-2.5 sm:py-3 pl-10 border border-gray-300 dark:border-gray-600 rounded-lg lg:rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:bg-slate-700 dark:text-white text-sm sm:text-base"
                                        />
                                        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-4 h-4 sm:w-5 sm:h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Stats Section -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6 lg:flex-shrink-0">
                                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg lg:rounded-xl p-3 sm:p-4 border border-blue-200 dark:border-blue-800">
                                        <div class="text-center sm:text-left">
                                            <p class="text-xs sm:text-sm font-medium text-blue-600 dark:text-blue-400">ข้อมูลทั้งหมด</p>
                                            <p class="text-lg sm:text-xl lg:text-2xl font-bold text-blue-700 dark:text-blue-300">{{ totalCount }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg lg:rounded-xl p-3 sm:p-4 border border-green-200 dark:border-green-800">
                                        <div class="text-center sm:text-left">
                                            <p class="text-xs sm:text-sm font-medium text-green-600 dark:text-green-400">ข้อมูลที่กรองแล้ว</p>
                                            <p class="text-lg sm:text-xl lg:text-2xl font-bold text-green-700 dark:text-green-300">{{ filteredCount }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading State -->
                        <div v-if="loading" class="text-center py-8 sm:py-12">
                            <div class="animate-spin rounded-full h-8 w-8 sm:h-12 sm:w-12 border-b-2 border-gray-900 dark:border-white mx-auto"></div>
                            <p class="mt-3 sm:mt-4 text-sm sm:text-base text-gray-600 dark:text-gray-400">กำลังโหลดข้อมูล...</p>
                        </div>

                        <!-- Error State -->
                        <div v-else-if="error" class="text-center py-8 sm:py-12">
                            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg lg:rounded-xl p-4 sm:p-6">
                                <svg class="mx-auto h-8 w-8 sm:h-12 sm:w-12 text-red-600 dark:text-red-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.268 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                </svg>
                                <p class="text-sm sm:text-base text-red-600 dark:text-red-400 font-medium">{{ error }}</p>
                            </div>
                        </div>

                        <!-- No Data State -->
                        <div v-else-if="filteredClients.length === 0" class="text-center py-8 sm:py-12">
                            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg lg:rounded-xl p-6 sm:p-8">
                                <svg class="mx-auto h-12 w-12 sm:h-16 sm:w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                </svg>
                                <p class="text-base sm:text-lg text-gray-500 dark:text-gray-400 font-medium">ไม่พบข้อมูลลูกค้า</p>
                                <p class="mt-2 text-sm text-gray-400 dark:text-gray-500">ลองเปลี่ยนเงื่อนไขการค้นหาหรือรีเฟรชหน้าใหม่</p>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div v-else class="overflow-hidden">
                            <!-- Mobile Card View (sm and below) -->
                            <div class="block lg:hidden space-y-4">
                                <div v-for="client in filteredClients" :key="client.id" 
                                     class="bg-gray-50 dark:bg-slate-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600">
                                    <div class="space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Client ID</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ client.client_id }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Name</span>
                                            <span class="text-sm text-gray-900 dark:text-white">{{ client.name }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Email</span>
                                            <span class="text-sm text-gray-900 dark:text-white break-all">{{ client.email }}</span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Status</span>
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400': client.status === 'active',
                                                'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400': client.status === 'inactive',
                                                'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['active', 'inactive'].includes(client.status)
                                            }">
                                                {{ client.status }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Balance</span>
                                            <span class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(client.balance) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Source</span>
                                            <span :class="{
                                                'px-2 py-1 rounded-full text-xs font-medium': true,
                                                'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400': client.source === 'V1',
                                                'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-400': client.source === 'V2'
                                            }">
                                                {{ client.source }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Desktop Table View (lg and above) -->
                            <div class="hidden lg:block overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-slate-700">
                                        <tr>
                                            <th class="px-4 xl:px-6 py-3 xl:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Client ID</th>
                                            <th class="px-4 xl:px-6 py-3 xl:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Name</th>
                                            <th class="px-4 xl:px-6 py-3 xl:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                                            <th class="px-4 xl:px-6 py-3 xl:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                            <th class="px-4 xl:px-6 py-3 xl:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Balance</th>
                                            <th class="px-4 xl:px-6 py-3 xl:py-4 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Source</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-slate-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="client in filteredClients" :key="client.id" class="hover:bg-gray-50 dark:hover:bg-slate-700 transition-colors duration-200">
                                            <td class="px-4 xl:px-6 py-3 xl:py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ client.client_id }}</td>
                                            <td class="px-4 xl:px-6 py-3 xl:py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">{{ client.name }}</td>
                                            <td class="px-4 xl:px-6 py-3 xl:py-4 text-sm text-gray-900 dark:text-white max-w-xs truncate">{{ client.email }}</td>
                                            <td class="px-4 xl:px-6 py-3 xl:py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <span :class="{
                                                    'px-2 py-1 rounded-full text-xs font-medium': true,
                                                    'bg-green-100 text-green-800 dark:bg-green-800/20 dark:text-green-400': client.status === 'active',
                                                    'bg-red-100 text-red-800 dark:bg-red-800/20 dark:text-red-400': client.status === 'inactive',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300': !['active', 'inactive'].includes(client.status)
                                                }">
                                                    {{ client.status }}
                                                </span>
                                            </td>
                                            <td class="px-4 xl:px-6 py-3 xl:py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                                {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(client.balance) }}
                                            </td>
                                            <td class="px-4 xl:px-6 py-3 xl:py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                                <span :class="{
                                                    'px-2 py-1 rounded-full text-xs font-medium': true,
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-800/20 dark:text-blue-400': client.source === 'V1',
                                                    'bg-purple-100 text-purple-800 dark:bg-purple-800/20 dark:text-purple-400': client.source === 'V2'
                                                }">
                                                    {{ client.source }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template> 