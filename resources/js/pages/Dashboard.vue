<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import { Users, FileSpreadsheet, ClipboardList } from 'lucide-vue-next';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Homepage', href: '/Homepage' },
];

const steps = [
    {
        title: "1. Create a list of Respondents",
        description: "Optionally list your possible respondents to make recording easier",
        icon: Users,
        href: "/dictionary"
    },
    {
        title: "2. Design Answer Sheet",
        description: "Prepare your answer sheet template and print it out",
        icon: FileSpreadsheet,
        href: "/omr-sheets"
    },
    {
        title: "3. Build Assessment",
        description: "Select respondents and Sheet to your assessments and start scanning",
        icon: ClipboardList,
        href: "/assessments"
    }
];

const animated = ref(false);
const currentUrl = computed(() => usePage().url);

onMounted(() => {
    setTimeout(() => {
        animated.value = true;
    }, 100);
});

// Keyboard navigation for cards for accessibility
const onKeyNavigate = (href: string) => {
  window.location.href = href;
};
</script>

<template>
  <Head title="Dashboard" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="relative flex h-full flex-1 flex-col">
      <!-- Decorative gradient background -->
      <div
        aria-hidden="true"
        class="pointer-events-none absolute inset-x-0 -top-24 -z-10 h-[28rem] bg-gradient-to-b from-blue-50 via-white to-transparent dark:from-blue-950/40 dark:via-transparent"
      ></div>

      <!-- Hero Section -->
      <div
        class="flex flex-1 flex-col items-center justify-center px-4 py-16 text-center sm:py-20"
      >
        <div class="max-w-5xl">
          <!-- Animated Title -->
          <h1
            class="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-6xl"
            :class="{ 'animate-fade-in-up': animated }"
          >
            Welcome to
            <span
              class="bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent"
              >EasyAssess</span
            >
          </h1>

          <!-- Animated Tagline -->
          <p
            class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-300 transition-all duration-500 delay-100 sm:text-xl"
            :class="{
              'opacity-0 translate-y-4': !animated,
              'opacity-100 translate-y-0': animated,
            }"
          >
            Streamlined assessment creation in three simple steps
          </p>

          <!-- Enhanced Steps with larger size and lift effect -->
          <div class="mt-14 grid gap-6 sm:mt-16 sm:grid-cols-2 lg:grid-cols-3">
            <a
              v-for="(step, index) in steps"
              :key="step.title"
              :href="step.href"
              class="group block transform rounded-2xl border border-gray-200/80 bg-white/70 p-7 text-left shadow-sm backdrop-blur transition-all duration-300 hover:-translate-y-2 hover:shadow-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 dark:border-gray-700/70 dark:bg-gray-800/60"
              :class="{
                'animate-fade-in-up': animated,
                'border-blue-500 ring-2 ring-blue-500/20': currentUrl.startsWith(
                  step.href
                ),
                'hover:border-blue-400 dark:hover:border-blue-400': !currentUrl.startsWith(
                  step.href
                ),
              }"
              :style="{ 'animation-delay': `${index * 90 + 180}ms` }"
              role="link"
              tabindex="0"
              @keyup.enter.prevent="onKeyNavigate(step.href)"
              @keyup.space.prevent="onKeyNavigate(step.href)"
            >
              <div class="flex items-start gap-4">
                <div
                  class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-blue-100 text-blue-600 shadow-sm transition-all duration-300 group-hover:scale-105 group-hover:bg-blue-600 group-hover:text-white dark:bg-blue-900/40 dark:text-blue-300"
                >
                  <component :is="step.icon" class="h-7 w-7" />
                </div>
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ step.title }}
                  </h3>
                  <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                    {{ step.description }}
                  </p>
                </div>
              </div>

              <!-- subtle underline on hover -->
              <span
                class="mt-5 block h-px w-0 bg-gradient-to-r from-blue-500 to-indigo-500 transition-all duration-300 group-hover:w-full"
              />
            </a>
          </div>

          <!-- Project Info Footer -->
          <div
            class="mt-16 text-xs text-gray-500 dark:text-gray-400 transition-all duration-700 delay-500 sm:text-sm"
            :class="{ 'opacity-0': !animated, 'opacity-80': animated }"
          >
            <p>Developed by Justine Jude D. Almaden</p>
            <p class="mt-1">Capstone Project • St. Anne College Lucena</p>
            <p class="mt-1">© 2025 EasyAssess • All rights reserved</p>
          </div>
        </div>
      </div>

      <!-- Subtle Background Pattern -->
      <div
        class="pointer-events-none absolute inset-0 -z-10 overflow-hidden opacity-[0.06] dark:opacity-[0.08]"
      >
        <div
          class="absolute left-1/2 top-0 h-[50rem] w-[120rem] -translate-x-1/2 bg-[linear-gradient(to_right,transparent,white,transparent),repeating-linear-gradient(to_right,currentColor,currentColor_1px,transparent_1px,transparent_4px)] dark:bg-[linear-gradient(to_right,transparent,rgba(17,24,39,0.5),transparent),repeating-linear-gradient(to_right,currentColor,currentColor_1px,transparent_1px,transparent_4px)]"
        >
          <div
            class="absolute inset-0 bg-gradient-to-b from-transparent to-white dark:to-gray-900"
          ></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style>
@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(18px) scale(0.99);
    filter: blur(3px);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
    filter: blur(0);
  }
}

.animate-fade-in-up {
  animation: fadeInUp 0.55s ease-out forwards;
  opacity: 0;
}

/* Reduce motion for users who prefer it */
@media (prefers-reduced-motion: reduce) {
  .animate-fade-in-up {
    animation: none;
    opacity: 1 !important;
  }
}

.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
  transition-duration: 280ms;
}
</style>
