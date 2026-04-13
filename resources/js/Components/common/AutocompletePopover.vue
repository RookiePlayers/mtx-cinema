<script setup lang="ts">
import { ref, watch } from 'vue';
import type { Movie } from '@/types/movies';

const props = defineProps<{
    searchTerm: string;
    options?: Movie[];
}>();

defineEmits<{
    select: [option: Movie];
}>();
const options = ref<Movie[]>(props.options || []);
watch(
    () => props.options,
    (newOptions) => {
        options.value = newOptions || [];
    },
);
</script>
<template>
    <div class="absolute z-10 mt-1 w-full rounded-md bg-white shadow-lg">
        <ul
            v-if="options && options.length > 0"
            class="ring-opacity-5 max-h-60 overflow-auto rounded-md py-1 text-base ring-1 ring-black focus:outline-none sm:text-sm"
        >
            <li
                v-for="option in options"
                :key="option.id"
                @mousedown.prevent
                @click="$emit('select', option)"
                class="relative cursor-pointer py-2 pr-9 pl-3 text-gray-900 select-none hover:bg-gray-100"
            >
                <img :src="option.poster" alt="Poster" class="inline-block h-10 w-10 rounded mr-3 object-cover" />
                {{ option.title }}
            </li>
        </ul>
        <div v-else class="px-3 py-2 text-gray-500">No results found.</div>
    </div>
</template>
<style scoped></style>
