<script setup lang="ts">
import { ref, watch } from 'vue';
import type { ExportSection } from '@/types/assessment';

const props = defineProps<{
    show: boolean;
    exportableColumns: ExportSection[];
    personMetaColumns: string[];
}>();

const emit = defineEmits<{
    (e: 'update:show', value: boolean): void;
    (e: 'confirm'): void;
}>();

const selectedColumns = defineModel<string[]>('selectedColumns', { required: true });
const groupBy = defineModel<string[]>('groupBy', { required: true });
const sortBy = defineModel<string>('sortBy', { required: true });

function close() {
    emit('update:show', false);
}

function confirm() {
    emit('update:show', false);
    emit('confirm');
}

function addGroupByColumn(col: string) {
    if (!groupBy.value.includes(col)) {
        groupBy.value.push(col);
    }
}

function removeGroupByColumn(index: number) {
    groupBy.value.splice(index, 1);
}

function moveGroupByUp(index: number) {
    if (index > 0) {
        const temp = groupBy.value[index];
        groupBy.value[index] = groupBy.value[index - 1];
        groupBy.value[index - 1] = temp;
    }
}

function moveGroupByDown(index: number) {
    if (index < groupBy.value.length - 1) {
        const temp = groupBy.value[index];
        groupBy.value[index] = groupBy.value[index + 1];
        groupBy.value[index + 1] = temp;
    }
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg p-6 max-w-md w-full shadow-xl space-y-4">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Export Options</h2>
                <button @click="close" class="text-gray-400 hover:text-gray-600">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Column Selection -->
            <div class="space-y-4">
                <label class="block text-sm font-medium text-gray-700">Columns to Include: Selection order
                    matters</label>

                <div v-for="section in exportableColumns" :key="section.section">
                    <div class="text-xs font-semibold text-gray-500 mb-1">{{ section.section }}</div>
                    <div class="grid grid-cols-2 gap-2">
                        <label v-for="opt in section.options" :key="opt.value" class="flex items-center space-x-2">
                            <input type="checkbox" v-model="selectedColumns" :value="opt.value"
                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <span class="text-sm text-gray-700">{{ opt.label }}</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Group by Section -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Group by (order matters)</label>

                <!-- Available columns -->
                <div class="flex flex-wrap gap-2 mb-2">
                    <button v-for="meta in personMetaColumns" :key="'add-' + meta" :disabled="groupBy.includes(meta)"
                        @click="addGroupByColumn(meta)"
                        class="px-3 py-1.5 text-xs border rounded hover:bg-gray-100 disabled:opacity-50 text-black">
                        {{ meta }}
                    </button>
                </div>

                <!-- Selected group order list with animation -->
                <transition-group name="fade-move" tag="ul" class="space-y-2">
                    <li v-for="(col, index) in groupBy" :key="col"
                        class="flex items-center justify-between gap-2 p-2 bg-gray-50 border border-gray-300 rounded-md shadow-sm">
                        <span class="text-sm font-medium text-gray-800 flex-1 truncate">{{ col }}</span>
                        <div class="flex gap-2 items-center">
                            <!-- Up/Down in one vertical column -->
                            <div class="flex flex-col items-center gap-1">
                                <button @click="moveGroupByUp(index)"
                                    class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-black text-sm">▲</button>
                                <button @click="moveGroupByDown(index)"
                                    class="w-6 h-6 flex items-center justify-center text-gray-500 hover:text-black text-sm">▼</button>
                            </div>

                            <!-- Delete button in its own column -->
                            <div class="flex items-center justify-center">
                                <button @click="removeGroupByColumn(index)"
                                    class="w-6 h-6 flex items-center justify-center text-red-500 hover:text-red-700 text-sm">✕</button>
                            </div>
                        </div>
                    </li>
                </transition-group>
            </div>

            <!-- Sort by -->
            <div class="space-y-2">
                <label class="block text-sm font-medium text-black">Sort by</label>
                <select v-model="sortBy" class="block w-full pl-3 pr-10 py-2 text-base sm:text-sm rounded-md
              text-black bg-white border border-gray-300
              focus:outline-none focus:ring-blue-500 focus:border-blue-500
              dark:text-black dark:bg-white dark:border-gray-300">
                    <option value="">None</option>
                    <option value="name">Name</option>
                    <option value="total">Total Score</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end gap-3 pt-4">
                <button @click="close"
                    class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Cancel
                </button>
                <button @click="confirm"
                    class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Export
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-move-enter-active,
.fade-move-leave-active {
    transition: all 0.3s ease;
}

.fade-move-enter-from,
.fade-move-leave-to {
    opacity: 0;
    transform: translateY(10px);
}
</style>
