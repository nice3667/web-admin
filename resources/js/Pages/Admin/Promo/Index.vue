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
        default: 'Promo Management'
    }
});

const promos = ref([
    {
        id: 1,
        name: 'Welcome Bonus',
        type: 'Deposit Bonus',
        amount: '100%',
        status: 'Active',
        start_date: '2024-01-01',
        end_date: '2024-12-31'
    },
    {
        id: 2,
        name: 'Loyalty Program',
        type: 'Cashback',
        amount: '5%',
        status: 'Active',
        start_date: '2024-01-01',
        end_date: '2024-12-31'
    }
]);
</script>

<template>
    <Head :title="title" />

    <LayoutAuthenticated>
        <SectionMain>
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Promo Management
                </h1>
            </div>

            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-4">
                        <BaseButton
                            label="Add New Promo"
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
                                    Type
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Start Date
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    End Date
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
                            <tr v-for="promo in promos" :key="promo.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ promo.name }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ promo.type }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ promo.amount }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ promo.start_date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ promo.end_date }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ promo.status }}
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