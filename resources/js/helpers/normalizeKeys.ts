import type { Movie } from "@/types/movies";

export const normalizeKeys = <T>(data: T): T => {
    if (Array.isArray(data)) {
        return data.map((item) => normalizeKeys(item)) as unknown as T;
    } else if (data !== null && typeof data === 'object') {
        const normalizedObject: Record<string, unknown> = {};

        for (const key in data) {
            if (Object.prototype.hasOwnProperty.call(data, key)) {
                const normalizedKey = toCamelCase(key);
                normalizedObject[normalizedKey] = normalizeKeys((data as Record<string, unknown>)[key]);
            }
        }

        return normalizedObject as T;
    }

    return data;
}

export const resolveMovieId = (movie: Movie | null, defaultId?: string | null): string | null => {
    if(!movie) {
        return defaultId ?? null; // Return defaultId if movie is null
    }

     const movieStrigified = JSON.parse(JSON.stringify(movie)); // Handle both cases just in case

    const movieId = movieStrigified ? movieStrigified['imdbId'] : movieStrigified['imdbID']; // Handle both cases just in case
    console.log('Resolved movie ID:',movieStrigified, movieId);

    return movieId ?? defaultId ?? null; // Fallback to defaultId if movie is not available

}

const toCamelCase = (str: string): string => {
    str = str[0].toLowerCase() + str.slice(1);

    return str.replace(/[-_](\w)/g, (_, char) => char.toUpperCase());

};

