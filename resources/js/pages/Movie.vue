<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AuthTextField from '@/Components/auth/AuthTextField.vue';
import MovieCard from '@/Components/common/MovieCard.vue';
import { resolveMovieId } from '@/helpers/normalizeKeys';
import { theme } from '@/theme';
import type { Auth } from '@/types';
import type { Movie } from '@/types/movies';

const props = defineProps<{
    movie?: Movie | null;
    movieId: string;
    notFound?: boolean;
    isSaved?: boolean;
}>();

const page = usePage<{ auth: Auth }>();
const user = computed(() => page.props.auth.user);
const movie = computed(() => props.movie ? (props.movie) : null);
const pageTitle = computed(() => movie.value?.title ?? 'Movie');
const saveForm = useForm({});
const isEditing = ref(false);
const movieId = computed(() => {
    return resolveMovieId(movie.value, props.movieId);
});

const editForm = useForm({
    title: movie.value?.title ?? '',
    year: movie.value?.year ?? '',
    rated: movie.value?.rated ?? '',
    runtime: movie.value?.runtime ?? '',
    genre: movie.value?.genre ?? '',
    actors: movie.value?.actors ?? '',
    plot: movie.value?.plot ?? '',
    poster: movie.value?.poster ?? '',
    languages: movie.value?.languages ?? '',
    imdbRating: movie.value?.imdbRating ?? '',
});

watch(movie, (currentMovie) => {
    editForm.defaults({
        title: currentMovie?.title ?? '',
        year: currentMovie?.year ?? '',
        rated: currentMovie?.rated ?? '',
        runtime: currentMovie?.runtime ?? '',
        genre: currentMovie?.genre ?? '',
        actors: currentMovie?.actors ?? '',
        plot: currentMovie?.plot ?? '',
        poster: currentMovie?.poster ?? '',
        languages: currentMovie?.languages ?? '',
        imdbRating: currentMovie?.imdbRating ?? '',
    });
    editForm.reset();
}, { immediate: true });

const toggleSavedMovie = () => {
    if (!movie.value) {
        return;
    }

    if(!movieId.value) {
        console.error('Movie ID is missing, cannot toggle saved movie status.');

        return;
    }

    if (props.isSaved) {
        saveForm.delete(`/movies/${movieId.value}/save`, {
            preserveScroll: true,
        });

        return;
    }

    console.log('Saving movie with ID:', movieId.value);
    saveForm.post(`/movies/${movieId.value}/save`, {
        preserveScroll: true,
    });
};

const startEditing = () => {
    isEditing.value = true;
};

const cancelEditing = () => {
    editForm.reset();
    editForm.clearErrors();
    isEditing.value = false;
};

const submitEdit = () => {
    if (!movie.value) {
        return;
    }

    editForm.put(`/movies/${movieId.value}`, {
        preserveScroll: true,
        onSuccess: () => {
            isEditing.value = false;
        },
    });
};
</script>
<template>
    <Head :title="pageTitle" />
    <div class="flex min-h-screen w-full flex-row items-center justify-center bg-[#FDFDFC] p-6 text-[#111827] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <div v-if="movie" class="flex flex-row items-start">
            <MovieCard :movie-or-movie-id="movie" size="large" />
            <div class="ml-8 max-w-lg">
                <div class="mb-4 flex items-center gap-3">
                    <button
                        v-if="user"
                        type="button"
                        class="rounded-xl px-4 py-2 font-semibold text-white"
                        :class="props.isSaved ? 'bg-emerald-600 hover:bg-emerald-500' : 'bg-gray-900 hover:bg-gray-700'"
                        :disabled="saveForm.processing"
                        @click="toggleSavedMovie"
                    >
                        {{ props.isSaved ? 'Remove from saved movies' : 'Save movie' }}
                    </button>
                    <button
                        v-if="user && !isEditing"
                        type="button"
                        class="rounded-xl px-4 py-2 font-semibold text-white"
                        :style="{ backgroundColor: theme.primary }"
                        @click="startEditing"
                    >
                        Edit
                    </button>
                </div>

                <div v-if="isEditing" class="space-y-4">
                    <AuthTextField id="edit-title" v-model="editForm.title" label="Title" :error="editForm.errors.title" />
                    <div class="grid gap-4 md:grid-cols-2">
                        <AuthTextField id="edit-year" v-model="editForm.year" label="Year" :error="editForm.errors.year" />
                        <AuthTextField id="edit-rated" v-model="editForm.rated" label="Rated" :error="editForm.errors.rated" />
                    </div>
                    <div class="grid gap-4 md:grid-cols-2">
                        <AuthTextField id="edit-runtime" v-model="editForm.runtime" label="Runtime" :error="editForm.errors.runtime" />
                        <AuthTextField id="edit-imdb-rating" v-model="editForm.imdbRating" label="IMDb Rating" :error="editForm.errors.imdbRating" />
                    </div>
                    <AuthTextField id="edit-genre" v-model="editForm.genre" label="Genre" :error="editForm.errors.genre" />
                    <AuthTextField id="edit-actors" v-model="editForm.actors" label="Actors" :error="editForm.errors.actors" />
                    <AuthTextField id="edit-languages" v-model="editForm.languages" label="Languages" :error="editForm.errors.languages" />
                    <AuthTextField id="edit-poster" v-model="editForm.poster" label="Poster URL" :error="editForm.errors.poster" />
                    <div>
                        <label class="mb-2 block text-sm font-medium" for="edit-plot">Plot</label>
                        <textarea
                            id="edit-plot"
                            v-model="editForm.plot"
                            rows="5"
                            class="block w-full rounded-2xl border border-gray-800 bg-gray-800/60 px-4 py-3 outline-none"
                        />
                        <p v-if="editForm.errors.plot" class="mt-1 text-sm text-red-500">{{ editForm.errors.plot }}</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            class="rounded-xl px-4 py-2 font-semibold text-white"
                            :style="{ backgroundColor: theme.primary }"
                            :disabled="editForm.processing"
                            @click="submitEdit"
                        >
                            Save changes
                        </button>
                        <button
                            type="button"
                            class="rounded-xl bg-gray-600 px-4 py-2 font-semibold text-white hover:bg-gray-500"
                            :disabled="editForm.processing"
                            @click="cancelEditing"
                        >
                            Cancel
                        </button>
                    </div>
                </div>

                <div v-else>
                    <h1 class="text-4xl font-bold mb-4">{{ movie.title }} ({{ movie.year }})</h1>
                    <p class="text-lg mb-2"><strong>Rated:</strong> {{ movie.rated }}</p>
                    <p class="text-lg mb-2"><strong>Runtime:</strong> {{ movie.runtime }}</p>
                    <p class="text-lg mb-2"><strong>Genre:</strong> {{ movie.genre }}</p>
                    <p class="text-lg mb-2"><strong>IMDB Rating:</strong> {{ movie.imdbRating }}</p>
                    <p class="text-lg mb-2"><strong>Actors:</strong> {{ movie.actors }}</p>
                    <p class="text-lg mb-2"><strong>Plot:</strong> {{ movie.plot }}</p>
                </div>
            </div>
        </div>
        <div v-else-if="notFound" class="text-center">
            <h1 class="text-3xl font-bold mb-3">Movie not found</h1>
            <p class="text-gray-500">No movie was found for ID {{ movieId }}.</p>
        </div>
        <div v-else class="text-gray-500">Loading movie details...</div>

    </div>
</template>
<style scoped></style>
