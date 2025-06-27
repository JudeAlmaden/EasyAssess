<script setup lang="ts">
import { ref, computed } from 'vue';
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

const unrecordedRespondents = computed(() => {
  const snapshotPersons = props.assessment.person_dictionary_snapshot?.data || [];

  return snapshotPersons
    .filter(p => !answeredIds.value.includes(String(p['Id'])))
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

const answers = ref([...props.assessment.answers]);

function handleDelete(index: number) {
  answers.value.splice(index, 1);
}


</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Tabs -->
      <div class="flex space-x-4 border-b">
        <button
          v-for="tab in tabs"
          :key="tab"
          @click="activeTab = tab"
          :class="[
            'pb-2 border-b-2 text-sm font-medium',
            activeTab === tab
              ? 'border-blue-600 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700'
          ]"
        >
          {{ tab }}
        </button>
      </div>

      <!-- Tab Contents -->
      <div v-if="activeTab === 'Dashboard'">
        <AssessmentDashboard :assessment="props.assessment" />
      </div>

      <!-- Answer key -->
      <div v-else-if="activeTab === 'Answer Key'">
        <AnswerKey
          ref="answerKeyRef"
          :assessment="props.assessment"
          :access_level="props.access_level"
          :answer_key="props.assessment.answer_key"
        />
      </div>

      <!--Recorded answers-->
      <div v-else-if="activeTab === 'Records'">
        <AssessmentRecords
          :answers="answers"
          :omrSheet="props.assessment.omr_sheet_snapshot"
          :assessmentId="props.assessment.id"
          :person_meta_columns="snapshotMetaColumns"
          @onDelete="handleDelete"
        />
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
          <PersonDictionary
            :dictionary="{
              id: props.assessment.id,
              name: props.assessment.person_dictionary_snapshot?.name ?? 'Respondents',
              description: 'List of respondents who completed the assessment',
              meta_columns: ['Id', 'Name', ...snapshotMetaColumns],
              persons: respondentRows
            }"
            :allow_delete="false"
            :access_level="props.access_level"
            :canAddPerson="false"
            :can-import="false"
            save-url="/api/dictionaries/respondent/update"
            class="border-none shadow-none"
          />
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
            <Unrecorded
              :columns="['Id', 'Name', ...snapshotMetaColumns]"
              :persons="unrecordedRespondents"
            />
          </div>
        </div>
      </div>

      <div v-else-if="activeTab === 'Data and Analytics'">
        <AssessmentAnalysis
        :answers="props.assessment.answers"
        :omr_sheet_snapshot="props.assessment.omr_sheet_snapshot"
        />
      </div>
    </div>
  </AppLayout>
</template>
