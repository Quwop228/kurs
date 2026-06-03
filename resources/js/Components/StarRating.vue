<script setup>
import { ref } from 'vue';

const props = defineProps({
    modelValue: {
        type: Number,
        default: 0,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    average: {
        type: Number,
        default: 0,
    },
    count: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(['update:modelValue']);

const hoverValue = ref(0);

const setHover = (value) => {
    if (!props.readonly) {
        hoverValue.value = value;
    }
};

const setRating = (value) => {
    if (!props.readonly) {
        emit('update:modelValue', value);
    }
};

const displayValue = (index) => {
    if (hoverValue.value > 0) return index <= hoverValue.value;
    if (props.modelValue > 0) return index <= props.modelValue;
    return index <= Math.round(props.average);
};
</script>

<template>
    <div class="flex items-center gap-2">
        <div class="flex items-center gap-0.5" @mouseleave="hoverValue = 0">
            <button
                v-for="star in 5"
                :key="star"
                type="button"
                :disabled="readonly"
                @mouseenter="setHover(star)"
                @click="setRating(star)"
                :class="[
                    'transition-colors',
                    readonly ? 'cursor-default' : 'cursor-pointer hover:scale-110 transform transition-transform'
                ]"
            >
                <svg
                    class="w-5 h-5"
                    :class="displayValue(star) ? 'text-amber-400' : 'text-gray-300 dark:text-gray-600'"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                >
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
            </button>
        </div>
        <span v-if="average > 0" class="text-sm text-gray-500 dark:text-gray-400">
            {{ average.toFixed(1) }}
            <span v-if="count > 0">({{ count }})</span>
        </span>
    </div>
</template>
