<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

const props = defineProps<{
  assessment: {
    id: number;
  };
}>();

</script>

<template>
  Dashboard
  <a :href="route('assessment.record.view', [assessment.id])"
     class="w-32 px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center gap-1 shadow-md">
    Record Answers
    <Eye class="w-4 h-4" />
  </a>
</template>
