<script setup lang="ts">

import { ref, onMounted } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Input } from '../../components/ui/input';
import { Button } from '../../components/ui/button';
import { Card, CardContent, CardHeader, } from '@/components/ui/card';
import { Table, TableBody, TableCaption, TableCell, TableHead, TableHeader, TableRow, } from '@/components/ui/table';
import { BaseModal } from '../../components/ui/modal'

const showModal = ref(false)
const groupName = ref('')
const groupDesc = ref('')

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Person Dictionary',
        href: '/dictionary',
    },
];

interface Dictionary {
    id: number
    name: string
    description: string
    member_count: number
    created_at: string
}

const dictionaries = ref<Dictionary[]>([
    {
        id: 0,
        name: '',
        description: '',
        member_count: 0,
        created_at: '',
    }
])

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
async function fetchDictionaries() {
    try {
        const res = await fetch('/api/user/dictionaries/get', {
            headers: {
                'Accept': 'application/json',
            },
            credentials: 'include'
        });

        if (!res.ok) throw new Error('Failed to fetch dictionaries');

        const data = await res.json();
        dictionaries.value = data.dictionaries;
    } catch (error) {
        console.error('Error fetching dictionaries:', error);
    }
}

async function createDictionary(name: string, description: string) {
    try {
        const csrfToken = getCsrfToken();
        const res = await fetch('/api/user/dictionaries/create', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken, // Include token here
            },
            credentials: 'include',
            body: JSON.stringify({
                name,
                description
            })
        });

        // if (!res.ok) throw new Error('Failed to create dictionary');

        const data = await res.json();
        console.log(data);
        // Optionally refresh the list after creating
        fetchDictionaries();
    } catch (error) {
        console.error('Error fetching dictionaries:', error);
    }
}

//Get initial data
onMounted(() => {
    fetchDictionaries();
});

//Open, Close, Confirm Modal
function openModal() {
    showModal.value = true
}

function onConfirm() {
    createDictionary(groupName.value, groupDesc.value);
}

function onCancel() {
    console.log('Modal cancelled')
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
                                <TableCell class="text-right">{{ dictionary.created_at }}</TableCell>
                                <TableCell class="text-center">
                                    <Button
                                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        View
                                    </Button>
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