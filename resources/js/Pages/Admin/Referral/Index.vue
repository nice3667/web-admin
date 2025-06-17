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
        default: 'Referral Management'
    }
});

const referrals = ref([
    {
        id: 1,
        referrer: 'John Doe',
        referred: 'Jane Smith',
        commission: '$50',
        status: 'Completed',
        date: '2024-03-15'
    },
    {
        id: 2,
        referrer: 'Mike Johnson',
        referred: 'Sarah Wilson',
        commission: '$75',
        status: 'Pending',
        date: '2024-03-16'
    }
]);
</script>

<template>
    <Head :title="title" />

    <LayoutAuthenticated>
        <SectionMain>
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Referral Management
                </h1>
            </div>

            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex space-x-4">
                        <BaseButton
                            label="Add New Referral"
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
                                    Referrer
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Referred
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Commission
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Date
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="referral in referrals" :key="referral.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ referral.referrer }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ referral.referred }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ referral.commission }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        referral.status === 'Completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]">
                                        {{ referral.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ referral.date }}
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