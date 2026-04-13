import { ref } from 'vue';
import type { PaginationData, PaginationInput } from '@/types';

export type Task<TArgs extends unknown[] = unknown[], TResult = unknown> = (
    ...args: TArgs
) => Promise<TResult>;
export type PaginatedTask<TArgs extends unknown[] = unknown[], TResult = unknown> = (
    pagination: PaginationInput,
    ...args: TArgs
) => Promise<PaginationData<TResult>>;

export function usePaginatedTask<TArgs extends unknown[], TResult>(
    task: PaginatedTask<TArgs, TResult>,
) {
    const data = ref<PaginationData<TResult> | null>(null);
    const error = ref<Error | null>(null);
    const loading = ref(false);
    const exec = async (pagination: PaginationInput, ...args: TArgs) => {
        loading.value = true;

        try {
            data.value = await task(pagination, ...args);
        } catch (err) {
            error.value = err as Error;
        } finally {
            loading.value = false;
        }
    };

    return { data, error, loading, exec };
}

export function useFuture<TArgs extends unknown[], TResult>(task: Task<TArgs, TResult>) {
    const data = ref<TResult | null>(null);
    const error = ref<Error | null>(null);
    const loading = ref(false);
    const exec = async (...args: TArgs) => {
        loading.value = true;

        try {
            data.value = await task(...args);
        } catch (err) {
            error.value = err as Error;
        } finally {
            loading.value = false;
        }
    };

    return { data, error, loading, exec };
}

export default function useFetch<T>(url: string, options?: RequestInit) {
    const data = ref<T | null>(null);
    const error = ref<Error | null>(null);
    const loading = ref(false);

    const exec = async () => {
        loading.value = true;

        try {
            const response = await fetch(url, options);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            data.value = await response.json();
        } catch (err) {
            error.value = err as Error;
        } finally {
            loading.value = false;
        }
    };

    return { data, error, loading, exec };
}
