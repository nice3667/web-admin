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
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Client Account</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <!-- Search and Stats -->
                        <div class="mb-4">
                            <div class="flex justify-between items-center mb-4">
                                <div class="flex space-x-4">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="ค้นหาลูกค้า..."
                                        class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                </div>
                                <div class="text-sm text-gray-600">
                                    <p>จำนวนข้อมูลทั้งหมด: {{ totalCount }}</p>
                                    <p>ข้อมูลที่กรองแล้ว: {{ filteredCount }}</p>
                                </div>
                            </div>
                        </div>

                        <div v-if="loading" class="text-center">
                            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-gray-900 mx-auto"></div>
                            <p class="mt-2">กำลังโหลดข้อมูล...</p>
                        </div>

                        <div v-else-if="error" class="text-red-500 text-center">
                            {{ error }}
                        </div>

                        <div v-else-if="filteredClients.length === 0" class="text-center text-gray-500 py-8">
                            <p>ไม่พบข้อมูลลูกค้า</p>
                        </div>

                        <div v-else>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client ID</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Balance</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="client in filteredClients" :key="client.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ client.client_id }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ client.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ client.email }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span :class="{
                                                    'px-2 py-1 rounded-full text-xs': true,
                                                    'bg-green-100 text-green-800': client.status === 'active',
                                                    'bg-red-100 text-red-800': client.status === 'inactive',
                                                    'bg-gray-100 text-gray-800': !['active', 'inactive'].includes(client.status)
                                                }">
                                                    {{ client.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD' }).format(client.balance) }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <span :class="{
                                                    'px-2 py-1 rounded-full text-xs': true,
                                                    'bg-blue-100 text-blue-800': client.source === 'V1',
                                                    'bg-purple-100 text-purple-800': client.source === 'V2'
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