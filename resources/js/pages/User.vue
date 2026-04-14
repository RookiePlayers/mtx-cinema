<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MovieCard from '@/Components/common/MovieCard.vue';
import { resolveMovieId } from '@/helpers/normalizeKeys';
import {theme} from '@/theme';
import type { Auth } from '@/types';
import type { Movie } from '@/types/movies';

type SavedMovie = Movie & {
    savedByUserName?: string;
    savedAt?: string;
};

const props = defineProps<{
    savedMovies: SavedMovie[];
    scope: 'mine' | 'all';
}>();
const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth.user);
const savedMovies = computed(() => props.savedMovies ?? []);
const viewMode = ref<'grid' | 'table'>('grid');
const activeScope = computed(() => props.scope ?? 'mine');
const goToMoviePage = (movie: Movie) => {
    console.log('Navigating to movie page with ID:', movie);
    router.get(`/movies/${resolveMovieId(movie) ?? movie.id ?? movie.imdbId ?? ''}`);
};

const changeScope = (scope: 'mine' | 'all') => {
    router.get('/users', { scope }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

</script>
<template>
    <div class="flex min-h-screen w-full flex-col bg-[#FDFDFC] p-6 text-[#111827] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <p class="text-lg mb-2 font-bold">Welcome, {{ user?.name }}!</p>
        <div class="mb-4 flex w-full items-center justify-between gap-4">
            <div>
                <h1 class="mb-2 text-3xl font-bold">Saved Movies</h1>
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 font-semibold text-white"
                        :class="activeScope === 'mine' ? 'hover:opacity-90' : 'bg-gray-600 hover:bg-gray-500'"
                        :style="activeScope === 'mine' ? { backgroundColor: theme.primary } : undefined"
                        @click="changeScope('mine')"
                    >My Saved Movies</button>
                    <button
                        type="button"
                        class="rounded-xl px-4 py-2 font-semibold text-white"
                        :class="activeScope === 'all' ? 'hover:opacity-90' : 'bg-gray-600 hover:bg-gray-500'"
                        :style="activeScope === 'all' ? { backgroundColor: theme.primary } : undefined"
                        @click="changeScope('all')"
                    >All Saved Movies</button>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 font-semibold text-white"
                    :class="viewMode === 'grid' ? 'hover:opacity-90' : 'bg-gray-600 hover:bg-gray-500'"
                    :style="viewMode === 'grid' ? { backgroundColor: theme.primary } : undefined"
                    @click="viewMode = 'grid'"
                >Grid</button>
                <button
                    type="button"
                    class="rounded-xl px-4 py-2 font-semibold text-white"
                    :class="viewMode === 'table' ? 'hover:opacity-90' : 'bg-gray-600 hover:bg-gray-500'"
                    :style="viewMode === 'table' ? { backgroundColor: theme.primary } : undefined"
                    @click="viewMode = 'table'"
                >Table</button>
            </div>
        </div>
        <p v-if="savedMovies.length === 0" class="text-gray-500">
            You have not saved any movies yet.
        </p>
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            <MovieCard
                v-for="movie in savedMovies"
                :key="movie.id ?? movie.imdbId"
                :movie-or-movie-id="movie"
                size="medium"
                @click="goToMoviePage(movie)"
            />
        </div>
        <div v-else class="overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-950">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Title</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Saved By</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Year</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Genre</th>
                        <th class="px-4 py-3 text-left text-sm font-semibold">Rating</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-800">
                    <tr
                        v-for="movie in savedMovies"
                        :key="movie.id ?? movie.imdbId"
                        class="cursor-pointer transition hover:bg-gray-50 dark:hover:bg-gray-900"
                        @click="goToMoviePage(movie)"
                    >
                        <td class="px-4 py-3 text-sm font-medium">{{ movie.title ?? 'Untitled' }}</td>
                        <td class="px-4 py-3 text-sm">{{ movie.savedByUserName ?? 'Unknown user' }}</td>
                        <td class="px-4 py-3 text-sm">{{ movie.year ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">{{ movie.genre ?? 'N/A' }}</td>
                        <td class="px-4 py-3 text-sm">{{ movie.imdbRating ?? 'N/A' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
