import z from 'zod';
export * from './auth';


export const paginationDataSchema = z.object({
    data: z.array(z.unknown()),
    nextCursor: z.string().nullable(),
    count: z.number(),
    total: z.number(),
    hasMore: z.boolean(),
});

export type PaginationInput = {
    cursor?: string | null;
    limit: number;
    offset: number;
}
export const paginationInputToQueryParams = (input: PaginationInput): string => {
    const params = new URLSearchParams();

    if (input.cursor) {
        params.append('cursor', input.cursor);
    }

    params.append('limit', input.limit.toString());
    params.append('offset', input.offset.toString());

    return params.toString();
}
export type PaginationData<T> = {
    data: T[];
    nextCursor: string | null;
    count: number;
    total: number;
    hasMore: boolean;
}
