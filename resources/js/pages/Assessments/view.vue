<script setup lang="ts">
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import AnswerKey from '@/components/AnswerKey.vue';
import AssessmentDashboard from '@/components/AssessmentDashboard.vue';

const props = defineProps<{
  assessment: {
    id: number;
    title: string | null;
    description: string | null;
    answer_key: any;
    answers: any;
    person_dictionary_snapshot: Record<string, any> | null;
    omr_sheet_snapshot: Record<string, any> | null;
  };
  access_level: string;
}>();

// Tab state
const tabs = [
  'Dashboard',
  'Answer Key',
  'Respondents Results',
  'Respondents List',
  'Results'
];
const activeTab = ref('Dashboard');


</script>

<template>
  <AppLayout>
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
        <AssessmentDashboard></AssessmentDashboard>
      </div>

      <div v-else-if="activeTab === 'Answer Key'">
        <AnswerKey
          ref="answerKeyRef"
          :assessment="props.assessment"
          :access_level="props.access_level"
          :answer_key="props.assessment.answer_key"
        />
      </div>

      <div v-else-if="activeTab === 'Respondents Results'">
        <h2 class="text-xl font-semibold">Respondents Results</h2>
        <p>Summary or analytics of respondents’ performance here.</p>
      </div>

      <div v-else-if="activeTab === 'Respondents List'">
        <h2 class="text-xl font-semibold">Respondents List</h2>
        <p>Table or list of all respondents here.</p>
      </div>

      <div v-else-if="activeTab === 'Results'">
        <h2 class="text-xl font-semibold">Results</h2>
        <p>Detailed results tab for further breakdown.</p>
      </div>
    </div>
  </AppLayout>
</template>
