import { computed, type ComputedRef } from 'vue';
import type {
    AssessmentAnswer,
    OMRSheet,
    ScoreMap,
    MCQAnswer,
    BlankAnswer,
    FreeformAnswer,
} from '@/types/assessment';

export function useScoreCalculation(omrSheet: OMRSheet) {
    // Section maps
    const mcqSectionMap = computed(() =>
        Object.fromEntries(omrSheet.OMRSheet.MCQ.map(b => [b.id, b.section]))
    );

    const blankSectionMap = computed(() =>
        Object.fromEntries((omrSheet.OMRSheet.Blanks || []).map(b => [b.id, b.section || 'Unknown']))
    );

    // Unique section headers
    const mcqSections = computed(() => [...new Set(omrSheet.OMRSheet.MCQ.map(b => b.section))]);
    const blankSections = computed(() => [...new Set((omrSheet.OMRSheet.Blanks || []).map(b => b.section || 'Unknown'))]);
    const freeformBlocks = computed(() => omrSheet.OMRSheet.Freeform || []);

    // Section score grouping
    function getMcqScores(mcq: MCQAnswer[]): ScoreMap {
        const result: ScoreMap = {};
        for (const b of mcq) {
            const section = mcqSectionMap.value[b.id];
            if (section) result[section] = (result[section] || 0) + b.score;
        }
        return result;
    }

    function getBlankScores(blanks: BlankAnswer[]): ScoreMap {
        const result: ScoreMap = {};
        for (const b of blanks) {
            const section = blankSectionMap.value[b.id];
            if (section) result[section] = (result[section] || 0) + b.score;
        }
        return result;
    }

    function getFreeformScore(freeform: FreeformAnswer[], id: string): number {
        return freeform.find(f => f.id === id)?.score ?? 0;
    }

    function getTotalScore(entry: AssessmentAnswer): number {
        const mcq = getMcqScores(entry.answers.mcq);
        const blanks = getBlankScores(entry.answers.Blanks);
        const freeforms = entry.answers.Freeform;

        const mcqTotal = Object.values(mcq).reduce((a, b) => a + b, 0);
        const blankTotal = Object.values(blanks).reduce((a, b) => a + b, 0);
        const freeformTotal = freeforms.reduce((a, b) => a + (b.score || 0), 0);

        return mcqTotal + blankTotal + freeformTotal;
    }

    return {
        mcqSectionMap,
        blankSectionMap,
        mcqSections,
        blankSections,
        freeformBlocks,
        getMcqScores,
        getBlankScores,
        getFreeformScore,
        getTotalScore,
    };
}

export type ScoreCalculation = ReturnType<typeof useScoreCalculation>;
