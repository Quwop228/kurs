<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const props = defineProps({
    articleSlug: { type: String, required: true },
    articleId: { type: Number, required: true },
    explanation: { type: Object, default: null },
    isAdmin: { type: Boolean, default: false },
})

const emit = defineEmits(['close'])

const currentStep = ref(0)
const readSteps = ref(new Set())
const loading = ref(false)
const error = ref(null)
const data = ref(props.explanation)
const expandedPoints = ref(new Set())

const steps = computed(() => data.value?.steps || [])
const totalSteps = computed(() => steps.value.length)
const progress = computed(() => totalSteps.value ? Math.round((readSteps.value.size / totalSteps.value) * 100) : 0)
const currentStepData = computed(() => steps.value[currentStep.value] || null)

const delay = (ms) => new Promise((r) => setTimeout(r, ms))

// Ждём, пока фоновая задача сгенерирует объяснение (макс ~5 минут).
async function pollExplanation() {
    const deadline = Date.now() + 5 * 60 * 1000
    while (Date.now() < deadline) {
        await delay(2500)
        const res = await axios.get(`/articles/${props.articleSlug}/interactive/status`)
        const payload = res.data
        if (payload.status === 'done') return payload.explanation
        if (payload.status === 'failed') throw new Error(payload.error || 'Генерация не удалась.')
        // status === 'processing' — ждём дальше
    }
    throw new Error('Генерация заняла слишком много времени. Попробуйте позже.')
}

async function fetchExplanation(regenerate = false) {
    loading.value = true
    error.value = null
    try {
        const url = regenerate
            ? `/articles/${props.articleSlug}/interactive/regenerate`
            : `/articles/${props.articleSlug}/interactive`
        const response = await axios.post(url)

        // Если объяснение уже готово — отдаётся сразу; иначе ставится в очередь (202) и опрашиваем статус.
        const explanation = response.data.explanation || await pollExplanation()
        data.value = explanation

        if (regenerate) {
            currentStep.value = 0
            readSteps.value = new Set()
            clearProgress()
        }
    } catch (e) {
        error.value = e.response?.data?.error
            || e.message
            || 'Не удалось сгенерировать объяснение. Попробуйте позже.'
    } finally {
        loading.value = false
    }
}

function nextStep() {
    readSteps.value.add(currentStep.value)
    expandedPoints.value.clear()
    if (currentStep.value < totalSteps.value - 1) {
        currentStep.value++
    }
    saveProgress()
}

function prevStep() {
    expandedPoints.value.clear()
    if (currentStep.value > 0) currentStep.value--
}

function goToStep(i) {
    expandedPoints.value.clear()
    currentStep.value = i
}

function togglePoints() {
    if (expandedPoints.value.has(currentStep.value)) {
        expandedPoints.value.delete(currentStep.value)
    } else {
        expandedPoints.value.add(currentStep.value)
    }
}

function saveProgress() {
    const key = `encyclopedia_interactive_${props.articleId}`
    localStorage.setItem(key, JSON.stringify({
        step: currentStep.value,
        read: [...readSteps.value],
    }))
}

function loadProgress() {
    const key = `encyclopedia_interactive_${props.articleId}`
    const saved = localStorage.getItem(key)
    if (saved) {
        const parsed = JSON.parse(saved)
        currentStep.value = parsed.step || 0
        readSteps.value = new Set(parsed.read || [])
    }
}

function clearProgress() {
    localStorage.removeItem(`encyclopedia_interactive_${props.articleId}`)
}

onMounted(() => {
    if (data.value) {
        loadProgress()
    } else {
        fetchExplanation()
    }
})
</script>

<template>
    <div class="bg-paper-50 dark:bg-ink-900 border-2 border-ink-900 dark:border-brand-400 shadow-hard overflow-hidden">
        <div class="bg-brand-700 text-paper border-b-2 border-ink-900 dark:border-brand-400">
            <div class="flex items-center justify-between px-3 py-1.5 font-mono text-[11px] border-b-2 border-brand-800 dark:border-brand-500">
                <div class="flex items-center gap-2">
                    <span class="flex gap-1">
                        <span class="w-2.5 h-2.5 rounded-full bg-red-400 border border-ink-900"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-300 border border-ink-900"></span>
                        <span class="w-2.5 h-2.5 rounded-full bg-brand-300 border border-ink-900"></span>
                    </span>
                    <span class="font-bold tracking-wider">ai_explain.sh</span>
                </div>
                <div class="flex items-center gap-2">
                    <button v-if="isAdmin && data && !loading" @click="fetchExplanation(true)"
                        class="hover:text-brand-100 underline-offset-2 hover:underline" title="Сгенерировать заново">
                        [regen]
                    </button>
                    <button @click="emit('close')" class="hover:text-red-200 hover:underline underline-offset-2">
                        [close]
                    </button>
                </div>
            </div>
            <div class="px-5 py-4">
                <div class="font-mono text-xs text-brand-200 mb-1">
                    <span class="text-brand-300">$</span> ai --explain --steps --simple
                </div>
                <h3 class="font-serif text-xl font-bold">Интерактивное объяснение</h3>
                <p v-if="data && !loading" class="font-mono text-xs text-brand-200 mt-1">
                    step {{ currentStep + 1 }} / {{ totalSteps }} — простым языком
                </p>
                <p v-else-if="loading" class="font-mono text-xs text-brand-200 mt-1">loading...</p>

                <p v-if="data?.summary && !loading" class="mt-3 font-mono text-sm text-brand-50 bg-brand-800 border-2 border-brand-900 p-3 leading-relaxed">
                    <span class="text-brand-300">// tl;dr:</span> {{ data.summary }}
                </p>
            </div>
        </div>

        <div v-if="loading" class="p-12 text-center font-mono text-sm">
            <div class="inline-flex flex-col items-center gap-4">
                <div class="relative w-14 h-14">
                    <div class="absolute inset-0 border-2 border-dashed border-ink-300 dark:border-ink-700 animate-spin" style="animation-duration: 3s;"></div>
                    <div class="absolute inset-2 border-2 border-brand-700 dark:border-brand-400 animate-pulse"></div>
                </div>
                <div>
                    <p class="text-ink-900 dark:text-paper font-bold">&gt; ai analyzing article...</p>
                    <p class="text-xs text-ink-700 dark:text-ink-100 mt-1">// разбиваем сложное на простое</p>
                </div>
            </div>
        </div>

        <div v-else-if="error" class="p-8 text-center">
            <div class="inline-flex flex-col items-center gap-3 font-mono">
                <div class="w-12 h-12 border-2 border-red-700 bg-red-100 dark:bg-red-900/40 flex items-center justify-center text-red-700 dark:text-red-300 text-xl font-bold">!</div>
                <p class="text-sm text-ink-900 dark:text-paper">{{ error }}</p>
                <button @click="fetchExplanation()" class="px-4 py-2 font-bold text-paper bg-brand-700 border-2 border-ink-900 dark:border-paper shadow-hard-sm hover:-translate-y-0.5 transition-all">
                    [retry]
                </button>
            </div>
        </div>

        <template v-else-if="data">
            <div class="h-2 bg-ink-100 dark:bg-ink-800 border-b-2 border-ink-900 dark:border-ink-200">
                <div class="h-full bg-brand-500 transition-all duration-500 ease-out"
                    :style="{ width: progress + '%' }"></div>
            </div>

            <div class="flex items-center justify-center gap-2 py-3 px-4 bg-paper dark:bg-ink-950 border-b-2 border-dashed border-ink-300 dark:border-ink-700 font-mono">
                <button v-for="(step, i) in steps" :key="i" @click="goToStep(i)"
                    :class="[
                        'w-8 h-8 border-2 text-xs font-bold transition-all flex items-center justify-center',
                        i === currentStep
                            ? 'bg-brand-700 border-ink-900 dark:border-paper text-paper shadow-hard-sm'
                            : readSteps.has(i)
                                ? 'bg-brand-200 dark:bg-brand-900/40 border-brand-700 text-brand-800 dark:text-brand-300'
                                : 'bg-paper dark:bg-ink-900 border-ink-300 dark:border-ink-700 text-ink-700 dark:text-ink-100 hover:border-ink-900 dark:hover:border-paper'
                    ]"
                    :title="step.title">
                    <span v-if="readSteps.has(i) && i !== currentStep">✓</span>
                    <span v-else>{{ i + 1 }}</span>
                </button>
            </div>

            <div class="p-6 min-h-[300px]">
                <div v-if="currentStepData">
                    <div class="flex items-start gap-3 mb-4">
                        <span class="flex-shrink-0 w-9 h-9 bg-brand-700 text-paper border-2 border-ink-900 dark:border-paper shadow-hard-sm flex items-center justify-center font-mono font-bold text-sm">
                            {{ String(currentStep + 1).padStart(2, '0') }}
                        </span>
                        <h2 class="font-serif text-2xl font-bold text-ink-900 dark:text-paper pt-0.5">
                            {{ currentStepData.title }}
                        </h2>
                    </div>

                    <div class="ml-12 space-y-4">
                        <p class="text-ink-900 dark:text-paper leading-relaxed text-base">
                            {{ currentStepData.explanation }}
                        </p>

                        <div v-if="currentStepData.analogy" class="flex gap-3 bg-amber-50 dark:bg-amber-900/20 border-2 border-amber-700 dark:border-amber-500 p-4">
                            <span class="font-mono text-xs font-bold text-amber-700 dark:text-amber-300 flex-shrink-0">[ аналогия ]</span>
                            <p class="text-sm text-amber-900 dark:text-amber-100 italic">{{ currentStepData.analogy }}</p>
                        </div>

                        <div v-if="currentStepData.key_points?.length">
                            <button @click="togglePoints"
                                class="flex items-center gap-2 font-mono text-sm font-bold text-brand-700 dark:text-brand-400 hover:underline underline-offset-4 decoration-2">
                                <span>{{ expandedPoints.has(currentStep) ? '[-]' : '[+]' }}</span>
                                ключевые факты ({{ currentStepData.key_points.length }})
                            </button>
                            <ul v-if="expandedPoints.has(currentStep)" class="mt-3 space-y-2 animate-fadeIn border-l-2 border-dashed border-brand-400 pl-4">
                                <li v-for="(point, pi) in currentStepData.key_points" :key="pi"
                                    class="flex items-start gap-2 text-sm text-ink-900 dark:text-paper">
                                    <span class="font-mono text-brand-700 dark:text-brand-400 flex-shrink-0">▸</span>
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between p-4 border-t-2 border-ink-900 dark:border-ink-200 bg-paper dark:bg-ink-950 font-mono">
                <button @click="prevStep" :disabled="currentStep === 0"
                    :class="[
                        'px-4 py-2 text-sm font-bold border-2 transition-all',
                        currentStep === 0
                            ? 'text-ink-300 dark:text-ink-700 border-ink-300 dark:border-ink-700 cursor-not-allowed'
                            : 'text-ink-900 dark:text-paper bg-paper-50 dark:bg-ink-900 border-ink-900 dark:border-paper shadow-hard-sm hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-hard'
                    ]">
                    ← prev
                </button>

                <span class="text-xs text-ink-700 dark:text-ink-100 font-bold">
                    {{ progress }}% done
                </span>

                <button v-if="currentStep < totalSteps - 1" @click="nextStep"
                    class="px-4 py-2 bg-brand-700 text-paper border-2 border-ink-900 dark:border-paper text-sm font-bold shadow-hard-sm hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-hard transition-all">
                    next →
                </button>
                <button v-else @click="readSteps.add(currentStep); saveProgress(); emit('close')"
                    class="px-4 py-2 bg-brand-600 text-paper border-2 border-ink-900 dark:border-paper text-sm font-bold shadow-hard-sm hover:-translate-y-0.5 hover:-translate-x-0.5 hover:shadow-hard transition-all">
                    [done] ✓
                </button>
            </div>
        </template>
    </div>
</template>

<style scoped>
.animate-fadeIn {
    animation: fadeIn 0.2s ease-out;
}
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(-4px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
