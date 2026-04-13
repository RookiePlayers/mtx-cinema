<script setup lang="ts">
import { computed, ref, watch } from 'vue';
defineOptions({
    inheritAttrs: false,
});

import AutocompletePopover from '@/Components/common/AutocompletePopover.vue';
import { theme } from '@/theme';
import type { Movie } from '@/types/movies';

const props = withDefaults(
    defineProps<{
        value?: string;
        defaultValue?: string;
        autocomplete?: boolean;
        containerClass?: string;
        options?: Movie[];
        onSuggestionSelected?: (option: Movie) => void;
        onRefreshAutocompleteList?: (value: string) => void;
        onSubmit?: (value: string) => void;
    }>(),
    {
        value: '',
        defaultValue: '',
        autocomplete: false,
        containerClass: '',
        options: () => [],
    },
);

const emit = defineEmits<{
    'update:value': [value: string];
    select: [option: Movie];
}>();

const searchTerm = ref(props.value || props.defaultValue);
const isFocused = ref(false);

watch(
    () => props.value,
    (value) => {
        searchTerm.value = value ?? '';
    },
);

const shouldShowAutocomplete = computed(() => {
    return (
        props.autocomplete &&
        isFocused.value &&
        searchTerm.value.trim().length > 0
    );
});

const handleInput = (event: Event) => {
    const target = event.target as HTMLInputElement;
    searchTerm.value = target.value;
    emit('update:value', target.value);
    props.onRefreshAutocompleteList?.(target.value);
};

const handleSelect = (option: Movie) => {
    searchTerm.value = option.title || '';
    emit('update:value', option.title || '');
    emit('select', option);
    props.onSuggestionSelected?.(option);
    isFocused.value = false;
};
const handleOnSubmit = () => {
    props.onSubmit?.(searchTerm.value);
};
</script>

<template>
    <div :class="['relative mt-6 mb-6 w-full', props.containerClass]">
        <input
            v-bind="$attrs"
            type="text"
            :value="searchTerm"
            placeholder="Search for movies..."
            class="block h-15 w-full rounded-3xl border border-gray-300/30 bg-gray-900/20 px-4 py-2 text-lg focus:ring-1 focus:outline-none"
            :style="{
                '--mtx-focus-color': theme.primary,
            }"
            @input="handleInput"
            @focus="isFocused = true"
            @blur="isFocused = false"
            @keydown.enter.prevent="handleOnSubmit"
        />
        <AutocompletePopover
            v-if="shouldShowAutocomplete"
            :search-term="searchTerm"
            :options="props.options"
            @select="handleSelect"
        />
    </div>
</template>

<style scoped>
input:focus {
    border-color: var(--mtx-focus-color);
    box-shadow: 0 0 0 1px var(--mtx-focus-color);
}
</style>
