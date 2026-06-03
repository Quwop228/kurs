<script setup>
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    dailyUpdate: {
        type: Object,
        default: null,
    },
    articleSlug: {
        type: String,
        required: true,
    },
});

const loading = ref(false);

const isUpdatedToday = computed(() => {
    if (!props.dailyUpdate) return false;
    const updateDate = new Date(props.dailyUpdate.created_at).toDateString();
    const today = new Date().toDateString();
    return updateDate === today;
});

const sources = computed(() => {
    const raw = props.dailyUpdate?.sources_json ?? props.dailyUpdate?.sources ?? [];
    return Array.isArray(raw) ? raw : [];
});

const sourceUrl = (s) => typeof s === 'string' ? s : (s?.url ?? '#');
const sourceLabel = (s) => {
    if (typeof s === 'string') return s;
    return s?.title || s?.source || s?.url || '';
};
const sourceMeta = (s) => {
    if (typeof s === 'string') return '';
    const parts = [];
    if (s?.source) parts.push(s.source);
    if (s?.published_at) {
        const d = new Date(s.published_at);
        if (!isNaN(d.getTime())) {
            parts.push(`${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`);
        }
    }
    return parts.join(' · ');
};

const formatDate = (date) => {
    const d = new Date(date);
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(d.getHours())}:${pad(d.getMinutes())}`;
};

const requestUpdate = () => {
    loading.value = true;
    router.post(`/articles/${props.articleSlug}/daily-update`, {}, {
        preserveScroll: true,
        onFinish: () => {
            loading.value = false;
        },
    });
};
</script>

<template>
    <div class="border-2 border-ink-900 dark:border-brand-400 bg-ink-900 dark:bg-ink-950 text-paper shadow-hard-brand">
        <div class="flex items-center justify-between px-3 py-1.5 bg-brand-700 border-b-2 border-ink-900 dark:border-brand-400 font-mono text-[11px] text-paper">
            <div class="flex items-center gap-2">
                <span class="flex gap-1">
                    <span class="w-2.5 h-2.5 rounded-full bg-red-400 border border-ink-900"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-300 border border-ink-900"></span>
                    <span class="w-2.5 h-2.5 rounded-full bg-brand-300 border border-ink-900"></span>
                </span>
                <span class="font-bold tracking-wider">ai_daily.sh</span>
            </div>
            <span v-if="dailyUpdate" class="text-brand-100">{{ formatDate(dailyUpdate.created_at) }}</span>
            <span v-else class="text-brand-100">not_run</span>
        </div>

        <div class="p-5 font-mono text-sm">
            <div class="text-brand-300 mb-3">
                <span class="text-brand-400">$</span> ai --daily-update --topic="article"
            </div>

            <div v-if="dailyUpdate" class="text-ink-50 leading-relaxed whitespace-pre-wrap mb-5 pl-3 border-l-2 border-brand-400">
                {{ dailyUpdate.content }}
            </div>
            <div v-else class="text-ink-200 italic mb-5 pl-3 border-l-2 border-dashed border-ink-700">
                // ежедневное обновление для этой статьи ещё не создано
            </div>

            <div v-if="sources.length" class="mb-5">
                <div class="text-brand-300 mb-2 text-[11px] uppercase tracking-wider">
                    <span class="text-brand-400">//</span> sources ({{ sources.length }})
                </div>
                <ul class="space-y-1.5 pl-3 border-l-2 border-dashed border-brand-400/60">
                    <li v-for="(src, idx) in sources" :key="idx" class="text-[12px] leading-snug">
                        <span class="text-brand-400 mr-1">[{{ idx + 1 }}]</span>
                        <a
                            :href="sourceUrl(src)"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="text-brand-200 hover:text-paper underline underline-offset-2 decoration-brand-400/60 hover:decoration-paper break-all"
                        >{{ sourceLabel(src) }}</a>
                        <span v-if="sourceMeta(src)" class="text-ink-300 ml-1">· {{ sourceMeta(src) }}</span>
                    </li>
                </ul>
            </div>

            <button
                @click="requestUpdate"
                :disabled="loading || isUpdatedToday"
                :class="[
                    'inline-flex items-center gap-2 px-4 py-2 font-mono text-sm font-bold border-2 transition-all',
                    isUpdatedToday
                        ? 'border-brand-400 bg-brand-900/40 text-brand-300 cursor-default'
                        : loading
                            ? 'border-brand-400 bg-ink-800 text-brand-300 cursor-wait'
                            : 'border-brand-400 bg-brand-700 text-paper hover:bg-brand-600 shadow-[3px_3px_0_0_rgba(167,243,208,1)] active:shadow-none active:translate-x-[3px] active:translate-y-[3px]'
                ]"
            >
                <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span v-else-if="isUpdatedToday">[✓]</span>
                <span v-else>&gt;</span>
                <span>{{ loading ? 'running...' : isUpdatedToday ? 'updated_today' : 'run_update' }}</span>
            </button>

            <div class="mt-3 text-[11px] text-ink-200">
                // доступно 1 раз в сутки · powered by openrouter
            </div>
        </div>
    </div>
</template>
