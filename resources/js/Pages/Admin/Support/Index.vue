<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import LayoutAuthenticated from '@/Layouts/Admin/LayoutAuthenticated.vue';
import SectionMain from '@/Components/SectionMain.vue';
import CardBox from '@/Components/CardBox.vue';
import BaseButton from '@/Components/BaseButton.vue';
import { mdiPlus, mdiPencil, mdiDelete, mdiTicket, mdiHelpCircle, mdiEmail } from '@mdi/js';

const props = defineProps({
    title: {
        type: String,
        default: 'Support Management'
    }
});

const tickets = ref([
    {
        id: 1,
        subject: 'Account Verification Issue',
        user: 'John Doe',
        status: 'Open',
        priority: 'High',
        created_at: '2024-03-15'
    },
    {
        id: 2,
        subject: 'Withdrawal Request',
        user: 'Jane Smith',
        status: 'In Progress',
        priority: 'Medium',
        created_at: '2024-03-16'
    }
]);

const faqs = ref([
    {
        id: 1,
        question: 'How to verify my account?',
        category: 'Account',
        status: 'Published'
    },
    {
        id: 2,
        question: 'How to withdraw funds?',
        category: 'Withdrawal',
        status: 'Published'
    }
]);
</script>

<template>
    <Head :title="title" />

    <LayoutAuthenticated>
        <SectionMain>
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                    Support Management
                </h1>
            </div>

            <!-- Tickets Section -->
            <CardBox class="mb-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiTicket" class="mr-2" />
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            Support Tickets
                        </h2>
                    </div>
                    <div class="flex space-x-4">
                        <BaseButton
                            label="New Ticket"
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
                                    Subject
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    User
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Status
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Priority
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Created At
                                </th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-900 dark:divide-gray-700">
                            <tr v-for="ticket in tickets" :key="ticket.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ticket.subject }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ticket.user }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span :class="[
                                        'px-2 inline-flex text-xs leading-5 font-semibold rounded-full',
                                        ticket.status === 'Open' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]">
                                        {{ ticket.status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ticket.priority }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ ticket.created_at }}
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

            <!-- FAQ Section -->
            <CardBox>
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center">
                        <BaseIcon :path="mdiHelpCircle" class="mr-2" />
                        <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                            FAQ Management
                        </h2>
                    </div>
                    <div class="flex space-x-4">
                        <BaseButton
                            label="Add FAQ"
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
                                    Question
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                    Category
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
                            <tr v-for="faq in faqs" :key="faq.id">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ faq.question }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ faq.category }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        {{ faq.status }}
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