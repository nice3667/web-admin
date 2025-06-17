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
        default: 'Rebate Management'
    }
});

const rebates = ref([
    {
        id: 1,
        name: 'Standard Rebate',
        rate: '0.5%',
        min_lot: 1,
        status: 'Active'
    },
    {
        id: 2,
        name: 'Premium Rebate',
        rate: '1.0%',
        min_lot: 5,
        status: 'Active'
    }
]);
</script>

<template>
    <Head :title="title" />

    <LayoutAuthenticated>
        <SectionMain>
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Rebate Management
                </h1>
            </div>

            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-4">
                        <BaseButton
                            label="Add New Rebate"
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
                                    Rate
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Min Lot
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="rebate in rebates" :key="rebate.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ rebate.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ rebate.rate }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ rebate.min_lot }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ rebate.status }}
                                    </span>
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