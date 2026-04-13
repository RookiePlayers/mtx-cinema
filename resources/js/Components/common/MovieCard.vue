<script lang="ts" setup>
import { computed } from 'vue';
import type { Movie } from '@/types/movies';

    const props = defineProps<{
        movieOrMovieId: Movie | string;
        size?: 'small' | 'medium' | 'large';
    }>();
    const movie = computed<Movie | null>(() => {
        if (typeof props.movieOrMovieId === 'string') {
            // Fetch the movie by ID or handle it as needed
            return null; // Placeholder, implement fetching logic if necessary
        }

        return props.movieOrMovieId;
    });
    const sizeClasses = computed(() => {
        switch (props.size) {
            case 'small':
                return 'w-24 h-36';
            case 'medium':
                return 'w-32 h-48';
            case 'large':
                return 'w-48 h-72';
            default:
                return 'w-32 h-48';
        }
    });
</script>
<template>
    <div v-if="movie" :class="`movie-card relative overflow-hidden rounded-lg ${sizeClasses}`">
        <img
            :src="movie.poster ?? 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRf-4Z90uQbyo0ToHU-s_CsHuFuLN8e-2JRtQ&s'"
            :alt="movie.title"
            :class="`movie-poster rounded-lg object-cover ${sizeClasses}`"
        />
            <div class="movie-rating text-sm font-semibold text-yellow-400 absolute top-3 left-3 bg-gray-900/80 px-2 py-1 rounded">
                {{ movie.imdbRating }}
            </div>
            <!-- <div class="movie-title mt-2 text-lg font-bold text-white absolute bottom-3 right-3 bg-gray-900/80 px-2 py-1 rounded">
                {{ movie.title }}
            </div> -->
    </div>
    <div v-else class="movie-card-placeholder" />
</template>
