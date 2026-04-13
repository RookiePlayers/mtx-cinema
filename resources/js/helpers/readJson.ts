import type { PaginationData, PaginationInput } from '@/types';
import { normalizeKeys } from './normalizeKeys';

const jsonModules = import.meta.glob('../**/*.json');

const resolveJsonPath = (path: string): string => {
    if (path.startsWith('../')) {
        return path;
    }

    if (path.startsWith('@/')) {
        return `..${path.slice(1)}`;
    }

    if (path.startsWith('/resources/js/')) {
        return `..${path.replace('/resources/js', '')}`;
    }

    if (path.startsWith('resources/js/')) {
        return `..${path.replace('resources/js', '')}`;
    }

    return path.startsWith('/') ? `..${path}` : `../${path}`;
};

export const readJson = async <T>(path: string, pagination?: PaginationInput): Promise<T> => {
    const resolvedPath = resolveJsonPath(path);
    const loader = jsonModules[resolvedPath];

    if (!loader) {
        throw new Error(`JSON file not found: ${path}`);
    }

    const module = (await loader()) as { default: T };

    if (pagination) {
        const items = module.default;

        if (!Array.isArray(items)) {
            throw new Error(
                `Paginated JSON must resolve to an array: ${path}`,
            );
        }

        const { cursor, limit, offset } = pagination;
        const startIndex = cursor ? Number.parseInt(cursor, 10) : offset;

        if (Number.isNaN(startIndex)) {
            throw new Error(`Invalid pagination cursor: ${cursor}`);
        }

        const data = items.slice(startIndex, startIndex + limit);
        const nextIndex = startIndex + data.length;
        const total = items.length;
        const normalizedData = normalizeKeys(data);
        console.log('Normalized Data:', normalizedData);
        
        return {
            data: normalizedData,
            nextCursor: nextIndex < total ? String(nextIndex) : null,
            count: normalizedData.length,
            total,
            hasMore: nextIndex < total,
        } as T & PaginationData<unknown>;
    }

    return module.default;
};
