<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import { useForm } from '@inertiajs/vue3';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const page = usePage();
const notifications = computed(() => page.props.auth.notifications);
const unreadCount = computed(() => notifications.value.length);

const markAsRead = (id) => {
    axios.put(route('notifications.read', id)).then(() => {
        const index = page.props.auth.notifications.findIndex(n => n.id === id);
        if (index !== -1) {
            page.props.auth.notifications.splice(index, 1);
        }
    });
};

const markAllAsRead = () => {
    axios.put(route('notifications.readAll')).then(() => {
        page.props.auth.notifications = [];
    });
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString() + ' ' + new Date(date).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <div class="relative">
        <Dropdown align="right" width="96" contentClasses="py-1 bg-white dark:bg-[#1a1a1a]">
            <template #trigger>
                <button class="relative p-2 text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 focus:outline-none transition duration-150 ease-in-out">
                    <span class="sr-only">Notifications</span>
                    <!-- Bell Icon -->
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    
                    <!-- Unread Badge -->
                    <span v-if="unreadCount > 0" class="absolute top-1 right-1 inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white transform translate-x-1/4 -translate-y-1/4 bg-red-600 rounded-full">
                        {{ unreadCount }}
                    </span>
                </button>
            </template>

            <template #content>
                <div class="px-4 py-2 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center">
                    <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Notifications</span>
                    <button v-if="unreadCount > 0" @click="markAllAsRead" class="text-xs text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300">
                        Mark all as read
                    </button>
                </div>

                <div v-if="notifications.length === 0" class="px-4 py-6 text-center text-sm text-gray-500 dark:text-gray-400">
                    No new notifications.
                </div>

                <div v-else class="max-h-96 overflow-y-auto">
                    <div v-for="notification in notifications" :key="notification.id" class="px-4 py-3 border-b border-gray-100 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-[#262626] transition duration-150 ease-in-out">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm text-gray-800 dark:text-gray-200 font-medium">
                                    {{ notification.data.message }}
                                </p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                    {{ formatDate(notification.created_at) }}
                                </p>
                            </div>
                            <button @click="markAsRead(notification.id)" class="ml-2 p-1 rounded-full text-gray-400 hover:text-gray-600 hover:bg-gray-200 dark:hover:text-gray-200 dark:hover:bg-gray-700 transition-colors duration-200" title="Mark as read">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </Dropdown>
    </div>
</template>
