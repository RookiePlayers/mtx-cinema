<script setup lang="ts">
import { router, useRemember } from '@inertiajs/vue3';
import { ref, watch  } from 'vue';
import type {Ref} from 'vue';
import MovieCard from '@/Components/common/MovieCard.vue';
import MTXTextField from '@/Components/common/MTXTextField.vue';
import PaginatedPageShell from '@/Components/PaginatedPageShell.vue';
import { resolveMovieId } from '@/helpers/normalizeKeys';
import { useFuture } from '@/hooks/useFetch';
import { useMovies } from '@/hooks/useMovies';
import { theme } from '@/theme';
import type { PaginationInput } from '@/types';
import type { Movie } from '@/types/movies';

const props = defineProps<{
    query?: string;
}>();

type SearchState = {
    inputQuery: string;
    submittedQuery: string;
    autocompleteQuery: string;
    options: Movie[];
};

const searchState: Ref<SearchState> = useRemember({
    inputQuery: props.query || '',
    submittedQuery: props.query || '',
    autocompleteQuery: props.query || '',
    options: [] as Movie[],
}, 'search.state') as Ref<SearchState>;
const limit = ref(10);
const { autocompleteSearch, searchMovies } = useMovies();
const {
    data,
    exec: runAutocomplete,
} = useFuture(autocompleteSearch);
const onRefreshAutoCompleteList = async (value: string) => {
    searchState.value.inputQuery = value;
    searchState.value.autocompleteQuery = value;
    await runAutocomplete(value);
    searchState.value.options = data.value ?? [];
};
watch(
    () => props.query,
    (newQuery) => {
        searchState.value.inputQuery = newQuery || '';
        searchState.value.submittedQuery = newQuery || '';
        searchState.value.autocompleteQuery = newQuery || '';
    },
);

const submitSearch = (value: string) => {
    searchState.value.inputQuery = value;
    searchState.value.submittedQuery = value;

    router.get(
        '/search',
        { query: value || undefined },
        {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        },
    );
};

const goToMoviePage = (movieId: string) => {
    router.get(`/movies/${movieId}`);
};

</script>

<template>
    <div class="container mx-auto py-8 w-full h-full">
        <div class="mb-6 flex w-full items-center justify-center gap-3">
            <MTXTextField
                :value="searchState.inputQuery"
                @update:value="(value) => (searchState.inputQuery = value)"
                :on-submit="submitSearch"
                container-class="mt-0 mb-0"
                autocomplete
                :options="searchState.options"
                :on-refresh-autocomplete-list="onRefreshAutoCompleteList"
                placeholder="Search for movies..."
                class="w-full"
            />
            <button
                type="button"
                class="h-15 shrink-0 rounded-3xl bg-gray-900 px-6 py-2 text-lg font-semibold text-white transition hover:bg-gray-700"
                :style="{
                    backgroundColor: theme.primary,
                }"
                @click="submitSearch(searchState.inputQuery)"
            >
                Search
            </button>

        </div>
        <PaginatedPageShell
            :task="
                (pagination: PaginationInput, query: string) =>
                    searchMovies(query, pagination)
            "
            :pageSize="limit"
            :args="[searchState.submittedQuery]"
            rememberKey="search.results"
        >
            <template #default="{ items }">
                <div
                class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                    <MovieCard
                        v-for="movie in (items as Movie[])"
                        :key="movie.id"
                        :movie-or-movie-id="movie"
                        @click="goToMoviePage(resolveMovieId(movie)??'')"
                        size="medium"
                    />
                </div>
            </template>
        </PaginatedPageShell>
    </div>
</template>
