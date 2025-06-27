<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { Eye } from 'lucide-vue-next';
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
    title: 'Your OMR Sheets',
    href: '/omr-sheets',
  },
];

interface OmrSheet {
  id: number;
  owner_id: number;
  title: string;
  description: string;
  created_at: string;
  updated_at: string;
}

const Questionnaires = ref<OmrSheet[]>([]);
const searchQuery = ref('');
const deleteConfirmModal = ref(false);
const sheetToDelete = ref<OmrSheet | null>(null);

// Fetch sheets
async function getOmrSheets() {
  try {
    const res = await fetch(route('omr_sheets.get'), {
      headers: {
        Accept: 'application/json',
      },
      credentials: 'include',
    });

    if (!res.ok) throw new Error('Failed to fetch sheets');

    const data = await res.json();
    Questionnaires.value = data.OMRSheets;
  } catch (error) {
    console.error('Error fetching sheets:', error);
  }
}

// Filtered list
const filteredSheets = computed(() => {
  if (!searchQuery.value.trim()) return Questionnaires.value;
  const q = searchQuery.value.toLowerCase();
  return Questionnaires.value.filter(
    (sheet) =>
      sheet.title.toLowerCase().includes(q) ||
      sheet.description?.toLowerCase().includes(q)
  );
});

// Format date
function formatDate(iso: string) {
  const date = new Date(iso);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
}

// CSRF Token
function getCsrfToken(): string {
  const meta = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
  if (!meta?.content) throw new Error('CSRF token not found');
  return meta.content;
}

// Open Delete Modal
function openDeleteModal(sheet: OmrSheet) {
  sheetToDelete.value = sheet;
  deleteConfirmModal.value = true;
}

// Confirm Delete
async function confirmDelete() {
  if (!sheetToDelete.value) return;

  try {
    const csrfToken = getCsrfToken();
    const res = await fetch(route('omr_sheet.delete'), {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrfToken,
      },
      credentials: 'include',
      body: JSON.stringify({ id: sheetToDelete.value.id }),
    });

    if (!res.ok) throw new Error('Delete failed');

    deleteConfirmModal.value = false;
    sheetToDelete.value = null;
    getOmrSheets();
  } catch (error) {
    console.error('Error deleting sheet:', error);
  }
}

onMounted(getOmrSheets);
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-5">
      <Card>
        <CardHeader>
          <div class="flex gap-2 items-center">
            <Input v-model="searchQuery" placeholder="Search OMR Sheet..." />
            <Link
              :href="route('omr_sheet.make')"
              target="_blank"
              class="px-4 py-2 bg-foreground text-background rounded text-sm"
            >
              Create
            </Link>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-1/4">Title</TableHead>
                <TableHead class="w-1/4">Description</TableHead>
                <TableHead class="w-1/4 text-right">Created At</TableHead>
                <TableHead class="w-1/4 text-center">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="sheet in filteredSheets" :key="sheet.id">
                <TableCell class="w-1/4 font-medium">{{ sheet.title }}</TableCell>
                <TableCell class="w-1/4">{{ sheet.description || 'None' }}</TableCell>
                <TableCell class="w-1/4 text-right">{{ formatDate(sheet.created_at) }}</TableCell>
                <TableCell class="w-1/4 text-center">
                  <div class="flex justify-center items-center gap-2">
                    <!-- View Button -->
                    <Link
                      :href="route('omr_sheet.view', [sheet.id])"
                      target="_blank"
                      class="w-32 px-3 py-2 text-sm bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-colors flex items-center justify-center gap-1 shadow-md"
                    >
                      View
                      <Eye class="w-4 h-4" />
                    </Link>

                    <!-- Delete Button -->
                    <Button
                      @click="openDeleteModal(sheet)"
                      class="w-32 px-3 py-2 text-sm bg-destructive text-destructive-foreground rounded-md hover:opacity-90 transition-colors flex items-center justify-center gap-1 shadow-md"
                    >
                      Delete
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <!-- Delete Modal -->
    <BaseModal
      v-model="deleteConfirmModal"
      title="Confirm Delete"
      @confirm="confirmDelete"
      @cancel="() => { deleteConfirmModal.value = false; sheetToDelete.value = null }"
    >
      <p class="text-gray-800 dark:text-gray-200">
        Are you sure you want to delete
        <strong>{{ sheetToDelete?.title }}</strong>?
      </p>
    </BaseModal>
  </AppLayout>
</template>

