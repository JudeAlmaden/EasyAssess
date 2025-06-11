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
    description:string
    created_at: string
    updated_at: string
}

const Questionnaires = ref<OmrSheet[]>([])

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

//Get initial data
onMounted(() => {
    fetchQuestionnaires();
});

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
                        <Link :href="route('questionnaire.make')" target="_blank">
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
                            <TableRow v-for="Questionnaire in Questionnaires" :key="Questionnaire.id">
                                <TableCell class="font-medium w-1/4">{{ Questionnaire.title }}</TableCell>
                                <TableCell class="font-medium w-1/4">{{ Questionnaire.description? Questionnaire.description: "None" }}</TableCell>
                                <TableCell class="text-right w-1/4">{{ formatDate(Questionnaire.created_at) }}</TableCell>
                                <TableCell class="text-center w-1/4">
                                    <div class="flex justify-center items-center">
                                        <Link :href="route('questionnaire.view', [Questionnaire.id])" target="_blank"
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