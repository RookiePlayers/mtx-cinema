<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import MTXTextField from '@/Components/common/MTXTextField.vue';
import { useFuture } from '@/hooks/useFetch';
import { useMovies } from '@/hooks/useMovies';
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
</script>

<template>

    <Head title="Home">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] p-6 text-[#111827] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <MTXTextField v-model="searchQuery" placeholder="Search for a movie..." :autocomplete="true" :options="options"
            :on-refresh-autocomplete-list="onRefreshAutoCompleteList"
            :on-suggestion-selected="(option) => goToMoviePage(option.imdbId)"
          />
        <h1 class="text-5xl font-bold tracking-tight sm:text-[5rem]">
            Welcome to MTX Cinema
        </h1>
        <p class="mt-6 max-w-2xl text-center text-lg leading-7 text-[#4B5563] dark:text-[#D1D5DB]">
            Search your favourite movie or not, up to you we don't do much than that!
        </p>
    </div>
</template>
