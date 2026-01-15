<script setup lang="ts">
import { computed, ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AnswerKey from '@/components/AnswerKey.vue';
import AssessmentDashboard from '@/components/AssessmentDashboard.vue';
import PersonDictionary from '@/components/PersonDictionary.vue';
import AssessmentRecords from '@/components/AssessmentRecords.vue';
import AssessmentAnalysis from '@/components/AssessmentAnalysis.vue';
import Unrecorded from '@/components/Unrecorded.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
  assessment: {
    id: number;
    title: string | null;
    description: string | null;
    answer_key: any;
    answers: any[];
    person_dictionary_snapshot: {
      name?: string;
      meta_columns: string[];
      data: { [key: string]: any }[];
    } | null;
    omr_sheet_snapshot: Record<string, any> | null;
  };
  access_level: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Assessment', href: '/assessments' },
  {
    title: props.assessment.title ?? 'Untitled Assessment',
    href: `/assessment/${props.assessment.id}`,
  },
];

const tabs = ['Dashboard', 'Answer Key', 'Records', 'Respondents List', 'Data and Analytics'];
const activeTab = ref('Dashboard');

// Filter out "Id" and "Name"
const snapshotMetaColumns = computed(() =>
  (props.assessment.person_dictionary_snapshot?.meta_columns || []).filter(
    col => col !== 'Id' && col !== 'Name'
  )
);

const answeredIds = computed(() =>
  props.assessment.answers.map(entry => String(entry.person?.id))
);

const respondentRows = computed(() =>
  props.assessment.answers.map((entry, i) => {
    const person = entry.person || {};
    const row: Record<string, any> = {
      Id: person.id,
      Name: person.name,
    };
    snapshotMetaColumns.value.forEach(col => {
      row[col] = person[col] ?? null;
    });
    return { id: i + 1, data: row };
  })
);


const answers = ref([...props.assessment.answers]);
const personDictionarySnapshot = ref(props.assessment.person_dictionary_snapshot);


// Recompute unrecorded respondents when snapshot changes
const unrecordedRespondents = computed(() => {
  const snapshotPersons = personDictionarySnapshot.value?.data || [];
  const answeredIds = answers.value.map(entry => String(entry.person?.id));

  return snapshotPersons
    .filter(p => !answeredIds.includes(String(p['Id'])))
    .map((p, i) => {
      const row: Record<string, any> = {
        Id: p['Id'],
        Name: p['Name'],
      };
      snapshotMetaColumns.value.forEach(col => {
        row[col] = p[col] ?? null;
      });
      return { id: i + 1, data: row };
    });
});

function handleDelete(index: number) {
  answers.value.splice(index, 1);
}

// Delete unrecorded respondent
const confirmUnrecordedId = ref<string | null>(null);
const showUnrecordedConfirm = ref(false);

function confirmDeleteUnrecorded(personId: string) {
  confirmUnrecordedId.value = personId;
  showUnrecordedConfirm.value = true;
}

function cancelDeleteUnrecorded() {
  confirmUnrecordedId.value = null;
  showUnrecordedConfirm.value = false;
}

async function doDeleteUnrecorded() {
  if (confirmUnrecordedId.value == null || props.assessment.id == null) {
    console.warn('Delete aborted: Missing person ID or assessment ID');
    return;
  }

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const payload = {
      assessment_id: props.assessment.id,
      person_id: confirmUnrecordedId.value
    };

    const res = await fetch(route('assessment.unrecorded.delete'), {
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
      throw new Error('Failed to delete unrecorded respondent');
    }

    const result = await res.json();

    // Update local state with new snapshot
    if (result.person_dictionary_snapshot) {
      personDictionarySnapshot.value = result.person_dictionary_snapshot;
    }

  } catch (e) {
    console.error('Error deleting unrecorded respondent:', e);
    alert('Failed to delete respondent. Please try again.');
  } finally {
    confirmUnrecordedId.value = null;
    showUnrecordedConfirm.value = false;
  }
}


</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Tabs -->
      <div class="flex space-x-4 border-b">
        <button v-for="tab in tabs" :key="tab" @click="activeTab = tab" :class="[
          'pb-2 border-b-2 text-sm font-medium',
          activeTab === tab
            ? 'border-blue-600 text-blue-600'
            : 'border-transparent text-gray-500 hover:text-gray-700'
        ]">
          {{ tab }}
        </button>
      </div>

      <!-- Tab Contents -->
      <div v-if="activeTab === 'Dashboard'">
        <AssessmentDashboard :assessment="props.assessment" />
      </div>

      <!-- Answer key -->
      <div v-else-if="activeTab === 'Answer Key'">
        <AnswerKey ref="answerKeyRef" :assessment="props.assessment" :access_level="props.access_level"
          :answer_key="props.assessment.answer_key" />
      </div>

      <!--Recorded answers-->
      <div v-else-if="activeTab === 'Records'">
        <AssessmentRecords :answers="answers" :omrSheet="props.assessment.omr_sheet_snapshot"
          :assessmentId="props.assessment.id" :person_meta_columns="snapshotMetaColumns" @onDelete="handleDelete" />
      </div>

      <div v-else-if="activeTab === 'Respondents List'" class="space-y-8">
        <!-- Respondents with recorded answers -->
        <div class="rounded-xl shadow-xs border border-accent p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-primary">
              Respondents with Recorded Answers
            </h2>
            <span class="text-sm text-gray-500 bg-gray-100 px-3 py-1 rounded-full">
              {{ respondentRows.length }} recorded
            </span>
          </div>
          <PersonDictionary :dictionary="{
            id: props.assessment.id,
            name: props.assessment.person_dictionary_snapshot?.name ?? 'Respondents',
            description: 'List of respondents who completed the assessment',
            meta_columns: ['Id', 'Name', ...snapshotMetaColumns],
            persons: respondentRows
          }" :allow_delete="false" :access_level="props.access_level" :canAddPerson="false" :can-import="false"
            save-url="/api/dictionaries/respondent/update" class="border-none shadow-none" />
        </div>

        <!-- Unrecorded Respondents -->
        <div v-if="unrecordedRespondents.length" class=" rounded-xl shadow-xs border border-accent p-6 ">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-semibold text-primary">
              Unrecorded Respondents
            </h2>
            <span class="text-sm text-yellow-800 bg-yellow-100 px-3 py-1 rounded-full">
              {{ unrecordedRespondents.length }} pending
            </span>
          </div>

          <div class="rounded-lg border border-popover bg-popover p-5 space-y-4">
            <div class="flex items-start gap-3">
              <p class="text-sm text-primary">
                These individuals are included in the assessment but haven't submitted any answers yet.
              </p>
            </div>
            <Unrecorded :columns="['Id', 'Name', ...snapshotMetaColumns]" :persons="unrecordedRespondents"
              @delete="confirmDeleteUnrecorded" />
          </div>
        </div>

        <!-- Delete Confirmation Modal for Unrecorded -->
        <div v-if="showUnrecordedConfirm"
          class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-lg p-6 max-w-sm w-full shadow-xl space-y-4">
            <div class="flex items-start">
              <div
                class="flex-shrink-0 h-10 w-10 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-lg font-medium text-gray-900">Delete Respondent</h3>
                <div class="mt-2 text-sm text-gray-500">
                  Are you sure you want to remove this person from the assessment?
                </div>
              </div>
            </div>
            <div class="flex justify-end gap-3 pt-2">
              <button @click="cancelDeleteUnrecorded"
                class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                Cancel
              </button>
              <button @click="doDeleteUnrecorded"
                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="activeTab === 'Data and Analytics'">
        <AssessmentAnalysis :answers="props.assessment.answers"
          :omr_sheet_snapshot="props.assessment.omr_sheet_snapshot" />
      </div>
    </div>
  </AppLayout>
</template>
