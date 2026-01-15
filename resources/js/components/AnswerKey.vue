<!-- eslint-disable vue/no-side-effects-in-computed-properties -->
<script setup lang="ts">
import { ref, computed } from 'vue';

const props = defineProps<{
  assessment: {
    id: number;
    omr_sheet_snapshot: {
      OMRSheet: {
        MCQ: {
          id: string;
          section: string;
          items: number;
          choices: number;
          x: number;
          y: number;
        }[];
      };
    };
    answer_key: any;
  };
  access_level: string;
}>();

// Initialize answerKey from prop
const answerKey = ref<Record<string, {
  x: number;
  y: number;
  answers: Record<number, string[]>;
}>>(props.assessment.answer_key ?? {});

// Group MCQs by section and assign global item numbers
const groupedSectionsWithGlobalItems = computed(() => {
  const mcqs = props.assessment.omr_sheet_snapshot?.OMRSheet?.MCQ ?? [];
  const sections: Record<string, any[]> = {};

  mcqs.forEach((mcq) => {
    if (!sections[mcq.section]) sections[mcq.section] = [];
    sections[mcq.section].push({ ...mcq });
  });

  for (const section in sections) {
    const sorted = sections[section].sort((a, b) => a.x - b.x || a.y - b.y);
    let globalItemNum = 1;

    sorted.forEach((block: any) => {
      const blockId = block.id;
      if (!answerKey.value[blockId]) {
        answerKey.value[blockId] = {
          x: block.x,
          y: block.y,
          answers: {}
        };
        for (let i = 1; i <= block.items; i++) {
          answerKey.value[blockId].answers[i] = [];
        }
      }

      block.globalItems = [];

      for (let i = 1; i <= block.items; i++) {
        block.globalItems.push({
          globalNumber: globalItemNum++,
          localNumber: i,
          x: block.x,
          y: block.y
        });
      }
    });

    sections[section] = sorted;
  }

  return sections;
});

// Checkbox model binding
const getCheckboxModel = (blockId: string, itemNum: number) => {
  return computed<string[]>({
    get() {
      // Ensure the answer keys exists for each box
      if (!answerKey.value[blockId]) {
        answerKey.value[blockId] = { x: 0, y: 0, answers: {} };
      }
      if (!answerKey.value[blockId].answers[itemNum]) {
        answerKey.value[blockId].answers[itemNum] = [];
      }
      return answerKey.value[blockId].answers[itemNum];
    },
    set(val) {
      //When changed
      if (!answerKey.value[blockId]) {
        answerKey.value[blockId] = { x: 0, y: 0, answers: {} };
      }
      answerKey.value[blockId].answers[itemNum] = val;
    },
  });
};

// Save function
async function saveAnswerKey() {
  const key = answerKey.value;
  if (!key) return;

  // Transform the answers from letters to numbers
  const transformedKey: typeof key = {};

  for (const blockId in key) {
    const block = key[blockId];
    const transformedAnswers: Record<number, string[]> = {};

    for (const item in block.answers) {
        const answers = block.answers[item];
        if (!answers || answers.length === 0) {
          transformedAnswers[Number(item)] = ['0'];
        } else {
          transformedAnswers[Number(item)] = answers;
        }
      }
      
  
    transformedKey[blockId] = {
      x: block.x,
      y: block.y,
      answers: transformedAnswers
    };
  }

  try {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    if (!csrf) throw new Error('Missing CSRF token');

    const res = await fetch(`/api/assessments/${props.assessment.id}/save-answer-key`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': csrf
      },
      body: JSON.stringify({ answer_key: transformedKey })
    });

    if (!res.ok) throw new Error('Save failed');
    alert('Answer key saved!');
    window.location.reload()
  } catch (err) {
    console.error(err);
    alert('Error saving answer key.');
  }
}
</script>

<template>
  <form class="space-y-10 p-4">
    <div
      v-for="(blocks, sectionName) in groupedSectionsWithGlobalItems"
      :key="sectionName"
    >
      <h2 class="text-xl font-bold text-foreground mb-4">{{ sectionName }}</h2>

      <table class="min-w-full table-auto border border-accent shadow-sm rounded-md overflow-hidden">
        <thead class="bg-accent">
          <tr>
            <th class="border px-4 py-2 text-left">Item #</th>
            <th
              v-for="choice in blocks[0]?.choices || 4"
              :key="choice"
              class="border px-4 py-2 text-center"
            >
              {{ String.fromCharCode(64 + choice) }}
            </th>
          </tr>
        </thead>
        <tbody>
          <template v-for="block in blocks" :key="block.id">
            <tr
              v-for="item in block.globalItems"
              :key="`${block.id}_${item.globalNumber}`"
              class="hover:bg-gray-50"
            >
              <td class="border px-4 py-2">{{ item.globalNumber }}</td>
              <td
                v-for="choice in block.choices"
                :key="choice"
                class="border px-4 py-2 text-center"
              >
                <input
                  type="checkbox"
                  class="form-checkbox text-blue-600 h-4 w-4"
                  :value="choice"
                  :checked="getCheckboxModel(block.id, item.localNumber).value.includes(choice)"
                  @change="(e) => {
                    const model = getCheckboxModel(block.id, item.localNumber);
                    const val = choice;
                    const checked = (e.target as HTMLInputElement).checked;

                    if (checked && !model.value.includes(val)) {
                      model.value.push(val);
                    } else if (!checked) {
                      model.value = model.value.filter(v => v !== val);
                    }
                  }"
                />

              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      <button
        @click.prevent="saveAnswerKey"
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700"
      >
        Save Answer Key
      </button>
    </div>
  </form>
</template>
