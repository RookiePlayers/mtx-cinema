<script setup lang="ts">
const model = defineModel<string>({ required: true });

defineProps<{
    id: string;
    label: string;
    type?: string;
    autocomplete?: string;
    error?: string;
}>();
</script>

<template>
    <div>
        <label class="mb-2 block text-sm font-medium" :for="id">{{ label }}</label>
        <input
            :id="id"
            v-model="model"
            :type="type ?? 'text'"
            :autocomplete="autocomplete"
            class="block w-full rounded-2xl border border-gray-800 bg-gray-800/60 px-4 py-3 outline-none"
        />
        <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
    </div>
</template>
