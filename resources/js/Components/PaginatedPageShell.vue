<script lang="ts" setup>
import { useRemember } from '@inertiajs/vue3';
import { computed, ref, watch  } from 'vue';
import type {Ref} from 'vue';
import { usePaginatedTask  } from '@/hooks/useFetch';
import type {PaginatedTask} from '@/hooks/useFetch';
import { theme } from '@/theme';
import type { PaginationInput } from '@/types';

const props = withDefaults(
    defineProps<{
        task: PaginatedTask<any[], any>;
        args?: any[];
        pageSize?: number;
        rememberKey?: string;
    }>(),
    {
        args: () => [],
        pageSize: 10,
        rememberKey: undefined,
    },
);

defineSlots<{
    default?: (props: {
        items: unknown[];
        data: ReturnType<typeof usePaginatedTask<unknown[], unknown>>['data']['value'];
        loading: boolean;
        error: Error | null;
        page: number;
        totalPages: number;
        hasMore: boolean;
        loadNextPage: () => Promise<void>;
        reload: () => Promise<void>;
    }) => unknown;
}>();

const createInitialState = () => ({
    page: 1,
    items: [] as unknown[],
    initialized: false,
});

type RememberedState = ReturnType<typeof createInitialState>;

const state: Ref<RememberedState> = props.rememberKey
    ? useRemember(createInitialState(), props.rememberKey) as Ref<RememberedState>
    : ref(createInitialState());

const page = computed({
    get: () => state.value.page,
    set: (value: number) => {
        state.value.page = value;
    },
});

const items = computed({
    get: () => state.value.items,
    set: (value: unknown[]) => {
        state.value.items = value;
    },
});

const { exec, data, loading, error } = usePaginatedTask(props.task);
let didHydrate = false;

const totalPages = computed(() => {
    if (!data.value) {
        return 1;
    }

    return Math.max(1, Math.ceil(data.value.total / props.pageSize));
});

const hasMore = computed(() => data.value?.hasMore ?? false);

const buildPaginationInput = (): PaginationInput => ({
    cursor: data.value?.nextCursor ?? null,
    offset: (page.value - 1) * props.pageSize,
    limit: props.pageSize,
});

const loadPage = async (nextPage: number, append: boolean) => {
    page.value = nextPage;
    await exec(buildPaginationInput(), ...props.args);
    const nextItems = data.value?.data ?? [];
    items.value = append ? [...items.value, ...nextItems] : nextItems;
    state.value.initialized = true;
};

const reload = async () => {
    items.value = [];
    state.value.initialized = false;
    await loadPage(1, false);
};

const loadNextPage = async () => {
    if (!hasMore.value) {
        return;
    }

    await loadPage(page.value + 1, true);
};

watch(
    () => props.args,
    async () => {
        if (!didHydrate && state.value.initialized) {
            didHydrate = true;

            return;
        }

        didHydrate = true;
        await reload();
    },
    { deep: true, immediate: true },
);
</script>

<template>
    <div class="container mx-auto py-8 w-full h-full h-min-screen">
        <div v-if="loading" class="text-center text-gray-500 h-full">Loading...</div>
        <div v-else-if="error" class="text-center text-red-500">Error loading data: {{ error.message }}</div>
        <div v-else-if="items.length === 0" class="text-center text-gray-500">No items found.</div>

        <slot
            :items="items"
            :data="data"
            :loading="loading"
            :error="error"
            :page="page"
            :total-pages="totalPages"
            :has-more="hasMore"
            :load-next-page="loadNextPage"
            :reload="reload"
        />
        <div class="pages-button flex row-auto items-center justify-center mt-6 gap-2 max-w-full">
            <div v-if="totalPages > 1" class="max-w-md">
            <button
                v-for="p in totalPages"
                :key="p"
                @click="loadPage(p, false)"
                :class="[
                    'px-3 py-1 rounded m-1',
                    p === page ? `bg-(--mtx-primary-color) text-white` : 'bg-gray-200 text-gray-700 hover:bg-gray-300',
                ]"
                :style="{
                    '--mtx-primary-color': theme.primary,
                }"
            >
                {{ p }}
            </button>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
