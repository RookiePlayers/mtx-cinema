import { normalizeKeys } from "@/helpers/normalizeKeys";
import type { PaginationData, PaginationInput} from "@/types";
import { paginationInputToQueryParams } from "@/types";
import type { Movie} from "@/types/movies";
import { moviePaginationDataSchema } from "@/types/movies";

export const useMovies = () => {
    const jsonHeaders = {
        Accept: 'application/json',
    };

    const searchMovies = async (query: string, pagination: PaginationInput): Promise<PaginationData<Movie>> => {
        const response = await fetch(`/movie-search?query=${encodeURIComponent(query)}&${paginationInputToQueryParams(pagination)}`, {
            headers: jsonHeaders,
        });

        if (!response.ok) {
            console.error('Failed to fetch search results');

            return { data: [], nextCursor: null, count: 0, total: 0, hasMore: false };
        }

        const data = normalizeKeys(await response.json());
        console.log('Search results:', data);

        const { data: parsedData, error } = moviePaginationDataSchema.safeParse(data);

        if (error) {
            console.error('Failed to parse search results', error);

            return { data: [], nextCursor: null, count: 0, total: 0, hasMore: false };
        }

        return parsedData;
    }

    const autocompleteSearch = async (value: string):Promise<Movie[]>=>{
        if(value.length < 3) {
            return []
        }

        const response = await fetch(`/autocomplete-search?query=${encodeURIComponent(value)}`, {
            headers: jsonHeaders,
        });

        if (!response.ok) {
            console.error('Failed to fetch autocomplete search results');

            return [];
        }

        const data = normalizeKeys(await response.json());

        const { data: parsedData, error } = moviePaginationDataSchema.safeParse(data);

        if (error) {
            console.error('Failed to parse autocomplete search results', error);

            return [];
        }

        return parsedData.data;
    }


    return { autocompleteSearch, searchMovies };
}
