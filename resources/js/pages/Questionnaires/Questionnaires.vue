<script setup lang="ts">

import { ref, onMounted } from 'vue';
import { Eye } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Card, CardContent, CardHeader, } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table';
import { BaseModal } from '../../components/ui/modal'
import { Link } from '@inertiajs/vue3';

const showModal = ref(false)
const QuestionnaireName = ref('')

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Your OMR Sheets',
        href: '/OMR Sheets',
    },
];

//Rename
interface OmrSheet {
    id: number
    owner_id: number
    title: string
    sections: any[] // or a more specific type if you know the shape
    html_content: string
    created_at: string
    updated_at: string
}

const Questionnaires = ref<OmrSheet[]>([])


function getCsrfToken(): string {
    const metaTag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement | null;

    if (!metaTag) {
        throw new Error('CSRF token meta tag not found');
    }

    const token = metaTag.content;

    if (!token) {
        throw new Error('CSRF token content is empty');
    }

    return token;
}

//Queries
async function fetchQuestionnaires() {
    try {
        const res = await fetch('api/questionnaires/get', {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'include'
        });

        if (!res.ok) throw new Error('Failed to fetch dictionaries');

        const data = await res.json();
        Questionnaires.value = data.OMRSheets;

    } catch (error) {
        console.error('Error fetching dictionaries:', error);
    }
}

async function createDictionary(name: string) {
    try {
        const csrfToken = getCsrfToken();
        //change this
        const res = await fetch('api/questionnaires/create', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            credentials: 'include',
            body: JSON.stringify({
                title: name
            })
        });

        if (!res.ok) throw new Error('Failed to create OMR Sheet');
        fetchQuestionnaires();
    } catch (error) {
        console.error('Error fetching dictionaries:', error);
    }
}

//Get initial data
onMounted(() => {
    fetchQuestionnaires();
});

//Open, Close, Confirm Modal
function openModal() {
    showModal.value = true
}

function onConfirm() {
    createDictionary(QuestionnaireName.value);
}

function onCancel() {
    console.log('Modal cancelled')
}

function formatDate(iso: string) {
    const date = new Date(iso)
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    })
}

</script>



<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-5">
            <Card>
                <CardHeader>
                    <div class="flex gap-2 items-center">
                        <Input placeholder="Search OMR Sheet..." />
                        <Button>Search</Button>
                        <Button @click="openModal">Create</Button>
                    </div>
                </CardHeader>
                <Link :href="route('questionnaire.make')" target="_blank">Open in New Tab</Link>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead class="w-1/2">Title</TableHead>
                                <TableHead class="w-1/4 text-right">Created At</TableHead>
                                <TableHead class="w-1/4 text-center">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="Questionnaire in Questionnaires" :key="Questionnaire.id">
                                <TableCell class="font-medium w-1/2">{{ Questionnaire.title }}</TableCell>
                                <TableCell class="text-right w-1/4">{{ formatDate(Questionnaire.created_at) }}</TableCell>
                                <TableCell class="text-center w-1/4">
                                    <div class="flex justify-center items-center">
                                        <Button
                                            class="px-3 py-1 text-sm w-32 bg-blue-600 text-white hover:bg-blue-700 flex items-center gap-1">
                                            View
                                            <Eye class="w-4 h-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>

        <!-- Modal -->
        <BaseModal v-model="showModal" title="Create New OMR Sheet" @confirm="onConfirm" @cancel="onCancel">
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Questionnaire Name
                </label>
                <input v-model="QuestionnaireName" type="text" placeholder="Ex. Prelim Examination Layout"
                    class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-500 dark:bg-gray-700 dark:text-white transition-colors mb-4" />
            </div>
        </BaseModal>
    </AppLayout>
</template>
<style>
/* Modal transition animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

/* Focus styles for better accessibility */
button:focus,
input:focus,
textarea:focus {
    outline: none;
    ring: 2px;
}
</style>