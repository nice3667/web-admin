<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { mdiPlus, mdiPencil, mdiDelete } from '@mdi/js';

const props = defineProps({
    title: {
        type: String,
        default: 'Agent Management'
    }
});

const agents = ref([
    {
        id: 1,
        name: 'John Doe',
        email: 'john@example.com',
        commission_rate: '10%',
        total_clients: 25,
        status: 'Active',
        join_date: '2024-01-01'
    },
    {
        id: 2,
        name: 'Jane Smith',
        email: 'jane@example.com',
        commission_rate: '15%',
        total_clients: 15,
        status: 'Active',
        join_date: '2024-02-01'
    }
]);
</script>

<template>
    <Head :title="title" />

    <LayoutAuthenticated>
        <SectionMain>
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Agent Management
                </h1>
            </div>

            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-4">
                        <BaseButton
                            label="Add New Agent"
                            :icon="mdiPlus"
                            color="info"
                        />
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Name
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Email
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Commission Rate
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Total Clients
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Join Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="agent in agents" :key="agent.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ agent.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ agent.email }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ agent.commission_rate }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ agent.total_clients }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ agent.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ agent.join_date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <BaseButton
                                        :icon="mdiPencil"
                                        color="info"
                                        small
                                        class="mr-2"
                                    />
                                    <BaseButton
                                        :icon="mdiDelete"
                                        color="danger"
                                        small
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </CardBox>
        </SectionMain>
    </LayoutAuthenticated>
</template> 