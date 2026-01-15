<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { Eye } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { BaseModal } from "@/components/ui/modal";
import type { BreadcrumbItem } from "@/types";
import { Link } from "@inertiajs/vue3";

// Refs
const showModal = ref(false);
const groupName = ref("");
const groupDesc = ref("");
const searchQuery = ref("");

// Delete modal
const showConfirmDeleteModal = ref(false);
const dictionaryToDelete = ref<Dictionary | null>(null);

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: "Respondent Dictionary", href: "/dictionary" },
];

// Dictionary Type
interface Dictionary {
  id: number;
  name: string;
  description: string;
  member_count: number;
  created_at: string;
}

// Data
const dictionaries = ref<Dictionary[]>([]);

// Computed search filter
const filteredDictionaries = computed(() => {
  if (!searchQuery.value.trim()) return dictionaries.value;
  const q = searchQuery.value.toLowerCase();
  return dictionaries.value.filter(
    (dict) =>
      dict.name.toLowerCase().includes(q) ||
      dict.description?.toLowerCase().includes(q)
  );
});

// Lifecycle
onMounted(getDictionaries);

// Modal Methods
function openModal() {
  showModal.value = true;
}

function onConfirm() {
  createDictionary(groupName.value, groupDesc.value);
}

function onCancel() {
  showModal.value = false;
}

// Delete Methods
function openDeleteModal(dictionary: Dictionary) {
  dictionaryToDelete.value = dictionary;
  showConfirmDeleteModal.value = true;
}

async function confirmDelete() {
  if (!dictionaryToDelete.value) return;

  try {
    const csrfToken = getCsrfToken();
    const res = await fetch(route('dictionary.delete'), {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
      },
      credentials: "include",
      body: JSON.stringify({ id: dictionaryToDelete.value.id }),
    });

    if (!res.ok) throw new Error("Delete failed");

    showConfirmDeleteModal.value = false;
    dictionaryToDelete.value = null;
    getDictionaries();
  } catch (error) {
    console.error("Error deleting dictionary:", error);
  }
}

// Fetch Methods
async function getDictionaries() {
  try {
    const res = await fetch(route('dictionaries.get'), {
      headers: { Accept: "application/json" },
      credentials: "include",
    });

    if (!res.ok) throw new Error("Failed to fetch dictionaries");

    const data = await res.json();
    dictionaries.value = data.dictionaries;
  } catch (error) {
    console.error("Error fetching dictionaries:", error);
  }
}

async function createDictionary(name: string, description: string) {
  try {
    const csrfToken = getCsrfToken();
    const res = await fetch(route('dictionary.create'), {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
      },
      credentials: "include",
      body: JSON.stringify({ name, description }),
    });

    showModal.value = false;
    groupName.value = "";
    groupDesc.value = "";
    getDictionaries();
  } catch (error) {
    console.error("Error creating dictionary:", error);
  }
}

// Helpers
function getCsrfToken(): string {
  const metaTag = document.querySelector(
    'meta[name="csrf-token"]'
  ) as HTMLMetaElement | null;

  if (!metaTag || !metaTag.content) {
    throw new Error("CSRF token not found");
  }

  return metaTag.content;
}

function formatDate(iso: string): string {
  const date = new Date(iso);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

const isFormValid = computed(() => {
  return groupName.value.trim() !== "" && groupDesc.value.trim() !== "";
});

</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-5">
      <Card>
        <CardHeader>
          <div class="flex gap-2 items-center">
            <Input v-model="searchQuery" placeholder="Search group name or description..." />
            <Button class="text-background" @click="openModal">Create</Button>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[180px]">Group Name</TableHead>
                <TableHead>Description</TableHead>
                <TableHead class="text-right">Created At</TableHead>
                <TableHead class="text-center">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="dictionary in filteredDictionaries" :key="dictionary.id">
                <TableCell class="font-medium">{{ dictionary.name }}</TableCell>
                <TableCell>{{ dictionary.description }}</TableCell>
                <TableCell class="text-right">{{ formatDate(dictionary.created_at) }}</TableCell>
                <TableCell class="text-center w-1/4">
                  <div class="flex justify-center items-center gap-2">
                    <!-- View Button -->
                    <Link
                      :href="route('dictionary.view', [dictionary.id])"
                      target="_blank"
                      class="w-32 px-3 py-2 text-sm bg-primary text-primary-foreground rounded-md hover:opacity-90 transition-colors flex items-center justify-center gap-1 shadow-md text-center"
                    >
                      View
                      <Eye class="w-4 h-4" />
                    </Link>

                    <!-- Delete Button -->
                    <Button
                      @click="openDeleteModal(dictionary)"
                      class="w-32 px-3 py-2 text-sm bg-destructive text-destructive-foreground rounded-md hover:opacity-90 transition-colors flex items-center justify-center gap-1 shadow-md text-center"
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

    <!-- Create Modal -->
    <BaseModal
      v-model="showModal"
      title="Create New Group"
      @confirm="onConfirm"
      @cancel="onCancel"
      :confirmDisabled="!isFormValid" 
    >
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Group Name
        </label>
        <input
          v-model="groupName"
          type="text"
          placeholder="Enter group name Eg. G12-STEM"
          required
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors mb-4"
        />

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Description
        </label>
        <textarea
          v-model="groupDesc"
          required
          placeholder="A brief description for the group"
          rows="3"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors"
        ></textarea>
      </div>
    </BaseModal>

    <!-- Delete Confirmation Modal -->
    <BaseModal
      v-model="showConfirmDeleteModal"
      title="Confirm Delete"
      @confirm="confirmDelete"
      @cancel="() => { showConfirmDeleteModal = false; dictionaryToDelete = null; }"
    >
      <p class="text-gray-800 dark:text-gray-200">
        Are you sure you want to delete
        <strong>{{ dictionaryToDelete?.name }}</strong>?
      </p>
    </BaseModal>
  </AppLayout>
</template>
