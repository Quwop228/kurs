<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    articleSlug: {
        type: String,
        required: true,
    },
    isFavorited: {
        type: Boolean,
        default: false,
    },
});

const loading = ref(false);

const toggle = () => {
    loading.value = true;
    router.post(`/articles/${props.articleSlug}/favorite`, {}, {
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};
</script>

<template>
    <button
        @click="toggle"
        :disabled="loading"
        :class="[
            'inline-flex items-center gap-2 px-3 py-1.5 font-mono text-sm font-bold border-2 transition-all',
            isFavorited
                ? 'border-ink-900 dark:border-paper bg-brand-700 text-paper shadow-hard-sm'
                : 'border-ink-900 dark:border-paper bg-paper-50 dark:bg-ink-900 text-ink-900 dark:text-paper shadow-hard-sm hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-hard',
            loading && 'opacity-60 cursor-wait'
        ]"
    >
        <span class="text-base leading-none" :class="{ 'scale-110': loading }">
            {{ isFavorited ? '♥' : '♡' }}
        </span>
        <span>{{ isFavorited ? 'в избранном' : 'в избранное' }}</span>
    </button>
</template>
