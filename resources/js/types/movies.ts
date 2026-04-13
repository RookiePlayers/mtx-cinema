import { z } from 'zod';
export const movieSchema = z.object({
    id: z.number().optional(),
    imdbId: z.string(),
    title: z.string().optional(),
    year: z.string().optional(),
    rated: z.string().optional(),
    runtime: z.string().optional(),
    genre: z.string().optional(),
    actors: z.string().optional(),
    plot: z.string().optional(),
    poster: z.string().optional(),
    languages: z.string().optional(),
    imdbRating: z.string().optional(),
});

export const moviePaginationDataSchema = z.object({
    data: z.array(movieSchema),
    nextCursor: z.string().nullable().default(null),
    count: z.number(),
    total: z.number(),
    hasMore: z.boolean(),
});

export type Movie = z.infer<typeof movieSchema>;
export type MoviePaginationData = z.infer<typeof moviePaginationDataSchema>;
