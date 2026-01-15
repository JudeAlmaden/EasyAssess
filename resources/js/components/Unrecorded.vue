<script setup lang="ts">
import { Trash2 } from 'lucide-vue-next'

const props = defineProps<{
  columns: string[];
  persons: {
    id: number;
    data: Record<string, string>;
  }[];
}>();

const emit = defineEmits<{
  (e: 'delete', personId: string): void;
}>();

function deletePerson(personId: string) {
  emit('delete', personId);
}
</script>

<template>
  <div class="space-y-4">
    <div class="overflow-auto rounded-lg border bg-accent">
      <table class="min-w-full divide-y divide-card text-sm border-accent-foreground">
        <thead class="bg-muted text-foreground border-border shadow-sm">
          <tr>
            <th v-for="col in props.columns" :key="col"
              class="px-4 py-3 text-left text-xs font-medium  uppercase tracking-wider">
              {{ col }}
            </th>
            <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-accent bg-muted">
          <tr v-if="props.persons.length === 0">
            <td :colspan="props.columns.length + 1" class="px-4 py-4 text-center text-muted-foreground">
              <div class="flex flex-col items-center justify-center py-6">
                <span class="text-sm italic">All respondents have completed the assessment</span>
              </div>
            </td>
          </tr>
          <tr v-for="person in props.persons" :key="person.id" class="hover:bg-gray-50/50 transition-colors">
            <td v-for="col in props.columns" :key="col" class="px-4 py-3  whitespace-nowrap"
              :class="{ 'text-muted-foreground': !person.data[col] }">
              {{ person.data[col] || '-' }}
            </td>
            <td class="px-4 py-3 text-center whitespace-nowrap">
              <button @click="deletePerson(person.data['Id'])"
                class="p-1.5 text-gray-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-colors"
                title="Delete person">
                <Trash2 class="h-5 w-5" />
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>