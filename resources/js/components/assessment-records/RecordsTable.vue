<script setup lang="ts">
import { inject } from 'vue';
import type { AssessmentAnswer, FreeformBlock } from '@/types/assessment';
import type { ScoreCalculation } from '@/composables/useScoreCalculation';

const props = defineProps<{
    answers: AssessmentAnswer[];
    hasMcqAnswers: boolean;
    hasFreeformAnswers: boolean;
    hasBlankAnswers: boolean;
    mcqSections: string[];
    blankSections: string[];
    freeformBlocks: FreeformBlock[];
}>();

const emit = defineEmits<{
    (e: 'delete', entry: AssessmentAnswer): void;
}>();

// Inject score calculation functions from parent
const scoreCalc = inject<ScoreCalculation>('scoreCalc');

if (!scoreCalc) {
    throw new Error('RecordsTable requires scoreCalc to be provided');
}
</script>

<template>
    <div class="overflow-x-auto shadow-sm rounded-lg border border-secondary">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-accent">
                <tr>
                    <th rowspan="2" scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                        Respondent
                    </th>

                    <template v-if="hasMcqAnswers">
                        <th :colspan="mcqSections.length" scope="col"
                            class="px-2 py-2 text-center text-xs font-medium uppercase tracking-wider border-b border-gray-200">
                            MCQ
                        </th>
                    </template>
                    <template v-if="hasFreeformAnswers">
                        <th :colspan="freeformBlocks.length" scope="col"
                            class="px-2 py-2 text-center text-xs font-medium uppercase tracking-wider border-b border-gray-200">
                            Freeform
                        </th>
                    </template>
                    <template v-if="hasBlankAnswers">
                        <th :colspan="blankSections.length" scope="col"
                            class="px-2 py-2 text-center text-xs font-medium uppercase tracking-wider border-b border-gray-200">
                            Blanks
                        </th>
                    </template>

                    <th rowspan="2" scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                        Total
                    </th>
                    <th rowspan="2" scope="col"
                        class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">
                        Actions
                    </th>
                </tr>

                <tr>
                    <template v-if="hasMcqAnswers">
                        <th v-for="section in mcqSections" :key="'mcq-' + section" scope="col"
                            class="px-3 py-2 text-xs font-medium uppercase tracking-wider">
                            {{ section }}
                        </th>
                    </template>
                    <template v-if="hasFreeformAnswers">
                        <th v-for="block in freeformBlocks" :key="'ff-' + block.id" scope="col"
                            class="px-3 py-2 text-xs font-medium uppercase tracking-wider">
                            {{ block.Instruction || block.id }}
                        </th>
                    </template>
                    <template v-if="hasBlankAnswers">
                        <th v-for="section in blankSections" :key="'blank-' + section" scope="col"
                            class="px-3 py-2 text-xs font-medium uppercase tracking-wider">
                            {{ section }}
                        </th>
                    </template>
                </tr>
            </thead>

            <tbody class="bg-background divide-y text-foreground border-secondary">
                <tr v-for="entry in answers" :key="entry.person.id" class="hover:bg-popover/50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div
                                class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-medium">
                                {{ entry.person.name?.charAt(0) || '?' }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium">
                                    {{ entry.person.name || `Respondent #${entry.person.id}` }}
                                </div>
                                <div class="text-sm text-muted-foreground">
                                    ID: {{ entry.person.id }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <template v-if="hasMcqAnswers">
                        <td v-for="section in mcqSections" :key="'mcq-cell-' + section"
                            class="px-3 py-4 whitespace-nowrap text-sm text-center text-foreground">
                            <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full">
                                {{ scoreCalc.getMcqScores(entry.answers.mcq)[section] ?? 0 }}
                            </span>
                        </td>
                    </template>

                    <template v-if="hasFreeformAnswers">
                        <td v-for="block in freeformBlocks" :key="'ff-cell-' + block.id"
                            class="px-3 py-4 whitespace-nowrap text-sm text-center text-foreground">
                            {{ scoreCalc.getFreeformScore(entry.answers.Freeform, block.id) }}
                        </td>
                    </template>

                    <template v-if="hasBlankAnswers">
                        <td v-for="section in blankSections" :key="'blank-cell-' + section"
                            class="px-3 py-4 whitespace-nowrap text-sm text-center text-foreground">
                            {{ scoreCalc.getBlankScores(entry.answers.Blanks)[section] ?? 0 }}
                        </td>
                    </template>

                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-foreground">
                        {{ scoreCalc.getTotalScore(entry) }}
                    </td>

                    <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                        <button @click="emit('delete', entry)"
                            class="text-red-600 hover:text-red-900 flex items-center gap-1">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Delete
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
