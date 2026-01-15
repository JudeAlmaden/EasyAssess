import { computed, ref, type Ref } from 'vue';
import type {
    AssessmentAnswer,
    ExportSection,
    ScoreMap,
    FreeformBlock,
} from '@/types/assessment';
import type { ScoreCalculation } from './useScoreCalculation';

interface UseExportOptions {
    answers: Ref<AssessmentAnswer[]>;
    personMetaColumns: string[];
    scoreCalc: ScoreCalculation;
    hasMcqAnswers: Ref<boolean>;
    hasFreeformAnswers: Ref<boolean>;
    hasBlankAnswers: Ref<boolean>;
    searchQuery: Ref<string>;
    omrSheet: any;
}

export function useExport(options: UseExportOptions) {
    const {
        answers,
        personMetaColumns,
        scoreCalc,
        hasMcqAnswers,
        hasFreeformAnswers,
        hasBlankAnswers,
        searchQuery,
        omrSheet,
    } = options;

    const selectedColumns = ref<string[]>(['name', 'total']);
    const groupBy = ref<string[]>([]);
    const sortBy = ref('');

    const exportableColumns = computed<ExportSection[]>(() => {
        const columns: ExportSection[] = [];

        columns.push({
            section: 'Respondent Info',
            options: [
                { label: 'Name', value: 'name' },
                ...personMetaColumns.map(col => ({ label: col, value: col }))
            ]
        });

        if (hasMcqAnswers.value) {
            columns.push({
                section: 'MCQ',
                options: [
                    { label: 'Total MCQ', value: 'total-mcq' },
                    ...scoreCalc.mcqSections.value.map(s => ({ label: `MCQ: ${s}`, value: `mcq-${s}` }))
                ]
            });
        }

        if (hasFreeformAnswers.value) {
            columns.push({
                section: 'Freeform',
                options: [
                    { label: 'Total Freeform', value: 'total-freeform' },
                    ...scoreCalc.freeformBlocks.value.map(b => ({
                        label: `Freeform: ${b.Instruction || b.id}`,
                        value: `freeform-${b.id}`
                    }))
                ]
            });
        }

        if (hasBlankAnswers.value) {
            columns.push({
                section: 'Blanks',
                options: [
                    { label: 'Total Blanks', value: 'total-blanks' },
                    ...scoreCalc.blankSections.value.map(s => ({ label: `Blank: ${s}`, value: `blank-${s}` }))
                ]
            });
        }

        columns.push({
            section: 'Summary',
            options: [
                { label: 'Total Score', value: 'total' }
            ]
        });

        return columns;
    });

    function getExportColumnValue(entry: AssessmentAnswer, col: string): string | number {
        if (col === 'name') return entry.person?.name ?? 'N/A';
        if (personMetaColumns.includes(col)) return entry.person?.[col] ?? 'N/A';

        if (col === 'total') return scoreCalc.getTotalScore(entry);
        if (col === 'total-mcq') return Object.values(scoreCalc.getMcqScores(entry.answers.mcq)).reduce((a, b) => a + b, 0);
        if (col === 'total-blanks') return Object.values(scoreCalc.getBlankScores(entry.answers.Blanks)).reduce((a, b) => a + b, 0);
        if (col === 'total-freeform') return entry.answers.Freeform.reduce((a, b) => a + b.score, 0);

        if (col.startsWith('mcq-')) {
            const section = col.slice(5);
            return scoreCalc.getMcqScores(entry.answers.mcq)[section] ?? 0;
        }

        if (col.startsWith('freeform-')) {
            const id = col.slice(9);
            return scoreCalc.getFreeformScore(entry.answers.Freeform, id);
        }

        if (col.startsWith('blank-')) {
            const section = col.slice(6);
            return scoreCalc.getBlankScores(entry.answers.Blanks)[section] ?? 0;
        }

        return '';
    }

    function exportXLSX() {
        // Dynamic import of xlsx library
        import('xlsx').then((XLSX) => {
            const headers = selectedColumns.value.map(col => {
                const found = exportableColumns.value.flatMap(group => group.options).find(opt => opt.value === col);
                return found?.label || col;
            });

            const filtered = answers.value.filter(entry =>
                (entry.person?.name ?? '').toLowerCase().includes(searchQuery.value.toLowerCase())
            );

            // Prepare data rows
            const data: any[][] = [];
            data.push(headers); // Add headers as first row

            for (const entry of filtered) {
                const row: any[] = [];

                for (const col of selectedColumns.value) {
                    row.push(getExportColumnValue(entry, col));
                }

                data.push(row);
            }

            // Create a new workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);

            // Add the worksheet to the workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Assessment Results');

            // Generate XLSX file and trigger download
            XLSX.writeFile(wb, `assessment_results_${Date.now()}.xlsx`);
        });
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

    function exportDetailedAnswers() {
        // Dynamic import of xlsx library
        import('xlsx').then((XLSX) => {
            let filtered = answers.value.filter(entry =>
                (entry.person?.name ?? '').toLowerCase().includes(searchQuery.value.toLowerCase())
            );

            if (!omrSheet?.OMRSheet?.MCQ || filtered.length === 0) {
                alert('No MCQ data available to export.');
                return;
            }

            // Sort by sortBy option if specified
            if (sortBy.value) {
                filtered = [...filtered].sort((a, b) => {
                    if (sortBy.value === 'name') {
                        const nameA = (a.person?.name ?? '').toLowerCase();
                        const nameB = (b.person?.name ?? '').toLowerCase();
                        return nameA.localeCompare(nameB);
                    } else if (sortBy.value === 'total') {
                        return scoreCalc.getTotalScore(a) - scoreCalc.getTotalScore(b);
                    } else if (personMetaColumns.includes(sortBy.value)) {
                        const valA = (a.person?.[sortBy.value] ?? '').toString().toLowerCase();
                        const valB = (b.person?.[sortBy.value] ?? '').toString().toLowerCase();
                        return valA.localeCompare(valB);
                    }
                    return 0;
                });
            }

            // Group by columns if specified
            if (groupBy.value.length > 0) {
                filtered = [...filtered].sort((a, b) => {
                    for (const col of groupBy.value) {
                        const valA = (a.person?.[col] ?? '').toString().toLowerCase();
                        const valB = (b.person?.[col] ?? '').toString().toLowerCase();
                        const cmp = valA.localeCompare(valB);
                        if (cmp !== 0) return cmp;
                    }
                    return 0;
                });
            }

            // Build list of all MCQ questions in order
            const allQuestions: { blockId: string; itemIndex: number; questionNum: number; section: string }[] = [];
            let questionCounter = 1;

            for (const block of omrSheet.OMRSheet.MCQ) {
                for (let i = 0; i < block.items; i++) {
                    allQuestions.push({
                        blockId: block.id,
                        itemIndex: i,
                        questionNum: questionCounter++,
                        section: block.section
                    });
                }
            }

            const data: any[][] = [];

            // Header row - Name, person metadata columns, then Q1, Q2, Q3..., totals
            const headerRow = ['CANDIDATE NAME',
                ...personMetaColumns,
                ...allQuestions.map(q => `Q${q.questionNum}`),
                'Total Right', 'Total Wrong', 'Total Left'];
            data.push(headerRow);

            // Answer key row
            const answerKeyRow = ['ANSWER KEY', ...personMetaColumns.map(() => '')];
            for (const q of allQuestions) {
                // Find all correct answers (for multiple-answer questions)
                let correctAnswers: string[] = [];
                for (const entry of filtered) {
                    const block = entry.answers.mcq.find(b => b.id === q.blockId);
                    if (block && block.bubbles[q.itemIndex]) {
                        const correctIndices = block.bubbles[q.itemIndex]
                            .map((b: any, idx: number) => b.isCorrect ? idx : -1)
                            .filter((idx: number) => idx !== -1);
                        if (correctIndices.length > 0) {
                            correctAnswers = correctIndices.map((idx: number) => String.fromCharCode(65 + idx));
                            break;
                        }
                    }
                }
                answerKeyRow.push(correctAnswers.join('') || '-');
            }
            answerKeyRow.push('', '', ''); // Empty cells for Total Right, Total Wrong, Total Left
            data.push(answerKeyRow);

            // Data rows for each person
            for (const entry of filtered) {
                const row: any[] = [];
                row.push(entry.person?.name ?? 'N/A');

                // Add person metadata columns
                for (const col of personMetaColumns) {
                    row.push(entry.person?.[col] ?? '');
                }

                let totalRight = 0;
                let totalWrong = 0;
                let totalLeft = 0;

                // For each question, find what this person answered
                for (const q of allQuestions) {
                    const block = entry.answers.mcq.find(b => b.id === q.blockId);
                    if (block && block.bubbles[q.itemIndex]) {
                        const bubbles = block.bubbles[q.itemIndex];
                        const shadedIndices = bubbles
                            .map((b: any, idx: number) => b.shaded ? idx : -1)
                            .filter((idx: number) => idx !== -1);

                        if (shadedIndices.length === 0) {
                            row.push(''); // No answer
                            totalLeft++;
                        } else {
                            // Multiple answers: concatenate letters (e.g., 'AB', 'ACD')
                            const answers = shadedIndices.map((idx: number) => String.fromCharCode(65 + idx)).join('');
                            row.push(answers);

                            // Check if all selected answers are correct and all correct answers are selected
                            const correctIndices = bubbles
                                .map((b: any, idx: number) => b.isCorrect ? idx : -1)
                                .filter((idx: number) => idx !== -1);

                            const allCorrect = shadedIndices.every((idx: number) => bubbles[idx].isCorrect);
                            const allSelected = correctIndices.every((idx: number) => shadedIndices.includes(idx));

                            if (allCorrect && allSelected && shadedIndices.length === correctIndices.length) {
                                totalRight++;
                            } else {
                                totalWrong++;
                            }
                        }
                    } else {
                        row.push(''); // Missing data
                        totalLeft++;
                    }
                }

                row.push(totalRight, totalWrong, totalLeft);
                data.push(row);
            }

            // Statistics rows
            const correctRow = ['CORRECT', ...personMetaColumns.map(() => '')];
            const incorrectRow = ['INCORRECT', ...personMetaColumns.map(() => '')];
            const leftRow = ['LEFT', ...personMetaColumns.map(() => '')];

            for (const q of allQuestions) {
                let correct = 0;
                let incorrect = 0;
                let left = 0;

                for (const entry of filtered) {
                    const block = entry.answers.mcq.find(b => b.id === q.blockId);
                    if (block && block.bubbles[q.itemIndex]) {
                        const bubbles = block.bubbles[q.itemIndex];
                        const shadedIndex = bubbles.findIndex((b: any) => b.shaded);

                        if (shadedIndex === -1) {
                            left++;
                        } else if (bubbles[shadedIndex].isCorrect) {
                            correct++;
                        } else {
                            incorrect++;
                        }
                    } else {
                        left++;
                    }
                }

                correctRow.push(correct);
                incorrectRow.push(incorrect);
                leftRow.push(left);
            }

            correctRow.push('', '', '');
            incorrectRow.push('', '', '');
            leftRow.push('', '', '');

            data.push(correctRow);
            data.push(incorrectRow);
            data.push(leftRow);

            // Create workbook and worksheet
            const wb = XLSX.utils.book_new();
            const ws = XLSX.utils.aoa_to_sheet(data);

            // Add the worksheet to the workbook
            XLSX.utils.book_append_sheet(wb, ws, 'Detailed Answers');

            // Generate XLSX file and trigger download
            XLSX.writeFile(wb, `assessment_detailed_answers_${Date.now()}.xlsx`);
        });
    }

    return {
        selectedColumns,
        groupBy,
        sortBy,
        exportableColumns,
        exportXLSX,
        exportDetailedAnswers,
        addGroupByColumn,
        removeGroupByColumn,
        moveGroupByUp,
        moveGroupByDown,
    };
}
