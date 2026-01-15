<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Eye, Trash } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { BaseModal } from '@/components/ui/modal';
import { Link } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Assessments',
    href: '/assessments',
  },
];

interface Assessment {
  id: number;
  title: string;
  description: string;
  created_at: string;
}

interface OmrSheet {
  id: number;
  owner_id: number;
  title: string;
  description: string;
  created_at: string;
  updated_at: string;
}

interface Dictionary {
  id: number;
  name: string;
  description: string;
  member_count: number;
  created_at: string;
}

const Assessments = ref<Assessment[]>([]);
const Questionnaires = ref<OmrSheet[]>([]);
const PersonDictionary = ref<Dictionary[]>([]);

const searchQuery = ref('');
const showConfirmDeleteModal = ref(false);
const assessmentToDelete = ref<Assessment | null>(null);

const showModal = ref(false);
const step = ref(1);
const assessmentTitleRef = ref('');
const assessmentDescriptionRef = ref('');
const omrSheetRef = ref<number | null>(null);
const respondentRef = ref<number | null>(null);
const modalComplete = ref(false);

function getCsrfToken(): string {
  const meta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  if (!meta?.content) throw new Error('CSRF token not found');
  return meta.content;
}

async function getAssessments() {
  try {
    const res = await fetch(route('assessments.get'), {
      headers: {
        Accept: 'application/json',
      },
      credentials: 'include',
    });

    if (!res.ok) throw new Error('Failed to fetch assessments');

    const data = await res.json();
    Assessments.value = data.data.map((item: any) => item.assessment);
  } catch (error) {
    console.error('Error fetching assessments:', error);
  }
}

async function getOmrSheets() {
  try {
    const res = await fetch(route('omr_sheets.get'), {
      headers: { Accept: 'application/json' },
      credentials: 'include',
    });

    if (!res.ok) throw new Error('Failed to fetch OMR sheets');

    const data = await res.json();
    Questionnaires.value = data.OMRSheets;
  } catch (error) {
    console.error('Error fetching OMR sheets:', error);
  }
}

async function getDictionaries() {
  try {
    const res = await fetch(route('dictionaries.get'), {
      headers: { Accept: 'application/json' },
      credentials: 'include',
    });

    if (!res.ok) throw new Error('Failed to fetch dictionaries');

    const data = await res.json();
    PersonDictionary.value = data.dictionaries;
  } catch (error) {
    console.error('Error fetching dictionaries:', error);
  }
}

const filteredAssessments = computed(() => {
  if (!searchQuery.value.trim()) return Assessments.value;
  const q = searchQuery.value.toLowerCase();
  return Assessments.value.filter(
    (item) =>
      item.title.toLowerCase().includes(q) ||
      item.description?.toLowerCase().includes(q)
  );
});

function formatDate(dateString: string) {
  const date = new Date(dateString);
  return date.toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
}

function openDeleteModal(assessment: Assessment) {
  assessmentToDelete.value = assessment;
  showConfirmDeleteModal.value = true;
}

async function confirmDelete() {
  if (!assessmentToDelete.value) return;

  try {
    const csrfToken = getCsrfToken();
    const res = await fetch(route('assessment.delete'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'include',
      body: JSON.stringify({ id: assessmentToDelete.value.id }),
    });

    if (!res.ok) throw new Error('Delete failed');

    showConfirmDeleteModal.value = false;
    assessmentToDelete.value = null;
    getAssessments();
  } catch (error) {
    console.error('Error deleting assessment:', error);
  }
}

function openModal() {
  showModal.value = true;
}

function onCancel() {
  resetModal();
}

function resetModal() {
  showModal.value = false;
  modalComplete.value = false;
  step.value = 1;
  assessmentTitleRef.value = '';
  assessmentDescriptionRef.value = '';
  omrSheetRef.value = null;
  respondentRef.value = null;
}

async function createAssessment(title: string, description: string, sheet: number, respondents: number) {
  try {
    const csrfToken = getCsrfToken();
    const res = await fetch(route('assessment.create'), {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
      },
      credentials: "include",
      body: JSON.stringify({ title, description, sheet, respondents }),
    });

    if (!res.ok) throw new Error("Failed to create");
    await getAssessments();
  } catch (error) {
    console.error("Error creating assessment", error);
  }
}

function onConfirm() {
  const title = assessmentTitleRef.value;
  const description = assessmentDescriptionRef.value;
  const omrSheet = omrSheetRef.value;
  const respondent = respondentRef.value;

  if (!title || !omrSheet ) {
    alert("Please fill in all required fields. :)");
    return;
  }

  createAssessment(title, description, omrSheet, respondent);
  resetModal();
}

onMounted(() => {
  getAssessments();
  getOmrSheets();
  getDictionaries();
});
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-5">
      <Card>
        <CardHeader>
          <div class="flex gap-2 items-center">
            <Input v-model="searchQuery" placeholder="Search assessment..." />
            <Button @click="openModal">Create</Button>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-1/4">Assessment</TableHead>
                <TableHead class="w-1/4">Description</TableHead>
                <TableHead class="w-1/4 text-right">Created At</TableHead>
                <TableHead class="w-1/4 text-center">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="item in filteredAssessments" :key="item.id">
                <TableCell class="w-1/4 font-medium">{{ item.title }}</TableCell>
                <TableCell class="w-1/4">{{ item.description || 'None' }}</TableCell>
                <TableCell class="w-1/4 text-right">{{ formatDate(item.created_at) }}</TableCell>
                <TableCell class="w-1/4 text-center">
                  <div class="flex justify-center items-center gap-2">
                    <Link
                      :href="route('assessment.view', [item.id])"
                      target="_blank"
                      class="w-32 px-3 py-2 text-sm bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-colors flex items-center justify-center gap-1 shadow-md"
                    >
                      View
                    </Link>

                    <Button
                      @click="openDeleteModal(item)"
                      class="w-32 px-3 py-2 text-sm bg-destructive text-destructive-foreground rounded-md hover:opacity-90 transition-colors flex items-center justify-center gap-1 shadow-md"
                    >
                      Delete
                      <Trash class="w-4 h-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <BaseModal
      v-model="showConfirmDeleteModal"
      title="Confirm Delete"
      @confirm="confirmDelete"
      @cancel="() => { showConfirmDeleteModal = false; assessmentToDelete = null }"
    >
      <p class="text-gray-800 dark:text-gray-200">
        Are you sure you want to delete
        <strong>{{ assessmentToDelete?.title }}</strong>?
      </p>
    </BaseModal>

    <!-- Create Assessment Modal -->
    <BaseModal
      v-model="showModal"
      :showFooter="modalComplete"
      title="Create New Assessment"
      @confirm="onConfirm"
      @cancel="onCancel"
    >
      <div v-if="step === 1">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Assessment Title
        </label>
        <input v-model="assessmentTitleRef" type="text" placeholder="Enter assessment title"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors mb-4" />

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Description
        </label>
        <textarea v-model="assessmentDescriptionRef" placeholder="Optional description" rows="3"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors"></textarea>

        <div class="flex justify-end mt-4">
          <button @click="step++; modalComplete = true"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
            Next
          </button>
        </div>
      </div>

      <div v-else-if="step === 2">
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          OMR Sheet
        </label>
        <select v-model="omrSheetRef"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 mb-4">
          <option :value="null" disabled>-- Please select --</option>
          <option v-for="sheet in Questionnaires" :key="sheet.id" :value="sheet.id">
            {{ sheet.title }}
          </option>
        </select>

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Reference for Respondents
        </label>
        <select v-model="respondentRef"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 mb-4">
          <option :value="null" >-- Please select --</option>
          <option v-for="dict in PersonDictionary" :key="dict.id" :value="dict.id">
            {{ dict.name }}
          </option>
        </select>
      </div>

      <div class="flex justify-center mt-6">
        <span class="h-2 w-2 rounded-full mx-1" :class="step === 1 ? 'bg-blue-600' : 'bg-gray-400'"></span>
        <span class="h-2 w-2 rounded-full mx-1" :class="step === 2 ? 'bg-blue-600' : 'bg-gray-400'"></span>
      </div>
    </BaseModal>
  </AppLayout>
</template>
