<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const totalAmount = ref(null);
const loading = ref(true);
const error = ref(null);

// Format currency function
const formatCurrency = (amount) => {
    if (amount === null || amount === undefined) return '$0.00';
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }).format(amount);
};

const fetchWalletAccounts = async () => {
    try {
        loading.value = true;
        error.value = null;
        totalAmount.value = null;
        
        const response = await fetch('/api/wallet/accounts', {
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            credentials: 'include'
        });

        if (!response.ok) {
            throw new Error('ไม่สามารถเชื่อมต่อกับเซิร์ฟเวอร์ได้');
        }
        
        const json = await response.json();
        console.log('API Response:', json);
        
        if (json.success && json.data && json.data.accounts) {
            console.log('Accounts data:', json.data.accounts);
            
            // คำนวณยอดรวมจากบัญชี USD
            totalAmount.value = json.data.accounts.reduce((sum, account) => {
                console.log('Processing account:', account);
                if (account.currency === 'USD' && typeof account.balance === 'number') {
                    console.log('Adding balance:', account.balance);
                    return sum + account.balance;
                }
                return sum;
            }, 0);
            console.log('Total amount calculated:', totalAmount.value);
        } else {
            error.value = 'ไม่พบข้อมูลบัญชี';
            console.error('Invalid data structure:', json);
        }
    } catch (err) {
        console.error('Error fetching wallet accounts:', err);
        error.value = 'ไม่สามารถดึงข้อมูลบัญชีได้ กรุณาลองใหม่อีกครั้ง';
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    fetchWalletAccounts();
});
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-gray-100">จำนวนเงินทั้งหมด</h3>
                        
                        <!-- แสดง Loading -->
                        <div v-if="loading" class="text-gray-600 dark:text-gray-400">
                            กำลังโหลดข้อมูล...
                        </div>

                        <!-- แสดง Error -->
                        <div v-else-if="error" class="text-red-600 dark:text-red-400">
                            {{ error }}
                        </div>

                        <!-- แสดงจำนวนเงิน -->
                        <div v-else class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ formatCurrency(totalAmount) }}
                        </div>

                        <!-- แสดงข้อมูลดิบเพื่อตรวจสอบ (ลบออกหลังจากแก้ไขเสร็จ) -->
                        <div v-if="!loading && !error" class="mt-4 text-sm text-gray-500">
                            Raw data for debugging (remove this later)
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>