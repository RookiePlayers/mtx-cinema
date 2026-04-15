<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MTXTextField from '@/Components/common/MTXTextField.vue';
import { useFuture } from '@/hooks/useFetch';
import { useMovies } from '@/hooks/useMovies';
import { theme } from '@/theme';
import type { Movie } from '@/types/movies';

const props = defineProps({
    movies: Array as () => Movie[],
    query: String,
})
const searchQuery = ref(props.query || '');
const options = ref<Movie[]>([]);
const { autocompleteSearch } = useMovies();
const {
    data,
    exec: runAutocomplete,
} = useFuture(autocompleteSearch);
const onRefreshAutoCompleteList = async (value: string) => {
    searchQuery.value = value;
    await runAutocomplete(value);
    console.log('Autocomplete results:', data.value);
    options.value = data.value ?? [];
};
const goToMoviePage = (movieId: string) => {
    router.get(`/movies/${movieId}`);
};

const submitSearch = (value: string) => {
    const query = value.trim();

    router.get('/search', {
        query: query || undefined,
    });
};
</script>

<template>

    <Head title="Home">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] p-6 text-[#111827] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <div class="mb-6 flex w-full max-w-3xl items-center gap-3">
            <MTXTextField
                :value="searchQuery"
                @update:value="(value) => (searchQuery = value)"
                placeholder="Search for a movie..."
                :autocomplete="true"
                :options="options"
                :on-refresh-autocomplete-list="onRefreshAutoCompleteList"
                :on-suggestion-selected="(option) => goToMoviePage(option.imdbId)"
                :on-submit="submitSearch"
                container-class="mt-0 mb-0"
                class="w-full"
            />
            <button
                type="button"
                class="h-15 shrink-0 rounded-3xl px-6 py-2 text-lg font-semibold text-white transition hover:bg-gray-700"
                :style="{
                    backgroundColor: theme.primary,
                }"
                @click="submitSearch(searchQuery)"
            >
                Search
            </button>
        </div>
        <h1 class="text-5xl font-bold tracking-tight sm:text-[5rem]">
            Welcome to MTX Cinema
        </h1>
        <p class="mt-6 max-w-2xl text-center text-lg leading-7 text-[#4B5563] dark:text-[#D1D5DB]">
            Search your favourite movie or not, up to you we don't do much than that!
        </p>
    </div>
</template>
