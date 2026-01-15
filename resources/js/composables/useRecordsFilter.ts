import { computed, ref, type Ref } from 'vue';
import type { AssessmentAnswer } from '@/types/assessment';

export function useRecordsFilter(answers: Ref<AssessmentAnswer[]>) {
    const searchQuery = ref('');

    const filteredAnswers = computed(() => {
        const q = searchQuery.value.toLowerCase();
        if (!q) return answers.value;
        return answers.value.filter(
            entry => {
                const name = (entry.person?.name || '').toLowerCase();
                const id = (entry.person?.id || '').toString().toLowerCase();
                return name.includes(q) || id.includes(q);
            }
        );
    });

    return {
        searchQuery,
        filteredAnswers,
    };
}
