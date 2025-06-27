<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
  answers: {
    person: { id: string | null; name: string | null };
    answers: {
      mcq: { id: string; score: number; bubbles: any[][] }[];
      Freeform: { id: string; score: number }[];
      Blanks: { id: string; score: number }[];
    };
  }[];
  omrSheet: {
    OMRSheet: {
      MCQ: { id: string; section: string }[];
      Freeform?: { id: string; Instruction?: string }[];
      Blanks?: { id: string; section?: string }[];
    };
  };
  person_meta_columns: string[];
  assessmentId?: number | null;
  onDelete?: (id: number) => void;
}>();

const searchQuery = ref('');
const confirmId = ref<number | null>(null);
const showConfirm = ref(false);

// Section maps
const mcqSectionMap = computed(() =>
  Object.fromEntries(props.omrSheet.OMRSheet.MCQ.map(b => [b.id, b.section]))
);
const blankSectionMap = computed(() =>
  Object.fromEntries((props.omrSheet.OMRSheet.Blanks || []).map(b => [b.id, b.section || 'Unknown']))
);

// Unique section headers
const mcqSections = computed(() => [...new Set(props.omrSheet.OMRSheet.MCQ.map(b => b.section))]);
const blankSections = computed(() => [...new Set((props.omrSheet.OMRSheet.Blanks || []).map(b => b.section || 'Unknown'))]);
const freeformBlocks = computed(() => props.omrSheet.OMRSheet.Freeform || []);

// Check which columns to render
const hasMcqAnswers = computed(() => props.answers.some(a => a.answers.mcq.length));
const hasFreeformAnswers = computed(() => props.answers.some(a => a.answers.Freeform.length));
const hasBlankAnswers = computed(() => props.answers.some(a => a.answers.Blanks.length));

// Section score grouping
function getMcqScores(mcq: { id: string; score: number }[]) {
  const result: Record<string, number> = {};
  for (const b of mcq) {
    const section = mcqSectionMap.value[b.id];
    if (section) result[section] = (result[section] || 0) + b.score;
  }
  return result;
}

function getBlankScores(blanks: { id: string; score: number }[]) {
  const result: Record<string, number> = {};
  for (const b of blanks) {
    const section = blankSectionMap.value[b.id];
    if (section) result[section] = (result[section] || 0) + b.score;
  }
  return result;
}

function getFreeformScore(freeform: { id: string; score: number }[], id: string) {
  return freeform.find(f => f.id === id)?.score ?? 0;
}

function getTotalScore(entry: typeof props.answers[0]) {
  const mcq = getMcqScores(entry.answers.mcq);
  const blanks = getBlankScores(entry.answers.Blanks);
  const freeforms = entry.answers.Freeform;

  const mcqTotal = Object.values(mcq).reduce((a, b) => a + b, 0);
  const blankTotal = Object.values(blanks).reduce((a, b) => a + b, 0);
  const freeformTotal = freeforms.reduce((a, b) => a + (b.score || 0), 0);

  return mcqTotal + blankTotal + freeformTotal;
}

const filteredAnswers = computed(() => {
  const q = searchQuery.value.toLowerCase();
  if (!q) return props.answers;
  return props.answers.filter(
    entry => (entry.person.name || '').toLowerCase().includes(q)
  );
});

function confirmDelete(entry: typeof props.answers[number]) {
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

const emit = defineEmits<{
  (e: 'onDelete', index: number): void;
}>();


//exporting ino csv
const exportableColumns = computed(() => {
  const columns = [];

  columns.push({
    section: 'Respondent Info',
    options: [
      { label: 'Name', value: 'name' },
      ...props.person_meta_columns.map(col => ({ label: col, value: col }))
    ]
  });

  if (hasMcqAnswers.value) {
    columns.push({
      section: 'MCQ',
      options: [
        { label: 'Total MCQ', value: 'total-mcq' },
        ...mcqSections.value.map(s => ({ label: `MCQ: ${s}`, value: `mcq-${s}` }))
      ]
    });
  }

  if (hasFreeformAnswers.value) {
    columns.push({
      section: 'Freeform',
      options: [
        { label: 'Total Freeform', value: 'total-freeform' },
        ...freeformBlocks.value.map(b => ({
          label: `Freeform: ${b.Instruction || b.id}`,
          value: `freeform-${b.id}`
        }))
      ]
    });
  }

  if (hasBlankAnswers.value) {
    columns.push({
      section: 'Blanks',
      options: [
        { label: 'Total Blanks', value: 'total-blanks' },
        ...blankSections.value.map(s => ({ label: `Blank: ${s}`, value: `blank-${s}` }))
      ]
    });
  }

  columns.push({
    section: 'Summary',
    options: [
      { label: 'Total Score', value: 'total' }
    ]
  });

  return columns;
});


const selectedColumns = ref<string[]>(['name', 'total']); // default columns
const groupBy = ref<string[]>([]);
const sortBy = ref('');

function getExportColumnValue(entry, col) {
  if (col === 'name') return entry.person.name ?? '';
  if (props.person_meta_columns.includes(col)) return entry.person[col] ?? '';

  if (col === 'total') return getTotalScore(entry);
  if (col === 'total-mcq') return Object.values(getMcqScores(entry.answers.mcq)).reduce((a, b) => a + b, 0);
  if (col === 'total-blanks') return Object.values(getBlankScores(entry.answers.Blanks)).reduce((a, b) => a + b, 0);
  if (col === 'total-freeform') return entry.answers.Freeform.reduce((a, b) => a + b.score, 0);

  if (col.startsWith('mcq-')) {
    const section = col.slice(5);
    return getMcqScores(entry.answers.mcq)[section] ?? 0;
  }

  if (col.startsWith('freeform-')) {
    const id = col.slice(9);
    return getFreeformScore(entry.answers.Freeform, id);
  }

  if (col.startsWith('blank-')) {
    const section = col.slice(6);
    return getBlankScores(entry.answers.Blanks)[section] ?? 0;
  }

  return '';
}



function exportCSV() {
  const headers = selectedColumns.value.map(col => {
    const found = exportableColumns.value.flatMap(group => group.options).find(opt => opt.value === col);
    return found?.label || col;
  });

  const rows: string[][] = [];
  const filtered = props.answers.filter(entry =>
    entry.person.name?.toLowerCase().includes(searchQuery.value.toLowerCase())
  );

  rows.push(headers);

  for (const entry of filtered) {
    const row: string[] = [];

    for (const col of selectedColumns.value) {
      row.push(getExportColumnValue(entry, col).toString());
    }

    rows.push(row);
  }

  // Convert to CSV string
  const csvContent = rows.map(r =>
    r.map(v => `"${(v ?? '').toString().replace(/"/g, '""')}"`).join(',')
  ).join('\n');

  // Trigger download
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `assessment_results_${Date.now()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}


const showExportModal = ref(false);

function confirmExportCSV() {
  showExportModal.value = false;
  exportCSV();
}

function addGroupByColumn(col: string) {
  if (!groupBy.value.includes(col)) {
    groupBy.value.push(col);
  }
}

function removeGroupByColumn(index: number) {
  groupBy.value.splice(index, 1);
}

function moveGroupByUp(index: number) {
  if (index > 0) {
    const temp = groupBy.value[index];
    groupBy.value[index] = groupBy.value[index - 1];
    groupBy.value[index - 1] = temp;
  }
}

function moveGroupByDown(index: number) {
  if (index < groupBy.value.length - 1) {
    const temp = groupBy.value[index];
    groupBy.value[index] = groupBy.value[index + 1];
    groupBy.value[index + 1] = temp;
  }
}

</script>


<template>
  <div class="space-y-6">
    <!-- Search and Export Controls -->
    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
      <div class="relative w-full sm:w-96">
        <input v-model="searchQuery" type="text" placeholder="Search respondent by name..."
          class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
        <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24"
          stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>

      <button @click="showExportModal = true"
        class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors shadow-sm">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
        </svg>
        Export CSV
      </button>
    </div>

    <!-- Export Modal -->
    <div v-if="showExportModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl space-y-4">
        <div class="flex justify-between items-center">
          <h2 class="text-lg font-semibold text-gray-800">Export Options</h2>
          <button @click="showExportModal = false" class="text-gray-400 hover:text-gray-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Column Selection -->
        <div class="space-y-4">
          <label class="block text-sm font-medium text-gray-700">Columns to Include: Selection order matters</label>

          <div v-for="section in exportableColumns" :key="section.section">
            <div class="text-xs font-semibold text-gray-500 mb-1">{{ section.section }}</div>
            <div class="grid grid-cols-2 gap-2">
              <label v-for="opt in section.options" :key="opt.value" class="flex items-center space-x-2">
                <input type="checkbox" v-model="selectedColumns" :value="opt.value"
                  class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <span class="text-sm text-gray-700">{{ opt.label }}</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Group by Section -->
        <div class="space-y-2">
          <label class="block text-sm font-medium text-gray-700">Group by (order matters)</label>

          <!-- Available columns -->
          <div class="flex flex-wrap gap-2 mb-2">
            <button v-for="meta in props.person_meta_columns" :key="'add-' + meta" :disabled="groupBy.includes(meta)"
              @click="addGroupByColumn(meta)"
              class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100 disabled:opacity-50 text-black">
              {{ meta }}
            </button>
          </div>

          <!-- Selected group order list with animation -->
          <transition-group name="fade-move" tag="ul" class="space-y-2">
            <li v-for="(col, index) in groupBy" :key="col"
              class="flex items-center justify-between gap-2 p-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
              <span class="text-sm font-medium text-gray-800 flex-1 truncate">{{ col }}</span>
              <div class="flex gap-2 items-center">
                <!-- Up/Down in one vertical column -->
                <div class="flex flex-col items-center gap-1">
                  <button @click="moveGroupByUp(index)"
                    class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-black text-sm">▲</button>
                  <button @click="moveGroupByDown(index)"
                    class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-black text-sm">▼</button>
                </div>

                <!-- Delete button in its own column -->
                <div class="flex items-center justify-center">
                  <button @click="removeGroupByColumn(index)"
                    class="w-6 h-6 flex items-center justify-center text-red-500 hover:text-red-700 text-sm">✕</button>
                </div>
              </div>
            </li>
          </transition-group>
        </div>


        <!-- Sort by -->
      <div class="space-y-2">
        <label class="block text-sm font-medium text-black">Sort by</label>
        <select
          v-model="sortBy"
          class="block w-full pl-3 pr-10 py-2 text-base sm:text-sm rounded-md
                text-black bg-white border border-gray-300
                focus:outline-none focus:ring-blue-500 focus:border-blue-500
                dark:text-black dark:bg-white dark:border-gray-300"
        >
          <option value="">None</option>
          <option value="name">Name</option>
          <option value="total">Total Score</option>
        </select>
      </div>
  


        <!-- Buttons -->
        <div class="flex justify-end gap-3 pt-4">
          <button @click="showExportModal = false"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Cancel
          </button>
          <button @click="confirmExportCSV"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Export
          </button>
        </div>
      </div>
    </div>

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
    <div v-else class="overflow-x-auto shadow-sm rounded-lg border border-secondary">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-accent">
          <tr>
            <th rowspan="2" scope="col"
              class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
              Respondent
            </th>

            <template v-if="hasMcqAnswers">
              <th :colspan="mcqSections.length" scope="col"
                class="px-2 py-2 text-center text-xs font-medium  uppercase tracking-wider border-b border-gray-200">
                MCQ
              </th>
            </template>
            <template v-if="hasFreeformAnswers">
              <th :colspan="freeformBlocks.length" scope="col"
                class="px-2 py-2 text-center text-xs font-medium  uppercase tracking-wider border-b border-gray-200">
                Freeform
              </th>
            </template>
            <template v-if="hasBlankAnswers">
              <th :colspan="blankSections.length" scope="col"
                class="px-2 py-2 text-center text-xs font-medium  uppercase tracking-wider border-b border-gray-200">
                Blanks
              </th>
            </template>

            <th rowspan="2" scope="col"
              class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
              Total
            </th>
            <th rowspan="2" scope="col"
              class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider">
              Actions
            </th>
          </tr>

          <tr>
            <template v-if="hasMcqAnswers">
              <th v-for="section in mcqSections" :key="'mcq-' + section" scope="col"
                class="px-3 py-2 text-xs font-medium  uppercase tracking-wider">
                {{ section }}
              </th>
            </template>
            <template v-if="hasFreeformAnswers">
              <th v-for="block in freeformBlocks" :key="'ff-' + block.id" scope="col"
                class="px-3 py-2 text-xs font-medium  uppercase tracking-wider">
                {{ block.Instruction || block.id }}
              </th>
            </template>
            <template v-if="hasBlankAnswers">
              <th v-for="section in blankSections" :key="'blank-' + section" scope="col"
                class="px-3 py-2 text-xs font-medium  uppercase tracking-wider">
                {{ section }}
              </th>
            </template>
          </tr>
        </thead>


        <tbody class="bg-background divide-y text-foreground border-secondary">
          <tr v-for="entry in filteredAnswers" :key="entry.person.id" class="hover:bg-popover/50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="flex items-center">
                <div
                  class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                  {{ entry.person.name?.charAt(0) || '?' }}
                </div>
                <div class="ml-4">
                  <div class="text-sm font-medium ">
                    {{ entry.person.name || `Respondent #${entry.person.id}` }}
                  </div>
                  <div class="text-sm text-muted-foreground">
                    ID: {{ entry.person.id }}
                  </div>
                </div>
              </div>
            </td>

            <template v-if="hasMcqAnswers">
              <td v-for="section in mcqSections" :key="'mcq-cell-' + section"
                class="px-3 py-4 whitespace-nowrap text-sm text-center text-foreground">
                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                  {{ getMcqScores(entry.answers.mcq)[section] ?? 0 }}
                </span>
              </td>
            </template>

            <template v-if="hasFreeformAnswers">
              <td v-for="block in freeformBlocks" :key="'ff-cell-' + block.id"
                class="px-3 py-4 whitespace-nowrap text-sm text-center text-foreground">
                {{ getFreeformScore(entry.answers.Freeform, block.id) }}
              </td>
            </template>

            <template v-if="hasBlankAnswers">
              <td v-for="section in blankSections" :key="'blank-cell-' + section"
                class="px-3 py-4 whitespace-nowrap text-sm text-center text-foreground">
                {{ getBlankScores(entry.answers.Blanks)[section] ?? 0 }}
              </td>
            </template>

            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
              {{ getTotalScore(entry) }}
            </td>

            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
              <button @click="confirmDelete(entry)" class="text-red-600 hover:text-red-900 flex items-center gap-1">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                Delete
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Delete Confirmation Modal -->
    <div v-if="showConfirm" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-xl space-y-4">
        <div class="flex items-start">
          <div class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
          </div>
          <div class="ml-4">
            <h3 class="text-lg font-medium text-gray-900">Delete record</h3>
            <div class="mt-2 text-sm text-gray-500">
              Are you sure you want to delete this record? This action cannot be undone.
            </div>
          </div>
        </div>
        <div class="flex justify-end gap-3 pt-2">
          <button @click="cancelDelete"
            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Cancel
          </button>
          <button @click="doDelete"
            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
            Delete
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.fade-move-enter-active,
.fade-move-leave-active {
  transition: all 0.3s ease;
}

.fade-move-enter-from,
.fade-move-leave-to {
  opacity: 0;
  transform: translateY(10px);
}
</style>