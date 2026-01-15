<script setup lang="ts">
import AppLayout from "@/layouts/AppLayout.vue";
import PersonDictionary from "@/components/PersonDictionary.vue";
import type { BreadcrumbItem } from "@/types";
import { ref } from "vue";

// Props
const props = defineProps<{
  dictionary: {
    id: number;
    name: string;
    description: string;
    meta_columns: string[];
    persons: { id: number; data: Record<string, string> }[];
  };
  access_level: string;
}>();

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: "Person Dictionary", href: "/dictionary" },
  {
    title: props.dictionary?.name ?? "Untitled Dictionary",
    href: `/dictionary/${props.dictionary?.id}`,
  },
];

// Ref to child component
const dictRef = ref<InstanceType<typeof PersonDictionary> | null>(null);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="space-y-6">
      <!-- Dictionary Table Component -->
      <PersonDictionary
        ref="dictRef"
        :dictionary="props.dictionary"
        :access_level="props.access_level"
        :allow_delete="true"
        :canAddPerson="true"
        save-url="/api/dictionary/update"
      />
    </div>
  </AppLayout>
</template>
