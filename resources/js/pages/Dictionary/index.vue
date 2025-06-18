<script setup lang="ts">
import { ref, onMounted } from "vue";
import AppLayout from "@/layouts/AppLayout.vue";
import { Eye } from "lucide-vue-next";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import {
  Table,
  TableBody,
  TableCaption,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from "@/components/ui/table";
import { BaseModal } from "@/components/ui/modal";
import type { BreadcrumbItem } from "@/types";
import { Link } from '@inertiajs/vue3'

// Refs
const showModal = ref(false);
const groupName = ref("");
const groupDesc = ref("");

// Breadcrumbs
const breadcrumbs: BreadcrumbItem[] = [
  { title: "Person Dictionary", href: "/dictionary" },
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
  console.log("Modal cancelled");
}

// Fetch Methods
async function getDictionaries() {
  try {
    const res = await fetch("/api/user/dictionaries/get", {
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
    const res = await fetch("/api/user/dictionaries/create", {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
      },
      credentials: "include",
      body: JSON.stringify({ name, description }),
    });

    console.log(description)
    const data = await res.json();
    console.log(data);
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
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-5">
      <Card>
        <CardHeader>
          <div class="flex gap-2 items-center">
            <Input placeholder="Search person..." />
            <Button>Search</Button>
            <Button @click="openModal">Create</Button>
          </div>
        </CardHeader>
        <CardContent>
          <Table>
            <TableCaption>A list of your Person Groups</TableCaption>
            <TableHeader>
              <TableRow>
                <TableHead class="w-[180px]">Group Name</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Members</TableHead>
                <TableHead class="text-right">Created At</TableHead>
                <TableHead class="text-center">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="dictionary in dictionaries" :key="dictionary.id">
                <TableCell class="font-medium">{{ dictionary.name }}</TableCell>
                <TableCell>{{ dictionary.description }}</TableCell>
                <TableCell>{{ dictionary.member_count }}</TableCell>
                <TableCell class="text-right">{{
                  formatDate(dictionary.created_at)
                  }}</TableCell>
                <TableCell class="text-center w-1/4">
                  <div class="flex justify-center items-center">
                    <Link :href="route('dictionary.view', [dictionary.id])" target="_blank"
                      class="px-3 py-1 text-sm w-32 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-150 flex items-center justify-center gap-1 shadow-md">
                    View
                    <Eye class="w-4 h-4" />
                    </Link>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>

    <!-- Modal -->
    <BaseModal v-model="showModal" title="Create New Group" @confirm="onConfirm" @cancel="onCancel">
      <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Group Name
        </label>
        <input v-model="groupName" type="text" placeholder="Enter group name"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors mb-4" />

        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
          Description
        </label>
        <textarea v-model="groupDesc" placeholder="Optional description" rows="3"
          class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors"></textarea>
      </div>
    </BaseModal>
  </AppLayout>
</template>
