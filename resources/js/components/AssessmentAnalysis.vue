<script setup lang="ts">
import { ref, computed } from 'vue';
import { Bar } from 'vue-chartjs';
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from 'chart.js';
import { Card, CardHeader, CardContent } from '@/components/ui/card';

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);

// Types
type Answer = {
  person: {
    id: number;
    name?: string;
    [key: string]: any;
  } | null;
  answers: {
    mcq: {
      id: string;
      score: number;
      bubbles: { shaded: boolean; isCorrect: boolean }[][];
    }[];
    Blanks?: {
      id: string;
      score: number;
    }[];
    Freeform?: {
      id: string;
      score: number;
    }[];
  };
};

type OMRSheet = {
  OMRSheet: {
    MCQ: {
      id: string;
      section: string;
      choices: number;
      items: number;
    }[];
    Blanks?: {
      id: string;
      section: string;
    }[];
    Freeform?: {
      id: string;
      Instruction?: string;
    }[];
  } | null;
};

// Props
const props = defineProps<{
  answers: Answer[];
  omr_sheet_snapshot: OMRSheet;
}>();

// Utility functions
const calculateStats = (values: number[]) => {
  if (!values.length) return { mean: 0, median: 0, mode: '-' };
  
  const mean = +(values.reduce((a, b) => a + b, 0) / values.length).toFixed(2);
  
  const sorted = [...values].sort((a, b) => a - b);
  const mid = Math.floor(sorted.length / 2);
  const median = +(sorted.length % 2 ? sorted[mid] : (sorted[mid - 1] + sorted[mid]) / 2).toFixed(2);
  
  const count: Record<number, number> = {};
  values.forEach(v => count[v] = (count[v] || 0) + 1);
  const max = Math.max(...Object.values(count));
  const modes = Object.entries(count).filter(([_, c]) => c === max).map(([v]) => +v);
  const mode = modes.length === 1 ? modes[0] : '-';
  
  return { mean, median, mode };
};

// MCQ Section Logic
const sortMode = ref<'standard' | 'asc' | 'desc'>('standard');
const collapsedSections = ref<Record<string, boolean>>({});

const sections = computed(() => [
  ...new Set(props.omr_sheet_snapshot?.OMRSheet.MCQ.map(b => b.section) || [])
]);

const distribution = computed(() => {
  const result: Record<string, Record<string, number[]>> = {};

  props.omr_sheet_snapshot?.OMRSheet.MCQ.forEach(block => {
    const { section, id, items, choices } = block;
    if (!result[section]) result[section] = {};

    for (let i = 0; i < items; i++) {
      result[section][`${id}:${i}`] = Array(choices).fill(0);
    }
  });

  props.answers.forEach(entry => {
    entry.answers.mcq.forEach(block => {
      block.bubbles.forEach((row, i) => {
        const selectedIndex = row.findIndex(b => b.shaded);
        const key = `${block.id}:${i}`;
        const section = props.omr_sheet_snapshot?.OMRSheet.MCQ.find(b => b.id === block.id)?.section || 'Unknown';

        if (selectedIndex !== -1 && result[section]?.[key]) {
          result[section][key][selectedIndex]++;
        }
      });
    });
  });

  return result;
});

const sectionItems = computed(() => {
  const result: Record<string, {
    blockId: string;
    itemIndex: number;
    choices: number;
    displayIndex: number;
    avgScore?: number;
  }[]> = {};
  const sectionCounters: Record<string, number> = {};

  props.omr_sheet_snapshot?.OMRSheet.MCQ.forEach(block => {
    const { section, id, items } = block;
    
    if (!result[section]) {
      result[section] = [];
      sectionCounters[section] = 1;
    }
    
    for (let i = 0; i < items; i++) {
      result[section].push({
        blockId: id,
        itemIndex: i,
        choices: block.choices,
        displayIndex: sectionCounters[section]++
      });
    }
  });

  for (const section in result) {
    result[section].forEach(item => {
      let total = 0;
      let count = 0;
      
      props.answers.forEach(entry => {
        const row = entry.answers.mcq.find(b => b.id === item.blockId)?.bubbles[item.itemIndex];
        const shaded = row?.find(b => b.shaded);
        if (shaded) {
          total += shaded.isCorrect ? 1 : 0;
          count++;
        }
      });
      
      item.avgScore = count ? total / count : 0;
    });

    if (sortMode.value !== 'standard') {
      result[section].sort((a, b) => 
        sortMode.value === 'asc' 
          ? (a.avgScore ?? 0) - (b.avgScore ?? 0) 
          : (b.avgScore ?? 0) - (a.avgScore ?? 0)
      );
    }
  }

  return result;
});

const sectionStats = computed(() => {
  const stats: Record<string, ReturnType<typeof calculateStats>> = {};
  
  for (const section in sectionItems.value) {
    const scores = sectionItems.value[section].map(i => i.avgScore ?? 0);
    stats[section] = calculateStats(scores);
  }
  
  return stats;
});

// Blanks & Freeform Logic
const blankStats = computed(() => {
  const result: Record<string, {
    scores: number[];
    stats: ReturnType<typeof calculateStats>;
  }> = {};

  props.answers.forEach(entry => {
    entry.answers.Blanks?.forEach(blank => {
      const section = props.omr_sheet_snapshot?.OMRSheet.Blanks?.find(b => b.id === blank.id)?.section || 'Unknown';
      if (!result[section]) {
        result[section] = { scores: [], stats: calculateStats([]) };
      }
      result[section].scores.push(blank.score);
    });
  });

  for (const section in result) {
    result[section].stats = calculateStats(result[section].scores);
  }

  return result;
});

const hasBlanks = computed(() => 
  props.answers.some(entry => entry.answers.Blanks?.length > 0)
);

const freeformStats = computed(() => {
  const result: Record<string, {
    instruction: string;
    scores: number[];
    stats: ReturnType<typeof calculateStats>;
  }> = {};

  props.answers.forEach(entry => {
    entry.answers.Freeform?.forEach(freeform => {
      const instruction = props.omr_sheet_snapshot?.OMRSheet.Freeform?.find(f => f.id === freeform.id)?.Instruction || freeform.id;
      if (!result[freeform.id]) {
        result[freeform.id] = {
          instruction,
          scores: [],
          stats: calculateStats([])
        };
      }
      result[freeform.id].scores.push(freeform.score);
    });
  });

  for (const id in result) {
    result[id].stats = calculateStats(result[id].scores);
  }

  return result;
});

const hasFreeform = computed(() => 
  Object.keys(freeformStats.value).length > 0
);

function exportFullStatisticsCSV() {
  const rows: string[][] = [];

  // MCQ Section Header (generated per item)
  rows.push(['MCQ Section', 'Question', 'Correct Answer', 'Choices Distribution...', 'Mean', 'Median', 'Mode']);

  for (const section of sections.value) {
    for (const item of sectionItems.value[section] || []) {
      const dist = distribution.value?.[section]?.[`${item.blockId}:${item.itemIndex}`] || Array(item.choices).fill(0);

      // Get correct option
      let correctIndex: number | null = null;
      const bubbleStructure = props.omr_sheet_snapshot.OMRSheet.MCQ.find(b => b.id === item.blockId)?.bubbles?.[item.itemIndex];
      if (bubbleStructure) {
        correctIndex = bubbleStructure.findIndex(opt => opt?.isCorrect);
      }

      const correctAnswer = correctIndex !== null && correctIndex >= 0
        ? String.fromCharCode(65 + correctIndex)
        : '-';

      // Calculate stats based on correct scores
      const scores: number[] = [];
      props.answers.forEach(entry => {
        const row = entry.answers.mcq.find(b => b.id === item.blockId)?.bubbles[item.itemIndex];
        const shaded = row?.find(b => b.shaded);
        scores.push(shaded?.isCorrect ? 1 : 0);
      });

      const stats = calculateStats(scores.filter(s => typeof s === 'number') as number[]);

      // Build header row for this item
      const choiceLabels = Array.from({ length: item.choices }, (_, i) => String.fromCharCode(65 + i));
      const row = [
        section,
        `Q${item.displayIndex}`,
        correctAnswer,
        ...dist.map(val => val.toString()),
        stats.mean.toString(),
        stats.median.toString(),
        typeof stats.mode === 'number' ? stats.mode.toString() : '-'
      ];

      // Only add choices header once (for visual reference)
      if (rows.length === 1) {
        const header = ['MCQ Section', 'Question', 'Correct Answer', ...choiceLabels, 'Mean', 'Median', 'Mode'];
        rows[0] = header;
      }

      rows.push(row);
    }
  }

  // Freeform
  if (hasFreeform.value) {
    rows.push([]);
    rows.push(['Freeform ID', 'Instruction', 'Mean', 'Median', 'Mode']);

    for (const id in freeformStats.value) {
      const stats = freeformStats.value[id];
      rows.push([
        id,
        stats.instruction,
        stats.stats.mean.toString(),
        stats.stats.median.toString(),
        typeof stats.stats.mode === 'number' ? stats.stats.mode.toString() : '-'
      ]);
    }
  }

  // Blanks
  if (hasBlanks.value) {
    rows.push([]);
    rows.push(['Blank Section', 'Mean', 'Median', 'Mode']);

    for (const section in blankStats.value) {
      const stats = blankStats.value[section].stats;
      rows.push([
        section,
        stats.mean.toString(),
        stats.median.toString(),
        typeof stats.mode === 'number' ? stats.mode.toString() : '-'
      ]);
    }
  }

  // Generate CSV content
  const csvContent = rows.map(row =>
    row.map(cell => `"${(cell ?? '').toString().replace(/"/g, '""')}"`).join(',')
  ).join('\n');

  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  link.href = URL.createObjectURL(blob);
  link.setAttribute('download', `assessment_statistics_${Date.now()}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}


</script>

<template>
<div class="mt-8 space-y-4 bg-card border border-border p-6 rounded-xl shadow-sm">
  <!-- Main Header -->
  <h2 class="text-2xl font-semibold text-foreground">Data and Analytics</h2>

  <!-- Subheader -->
  <p class="text-sm text-muted-foreground">
    See the answer distribution of each question, along with the mean, median, and mode scores for each section.
    You can also export the full statistics as a CSV file for further analysis.
  </p>

  <!-- Export Button -->
  <div class="flex justify-end">
    <button
      @click="exportFullStatisticsCSV"
      class="inline-flex items-center px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all"
    >
      Export MCQ Distribution
    </button>
  </div>
</div>


  <div class="w-full max-w-6xl mx-auto px-4">
    <!-- Blanks Section -->
    <Card v-if="hasBlanks" class="mt-6 bg-card shadow-sm rounded-xl">
      <CardHeader 
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center cursor-pointer text-md  font-semibold gap-2"
        @click="collapsedSections.blanks = !collapsedSections.blanks"
      >
        <span>Blanks Sections Statistics</span>
        <span class="text-sm font-normal text-muted-foreground">
          {{ collapsedSections.blanks ? 'Show' : 'Hide' }}
        </span>
      </CardHeader>
      <CardContent v-if="!collapsedSections.blanks">
        <div v-for="(stats, section) in blankStats" :key="section" class="mb-4 last:mb-0">
          <h3 class="font-medium text-gray-700 mb-2">{{ section }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div v-for="stat in ['mean', 'median', 'mode']" :key="stat" 
                class="bg-card p-3 rounded shadow-sm border border-border">
              <div class=" capitalize">{{ stat }}</div>
              <div class="font-semibold ">{{ stats.stats[stat as keyof typeof stats.stats] }}</div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Freeform Section -->
    <Card v-if="hasFreeform" class="mt-6 bg-card shadow-sm rounded-xl">
      <CardHeader 
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center cursor-pointer text-md  font-semibold gap-2"
        @click="collapsedSections.freeform = !collapsedSections.freeform"
      >
        <span>Freeform Sections Statistics</span>
        <span class="text-sm font-normal text-muted-foreground">
          {{ collapsedSections.freeform ? 'Show' : 'Hide' }}
        </span>
      </CardHeader>
      <CardContent v-if="!collapsedSections.freeform">
        <div v-for="(stats, id) in freeformStats" :key="id" class="mb-4 last:mb-0">
          <h3 class="font-medium  mb-2">{{ stats.instruction }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div v-for="stat in ['mean', 'median', 'mode']" :key="stat" 
                class="bg-card p-3 rounded shadow-sm border border-border">
              <div class=" capitalize">{{ stat }}</div>
              <div class="font-semibold ">{{ stats.stats[stat as keyof typeof stats.stats] }}</div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <hr class="my-6">

    <!-- MCQ Section Header and Sorting -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
      <div class="text-lg font-semibold ">MCQ Sections</div>

      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Sort by average:</label>
        <select v-model="sortMode" class="border border-gray-300 rounded-md px-2 py-1 text-sm">
          <option value="standard">Default</option>
          <option value="asc">Lowest First</option>
          <option value="desc">Highest First</option>
        </select>
      </div>
    </div>

    <!-- MCQ Sections -->
    <div class="space-y-10">
      <div
        v-for="section in sections"
        :key="section"
        class="border border-border-secondary rounded shadow-sm"
      >
        <div 
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 gap-2 cursor-pointer bg-primary-foreground" 
          @click="collapsedSections[section] = !collapsedSections[section]"
        >
          <div>
            <h2 class="text-lg font-bold text-foreground">Section: {{ section }}</h2>
            <p class="text-sm text-muted-foreground">
              Mean Score: {{ sectionStats[section]?.mean }}
              &nbsp;• Median Score: {{ sectionStats[section]?.median }}
              &nbsp;• Mode Score: {{ sectionStats[section]?.mode }}
            </p>
          </div>
          <div class="text-sm text-muted-foreground">
            {{ collapsedSections[section] ? 'Show Details' : 'Hide Details' }}
            {{ collapsedSections[section] ? 'Expand' : 'Collapse' }}
          </div>
        </div>

        <div v-if="!collapsedSections[section]" class="p-4">
          <div
            v-for="item in sectionItems[section]"
            :key="`${item.blockId}:${item.itemIndex}`"
            class="mb-6"
          >
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2 gap-2">
              <div class="text-sm font-medium ">
                Question {{ item.displayIndex }} - Avg: {{ (item.avgScore ?? 0).toFixed(2) }}
              </div>
              <div class="flex items-center gap-2">
                <div 
                  class="w-4 h-4 rounded-full" 
                  :class="{
                    'bg-green-600': item.avgScore === 1,
                    'bg-red-500': item.avgScore === 0,
                    'bg-yellow-400': item.avgScore > 0 && item.avgScore < 1
                  }" 
                />
                <div class="text-xs text-gray-600">Correctness Indicator</div>
              </div>
            </div>

            <div class="h-[200px] sm:h-[180px] md:h-[150px]">
              <Bar
                :data="{
                  labels: Array.from({ length: item.choices }, (_, i) => String.fromCharCode(65 + i)),
                  datasets: [{
                    label: 'Responses',
                    backgroundColor: '#3b82f6',
                    data: distribution[section]?.[`${item.blockId}:${item.itemIndex}`] || Array(item.choices).fill(0)
                  }]
                }"
                :options="{
                  responsive: true,
                  maintainAspectRatio: false,
                  scales: {
                    y: {
                      beginAtZero: true,
                      title: { display: true, text: 'Number of responses' }
                    },
                    x: {
                      title: { display: true, text: 'Options' }
                    }
                  },
                  plugins: {
                    legend: { display: false },
                    tooltip: {
                      callbacks: {
                        label: (context) => {
                          const total = context.dataset.data.reduce((a, b) => a + b, 0);
                          const percentage = total > 0 ? Math.round((context.raw / total) * 100) : 0;
                          return `${context.label}: ${context.raw} (${percentage}%)`;
                        }
                      }
                    }
                  }
                }"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
