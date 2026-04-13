<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AuthTextField from '@/Components/auth/AuthTextField.vue';
import { theme } from '@/theme';

const form = useForm({
    title: '',
    imdbId: '',
    year: '',
    rated: '',
    runtime: '',
    genre: '',
    actors: '',
    plot: '',
    poster: '',
    languages: '',
    imdbRating: '',
});

const submit = () => {
    form.post('/movies');
};
</script>
<template>
    <Head title="Movie Manager" />
    <div class="flex min-h-screen w-full justify-center bg-[#FDFDFC] p-6 text-[#111827] lg:p-8 dark:bg-[#0a0a0a] dark:text-[#F9FAFB]">
        <div class="w-full max-w-3xl rounded-3xl border border-gray-200 bg-white p-8 shadow-sm dark:border-gray-800 dark:bg-gray-950">
            <h1 class="text-3xl font-bold">Create Movie Entry</h1>
            <p class="mt-2 text-sm text-gray-500">
                Add a custom movie directly to the catalog. `IMDb ID` is optional for manual entries.
            </p>

            <form class="mt-8 space-y-4" @submit.prevent="submit">
                <AuthTextField
                    id="movie-title"
                    v-model="form.title"
                    label="Title"
                    :error="form.errors.title"
                />

                <div class="grid gap-4 md:grid-cols-2">
                    <AuthTextField
                        id="movie-imdb-id"
                        v-model="form.imdbId"
                        label="IMDb ID"
                        :error="form.errors.imdbId"
                    />
                    <AuthTextField
                        id="movie-year"
                        v-model="form.year"
                        label="Year"
                        :error="form.errors.year"
                    />
                </div>

                <div class="grid gap-4 md:grid-cols-3">
                    <AuthTextField
                        id="movie-rated"
                        v-model="form.rated"
                        label="Rated"
                        :error="form.errors.rated"
                    />
                    <AuthTextField
                        id="movie-runtime"
                        v-model="form.runtime"
                        label="Runtime"
                        :error="form.errors.runtime"
                    />
                    <AuthTextField
                        id="movie-imdb-rating"
                        v-model="form.imdbRating"
                        label="IMDb Rating"
                        :error="form.errors.imdbRating"
                    />
                </div>

                <AuthTextField
                    id="movie-genre"
                    v-model="form.genre"
                    label="Genre"
                    :error="form.errors.genre"
                />

                <AuthTextField
                    id="movie-actors"
                    v-model="form.actors"
                    label="Actors"
                    :error="form.errors.actors"
                />

                <AuthTextField
                    id="movie-languages"
                    v-model="form.languages"
                    label="Languages"
                    :error="form.errors.languages"
                />

                <AuthTextField
                    id="movie-poster"
                    v-model="form.poster"
                    label="Poster URL"
                    :error="form.errors.poster"
                />

                <div>
                    <label class="mb-2 block text-sm font-medium" for="movie-plot">Plot</label>
                    <textarea
                        id="movie-plot"
                        v-model="form.plot"
                        rows="5"
                        class="block w-full rounded-2xl border border-gray-800 bg-gray-800/60 px-4 py-3 outline-none"
                    />
                    <p v-if="form.errors.plot" class="mt-1 text-sm text-red-500">{{ form.errors.plot }}</p>
                </div>

                <button
                    type="submit"
                    class="w-full rounded-2xl px-4 py-3 font-semibold text-white"
                    :style="{ backgroundColor: theme.primary }"
                    :disabled="form.processing"
                >
                    Create movie
                </button>
            </form>
        </div>
    </div>
</template>
