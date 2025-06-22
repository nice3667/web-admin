<script setup>
import { Link, router } from '@inertiajs/vue3'
import { computed } from 'vue'

const props = defineProps({
    data: {
        type: Object,
        default: () => ({}),
    },
})

// Computed style for current page glow effect
const currentPageStyle = computed(() => ({
    boxShadow: `
        0 0 20px rgba(59, 130, 246, 0.8),
        0 0 40px rgba(99, 102, 241, 0.6),
        0 0 60px rgba(139, 92, 246, 0.4),
        0 0 0 4px rgba(59, 130, 246, 0.3)
    `,
    animation: 'pageGlow 2s infinite',
    position: 'relative',
    zIndex: 10
}))

// Helper function to get visible pages for pagination
const getVisiblePages = () => {
    if (!props.data || !props.data.last_page) return []

    const totalPages = props.data.last_page
    const currentPage = props.data.current_page
    const pages = []

    if (totalPages <= 7) {
        for (let i = 1; i <= totalPages; i++) {
            pages.push(i)
        }
    } else {
        let start = Math.max(currentPage - 3, 1)
        let end = Math.min(currentPage + 3, totalPages)

        if (start > 1) {
            pages.push(1)
            if (start > 2) {
                pages.push('...')
            }
        }

        for (let i = start; i <= end; i++) {
            pages.push(i)
        }

        if (end < totalPages) {
            if (end < totalPages - 1) {
                pages.push('...')
            }
            pages.push(totalPages)
        }
    }

    return pages
}

// Navigation function
const goToPage = (page) => {
    if (page < 1 || page > props.data.last_page) return

    console.log('Navigating to page:', page) // Debug log

    // Get current URL and preserve existing query parameters
    const url = new URL(window.location.href)
    const currentParams = new URLSearchParams(url.search)

    // Update page parameter
    currentParams.set('page', page)

    // Build new URL with all parameters
    const newUrl = `${url.pathname}?${currentParams.toString()}`

    console.log('New URL:', newUrl) // Debug log

    // Navigate using Inertia router
    router.get(newUrl, {}, {
        preserveState: false, // Force reload to ensure data updates
        preserveScroll: true,
        replace: true
    })
}
</script>

<template>
    <div v-if="data && data.total > 0" class="px-8 py-6 bg-gradient-to-r from-gray-50 via-blue-50 to-indigo-50 dark:from-gray-800 dark:via-slate-800 dark:to-slate-900 border-t border-gray-200 dark:border-gray-700 shadow-lg">
        <div class="flex flex-col lg:flex-row items-center justify-between space-y-4 lg:space-y-0">
            <!-- Pagination Info -->
            <div class="flex flex-col sm:flex-row items-center space-y-2 sm:space-y-0 sm:space-x-6">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 via-indigo-500 to-purple-600 rounded-xl flex items-center justify-center shadow-lg transform hover:scale-110 transition-all duration-300">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-sm font-bold text-gray-800 dark:text-gray-200">
                            แสดง {{ data.from }} ถึง {{ data.to }} จาก {{ data.total }} รายการ
                        </span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            หน้า {{ data.current_page }} จาก {{ data.last_page }}
                        </span>
                    </div>
                </div>

                <div class="flex items-center space-x-2">
                    <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center shadow-md transform hover:scale-110 transition-all duration-300">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 font-semibold">
                        {{ Math.ceil((data.current_page / data.last_page) * 100) }}% เสร็จสิ้น
                    </span>
                </div>
            </div>

            <!-- Pagination Navigation -->
            <div class="flex items-center space-x-3">
                <!-- Previous Button -->
                <button
                    :disabled="!data.prev_page_url"
                    @click="goToPage(data.current_page - 1)"
                    :class="[
                        'group flex items-center justify-center w-14 h-14 rounded-2xl border-2 transition-all duration-300 transform hover:scale-110 shadow-lg relative overflow-hidden',
                        data.prev_page_url
                            ? 'border-blue-300 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 text-white hover:from-blue-600 hover:via-indigo-600 hover:to-purple-700 hover:shadow-2xl hover:border-blue-400'
                            : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed dark:border-gray-600 dark:bg-gray-700'
                    ]"
                >
                    <div v-if="data.prev_page_url" class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <!-- Page Numbers -->
                <div class="flex items-center space-x-2">
                    <template v-for="page in getVisiblePages()" :key="page">
                        <button
                            v-if="page !== '...'"
                            @click="goToPage(page)"
                            :class="[
                                'group flex items-center justify-center min-w-[3.5rem] h-14 px-4 rounded-2xl border-2 font-bold text-sm transition-all duration-300 transform hover:scale-110 shadow-lg relative overflow-hidden',
                                page === data.current_page
                                    ? 'border-4 border-blue-500 bg-gradient-to-r from-blue-600 via-indigo-600 to-purple-700 text-white shadow-2xl scale-110'
                                    : 'border-2 border-gray-200 bg-white text-gray-700 hover:border-blue-300 hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 hover:text-blue-600 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gradient-to-r dark:hover:from-gray-600 dark:hover:to-slate-600 dark:hover:text-blue-400'
                            ]"
                        >
                            <div v-if="page === data.current_page" class="absolute inset-0 bg-gradient-to-r from-white/30 to-transparent rounded-2xl"></div>
                            <div v-else class="absolute inset-0 bg-gradient-to-r from-blue-100/50 to-indigo-100/50 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <span class="relative z-10 font-extrabold">{{ page }}</span>
                        </button>
                        <span v-else class="flex items-center justify-center w-14 h-14 text-gray-400 dark:text-gray-500 font-bold text-lg">
                            ...
                        </span>
                    </template>
                </div>

                <!-- Next Button -->
                <button
                    :disabled="!data.next_page_url"
                    @click="goToPage(data.current_page + 1)"
                    :class="[
                        'group flex items-center justify-center w-14 h-14 rounded-2xl border-2 transition-all duration-300 transform hover:scale-110 shadow-lg relative overflow-hidden',
                        data.next_page_url
                            ? 'border-blue-300 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 text-white hover:from-blue-600 hover:via-indigo-600 hover:to-purple-700 hover:shadow-2xl hover:border-blue-400'
                            : 'border-gray-200 bg-gray-100 text-gray-400 cursor-not-allowed dark:border-gray-600 dark:bg-gray-700'
                    ]"
                >
                    <div v-if="data.next_page_url" class="absolute inset-0 bg-gradient-to-r from-white/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="mt-4 w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 overflow-hidden">
            <div
                class="h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-600 rounded-full transition-all duration-500 ease-out shadow-lg"
                :style="{ width: `${(data.current_page / data.last_page) * 100}%` }"
            ></div>
        </div>
    </div>
</template>

<style scoped>
/* Glow animation for current page */
@keyframes pageGlow {
    0%, 100% {
        box-shadow:
            0 0 20px rgba(59, 130, 246, 0.8),
            0 0 40px rgba(99, 102, 241, 0.6),
            0 0 60px rgba(139, 92, 246, 0.4),
            0 0 0 4px rgba(59, 130, 246, 0.3);
        transform: scale(1);
    }
    50% {
        box-shadow:
            0 0 30px rgba(59, 130, 246, 1),
            0 0 50px rgba(99, 102, 241, 0.8),
            0 0 70px rgba(139, 92, 246, 0.6),
            0 0 0 6px rgba(59, 130, 246, 0.5);
        transform: scale(1.05);
    }
}

/* Hover effects */
.group:hover .group-hover\:scale-110 {
    transform: scale(1.1);
}

/* Smooth transitions */
* {
    transition-property: all;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for webkit browsers */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #3b82f6, #6366f1);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #2563eb, #4f46e5);
}
</style>
