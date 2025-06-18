<script setup lang="ts">
import { ref, onMounted } from 'vue';
import { Eye } from 'lucide-vue-next';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { BaseModal } from '@/components/ui/modal';

import { Link } from '@inertiajs/vue3'

const showModal = ref(false);
const step = ref(1);
const assessmentTitleRef = ref('');
const assessmentDescriptionRef = ref('');
const omrSheetRef = ref<number | null>(null);
const respondentRef = ref<number | null>(null);
const modalComplete = ref(false);

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Assessments', href: '/assessments' },
];

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

interface Assessment {
    id: number;
    title:string;
    name: string;
    description: string;
    created_at: string;
}

const PersonDictionary = ref<Dictionary[]>([]);
const Questionnaires = ref<OmrSheet[]>([]);
const Assessments = ref<Assessment[]>([]);

function getCsrfToken(): string {
    const metaTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;
    if (!metaTag || !metaTag.content) throw new Error('CSRF token not found');
    return metaTag.content;
}

function onConfirm() {
    const title = assessmentTitleRef.value;
    const description = assessmentDescriptionRef.value;
    const omrSheet = omrSheetRef.value;
    const respondent = respondentRef.value;

    if (!title || !description || !omrSheet || !respondent) {
        alert("Please fill in all required fields.");
        return;
    }

    createAssessment(title, description, omrSheet, respondent);
    resetModal();
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

function openModal() {
    showModal.value = true;
}

async function createAssessment(title: string, description: string, sheet: number, respondents: number) {
    try {
        const csrfToken = getCsrfToken();
        const res = await fetch("/api/assessments/create", {
            method: "POST",
            headers: {
                Accept: "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
            },
            credentials: "include",
            body: JSON.stringify({ title, description, sheet, respondents}),
        });

        if (!res.ok) throw new Error("Failed to create");
        const data = await res.json();
        console.log(data);
        await getAssessment(); // refresh list
    } catch (error) {
        console.error("Error creating assessment", error);
    }
}

async function getAssessment() {
    try {
        const res = await fetch("/api/user/assessments/get", {
            headers: { Accept: "application/json" },
            credentials: "include",
        });

        if (!res.ok) throw new Error("Failed to fetch assessments");

        const data = await res.json();
        Assessments.value = data.data.map((item: any) => item.assessment);

    } catch (error) {
        console.error("Error fetching assessments:", error);
    }
}

async function getOmrSheets() {
    try {
        const res = await fetch('/api/omr-sheets/get', {
            headers: { Accept: 'application/json' },
            credentials: 'include'
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
        const res = await fetch("/api/user/dictionaries/get", {
            headers: { Accept: "application/json" },
            credentials: "include",
        });

        if (!res.ok) throw new Error("Failed to fetch dictionaries");

        const data = await res.json();
        PersonDictionary.value = data.dictionaries;
    } catch (error) {
        console.error("Error fetching dictionaries:", error);
    }
}

function formatDate(dateString: string) {
    const date = new Date(dateString);
    return date.toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
}

onMounted(() => {
    getOmrSheets();
    getAssessment();
    getDictionaries();
});
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
                                <TableHead class="w-[180px]">Assessment</TableHead>
                                <TableHead>Description</TableHead>
                                <TableHead class="text-right">Created At</TableHead>
                                <TableHead class="text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <TableRow v-for="assessment in Assessments" :key="assessment.id">
                                <TableCell class="font-medium">{{ assessment.title }}</TableCell>
                                <TableCell>{{ assessment.description || '—' }}</TableCell>
                                <TableCell class="text-right">{{ formatDate(assessment.created_at) }}</TableCell>
                                <TableCell class="text-center w-1/4">
                                    <div class="flex justify-center">
                                        <Link :href="route('assessment.view', [assessment.id])" target="_blank"
                                            class="w-32 px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center gap-1 shadow-md">
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
        <BaseModal v-model="showModal" :showFooter="modalComplete" title="Create New Group" @confirm="onConfirm"
            @cancel="onCancel">
            <div v-if="step === 1">
                <!-- Step 1: Group Name & Description -->
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Assessment Title
                </label>
                <input v-model="assessmentTitleRef" type="text" placeholder="Enter group name"
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


            <!-- Step 2: Dropdowns -->
            <div v-else-if="step === 2">
                <!-- OMR Sheet -->
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

                <!-- Respondent Dictionary -->
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Reference for Respondents
                </label>
                <select v-model="respondentRef"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 mb-4">
                    <option :value="null" disabled>-- Please select --</option>
                    <option v-for="dict in PersonDictionary" :key="dict.id" :value="dict.id">
                        {{ dict.name }}
                    </option>
                </select>
            </div>


            <!-- Pagination Dots -->
            <div class="flex justify-center mt-6">
                <span class="h-2 w-2 rounded-full mx-1" :class="step === 1 ? 'bg-blue-600' : 'bg-gray-400'"></span>
                <span class="h-2 w-2 rounded-full mx-1" :class="step === 2 ? 'bg-blue-600' : 'bg-gray-400'"></span>
            </div>
        </BaseModal>
    </AppLayout>
</template>
