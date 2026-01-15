<script setup lang="ts">
import { computed, ref, provide, toRef } from 'vue';
import type { AssessmentAnswer, OMRSheet } from '@/types/assessment';
import { useScoreCalculation } from '@/composables/useScoreCalculation';
import { useRecordsFilter } from '@/composables/useRecordsFilter';
import { useExport } from '@/composables/useExport';
import ExportModal from './assessment-records/ExportModal.vue';
import DeleteConfirmModal from './assessment-records/DeleteConfirmModal.vue';
import RecordsTable from './assessment-records/RecordsTable.vue';

const props = defineProps<{
  answers: AssessmentAnswer[];
  omrSheet: OMRSheet;
  person_meta_columns: string[];
  assessmentId?: number | null;
  onDelete?: (id: number) => void;
}>();

const emit = defineEmits<{
  (e: 'onDelete', index: number): void;
}>();

// Use composables
const answersRef = toRef(props, 'answers');
const scoreCalc = useScoreCalculation(props.omrSheet);
const { searchQuery, filteredAnswers } = useRecordsFilter(answersRef);

// Check which columns to render
const hasMcqAnswers = computed(() => props.answers.some(a => a.answers.mcq.length));
const hasFreeformAnswers = computed(() => props.answers.some(a => a.answers.Freeform.length));
const hasBlankAnswers = computed(() => props.answers.some(a => a.answers.Blanks.length));

const exportLogic = useExport({
  answers: answersRef,
  personMetaColumns: props.person_meta_columns,
  scoreCalc,
  hasMcqAnswers,
  hasFreeformAnswers,
  hasBlankAnswers,
  searchQuery,
  omrSheet: props.omrSheet,
});

// Provide score calculation to child components
provide('scoreCalc', scoreCalc);

// Delete handling
const confirmId = ref<number | null>(null);
const showConfirm = ref(false);

function confirmDelete(entry: AssessmentAnswer) {
  const index = props.answers.findIndex(e => e.person.id === entry.person.id);
  if (index !== -1) {
    confirmId.value = index;
    showConfirm.value = true;
  } else {
    console.warn('Could not find index of entry in original answers list');
  }
}

function cancelDelete() {
  confirmId.value = null;
  showConfirm.value = false;
}

async function doDelete() {
  if (confirmId.value == null || props.assessmentId == null) {
    console.warn('Delete aborted: Missing confirmId or assessmentId', {
      confirmId: confirmId.value,
      assessmentId: props.assessmentId
    });
    return;
  }

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const payload = {
      id: props.assessmentId,
      index: confirmId.value
    };

    const res = await fetch(route('assessment.record.delete'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf || '',
        Accept: 'application/json',
      },
      credentials: 'include',
      body: JSON.stringify(payload),
    });

    if (!res.ok) {
      const errorText = await res.text();
      console.error('Server responded with error:', errorText);
      throw new Error('Failed to delete respondent');
    }

    if (res.ok) {
      emit('onDelete', payload.index);
    }

  } catch (e) {
    console.error('Error deleting respondent:', e);
  } finally {
    confirmId.value = null;
    showConfirm.value = false;
  }
}

// Export modal
const showExportModal = ref(false);

function confirmExportXLSX() {
  exportLogic.exportXLSX();
}

function exportDetailed() {
  exportLogic.exportDetailedAnswers();
}
</script>

<template>
  <div class="space-y-6">
    <!-- Search and Export Controls -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <div class="relative w-full sm:w-96">
        <input v-model="searchQuery" type="text" placeholder="Search by name or ID..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <div class="flex items-center gap-2">
        <button @click="showExportModal = true"
          class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Export XLSX
        </button>

        <button @click="exportDetailed"
          class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors shadow-sm">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Detailed Export
        </button>
      </div>
    </div>

    <!-- Export Modal -->
    <ExportModal v-model:show="showExportModal" v-model:selected-columns="exportLogic.selectedColumns.value"
      v-model:group-by="exportLogic.groupBy.value" v-model:sort-by="exportLogic.sortBy.value"
      :exportable-columns="exportLogic.exportableColumns.value" :person-meta-columns="person_meta_columns"
      @confirm="confirmExportXLSX" />

    <!-- Empty State -->
    <div v-if="filteredAnswers.length === 0"
      class="flex flex-col items-center justify-center p-8 bg-gray-50 rounded-lg border border-gray-200">
      <svg class="h-12 w-12 text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
          d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
      <p class="text-gray-500 text-center">
        No matching records found. <br>
        Try adjusting your search query.
      </p>
    </div>

    <!-- Results Table -->
    <RecordsTable v-else :answers="filteredAnswers" :has-mcq-answers="hasMcqAnswers"
      :has-freeform-answers="hasFreeformAnswers" :has-blank-answers="hasBlankAnswers"
      :mcq-sections="scoreCalc.mcqSections.value" :blank-sections="scoreCalc.blankSections.value"
      :freeform-blocks="scoreCalc.freeformBlocks.value" @delete="confirmDelete" />

    <!-- Delete Confirmation Modal -->
    <DeleteConfirmModal v-model:show="showConfirm" @confirm="doDelete" @cancel="cancelDelete" />
  </div>
</template>