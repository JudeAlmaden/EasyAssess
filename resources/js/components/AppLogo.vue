<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { ref, onMounted } from 'vue';

const animated = ref(false);

onMounted(() => {
  setTimeout(() => {
    animated.value = true;
  }, 100);
});
</script>

<template>
  <div class="flex items-center">
    <!-- Animated Logo Container -->
    <div
      class="flex aspect-square size-8 items-center justify-center rounded-md bg-white text-sidebar-primary-foreground transition-all duration-500 border-0"
      :class="{
        'scale-0': !animated,
        'scale-100': animated,
        'hover:rotate-12 hover:scale-110': animated
      }">
      <AppLogoIcon class="size-5   transition-transform duration-300 hover:scale-125 " />
    </div>

    <!-- Animated Text Container -->
    <div class="ml-2 grid flex-1 overflow-hidden text-left text-sm transition-all duration-700" :class="{
      'opacity-0 -translate-x-2': !animated,
      'opacity-100 translate-x-0': animated
    }">
      <span class="truncate font-semibold leading-tight">
        <span
          class="inline-block bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent dark:from-blue-400 dark:to-purple-400"
          :class="{
            'animate-text-reveal': animated
          }">
          EasyAssess
        </span>
      </span>
      <span class="truncate text-xs text-muted-foreground transition-all duration-1000" :class="{
        'opacity-0': !animated,
        'opacity-70': animated
      }">
        Optical Mark Recognition System
      </span>
    </div>
  </div>
</template>

<style>
@keyframes textReveal {
  0% {
    opacity: 0;
    background-position: -200% center;
  }

  100% {
    opacity: 1;
    background-position: 0 center;
  }
}

.animate-text-reveal {
  animation: textReveal 1.2s ease-out forwards;
  background-size: 200% auto;
}

.hover\:rotate-12:hover {
  transform: rotate(12deg);
}
</style>