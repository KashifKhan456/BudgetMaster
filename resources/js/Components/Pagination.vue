<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array,
});
</script>

<template>
    <div v-if="links.length > 3">
        <div class="flex flex-wrap -mb-1">
            <template v-for="(link, key) in links" :key="key">
                <div v-if="link.url === null"
                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 text-gray-400 dark:text-gray-500 border dark:border-gray-700 rounded"
                    v-html="link.label" />
                <Link v-else
                    class="mr-1 mb-1 px-4 py-3 text-sm leading-4 border dark:border-gray-700 rounded hover:bg-gray-50 dark:hover:bg-gray-700 focus:border-indigo-500 focus:text-indigo-500 dark:text-gray-300"
                    :class="{ 'bg-indigo-600 text-white dark:bg-indigo-500': link.active, 'bg-white dark:bg-[#1a1a1a]': !link.active }"
                    :href="link.url" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
