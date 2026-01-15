<script setup lang="ts">
import { computed } from 'vue';

interface GradientTextProps {
    text: string;
    className?: string;
    colors?: string[];
    animationSpeed?: number;
    showBorder?: boolean;
}

const props = withDefaults(defineProps<GradientTextProps>(), {
    text: '',
    className: '',
    colors: () => ['#ffaa40', '#9c40ff', '#ffaa40'],
    animationSpeed: 8,
    showBorder: false,
});

const gradientStyle = computed(() => ({
    backgroundImage: `linear-gradient(to right, ${props.colors.join(', ')})`,
    animationDuration: `${props.animationSpeed}s`,
    backgroundSize: '300% 100%',
    '--animation-duration': `${props.animationSpeed}s`,
}));

const borderStyle = computed(() => ({
    ...gradientStyle.value,
}));

const textStyle = computed(() => ({
    ...gradientStyle.value,
    backgroundClip: 'text',
    WebkitBackgroundClip: 'text',
}));
</script>

<template>
    <div
        :class="`relative mx-auto flex max-w-fit cursor-pointer flex-row items-center justify-center overflow-hidden rounded-[1.25rem] font-medium backdrop-blur transition-shadow duration-500 ${className}`"
    >
        <div v-if="showBorder" class="animate-gradient pointer-events-none absolute inset-0 z-0 bg-cover" :style="borderStyle">
            <div
                class="absolute inset-0 z-[-1] rounded-[1.25rem] bg-black"
                style="width: calc(100% - 2px); height: calc(100% - 2px); left: 50%; top: 50%; transform: translate(-50%, -50%)"
            />
        </div>

        <div class="animate-gradient relative z-2 inline-block bg-cover text-transparent" :style="textStyle">
            {{ text }}
        </div>
    </div>
</template>

<style scoped>
@keyframes gradient {
    0% {
        background-position: 0% 50%;
    }
    50% {
        background-position: 100% 50%;
    }
    100% {
        background-position: 0% 50%;
    }
}

.animate-gradient {
    animation: gradient var(--animation-duration, 8s) linear infinite;
}
</style>
