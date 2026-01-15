<script setup lang="ts">
import { ref, computed } from "vue";
import { Bar } from "vue-chartjs";
import {
  Chart as ChartJS,
  BarElement,
  CategoryScale,
  LinearScale,
  Tooltip,
  Legend,
} from "chart.js";
import { Card, CardHeader, CardContent } from "@/components/ui/card";

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
  if (!values.length) return { mean: 0, median: 0, mode: "-" };

  const mean = +(values.reduce((a, b) => a + b, 0) / values.length).toFixed(2);

  const sorted = [...values].sort((a, b) => a - b);
  const mid = Math.floor(sorted.length / 2);
  const median = +(sorted.length % 2
    ? sorted[mid]
    : (sorted[mid - 1] + sorted[mid]) / 2
  ).toFixed(2);

  const count: Record<number, number> = {};
  values.forEach((v) => (count[v] = (count[v] || 0) + 1));
  const max = Math.max(...Object.values(count));
  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const modes = Object.entries(count)
    .filter(([_, c]) => c === max)
    .map(([v]) => +v);
  const mode = modes.length === 1 ? modes[0] : "-";

  return { mean, median, mode };
};

// MCQ Section Logic
const sortMode = ref<"standard" | "asc" | "desc" | "weighted-asc" | "weighted-desc" | "popularity">("standard");
const collapsedSections = ref<Record<string, boolean>>({});

const sections = computed(() => [
  ...new Set(props.omr_sheet_snapshot?.OMRSheet.MCQ.map((b) => b.section) || []),
]);

const distribution = computed(() => {
  const result: Record<string, Record<string, number[]>> = {};

  props.omr_sheet_snapshot?.OMRSheet.MCQ.forEach((block) => {
    const { section, id, items, choices } = block;
    if (!result[section]) result[section] = {};

    for (let i = 0; i < items; i++) {
      result[section][`${id}:${i}`] = Array(choices).fill(0);
    }
  });

  props.answers.forEach((entry) => {
    entry.answers.mcq.forEach((block) => {
      block.bubbles.forEach((row, i) => {
        const selectedIndex = row.findIndex((b) => b.shaded);
        const key = `${block.id}:${i}`;
        const section =
          props.omr_sheet_snapshot?.OMRSheet.MCQ.find((b) => b.id === block.id)
            ?.section || "Unknown";

        if (selectedIndex !== -1 && result[section]?.[key]) {
          result[section][key][selectedIndex]++;
        }
      });
    });
  });

  return result;
});

const sectionItems = computed(() => {
  const result: Record<
    string,
    {
      blockId: string;
      itemIndex: number;
      choices: number;
      displayIndex: number;
      avgScore?: number;
      weightedMean?: number;
      popularity?: number;
    }[]
  > = {};
  const sectionCounters: Record<string, number> = {};

  props.omr_sheet_snapshot?.OMRSheet.MCQ.forEach((block) => {
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
        displayIndex: sectionCounters[section]++,
      });
    }
  });

  for (const section in result) {
    result[section].forEach((item) => {
      let total = 0;
      let count = 0;

      props.answers.forEach((entry) => {
        const row = entry.answers.mcq.find((b) => b.id === item.blockId)?.bubbles[
          item.itemIndex
        ];
        const shaded = row?.find((b) => b.shaded);
        if (shaded) {
          total += shaded.isCorrect ? 1 : 0;
          count++;
        }
      });

      item.avgScore = count ? total / count : 0;

      // Calculate weighted mean (treating choices as 1, 2, 3, 4...)
      let weightedSum = 0;
      let weightedCount = 0;
      props.answers.forEach((entry) => {
        const row = entry.answers.mcq.find((b) => b.id === item.blockId)?.bubbles[
          item.itemIndex
        ];
        if (row) {
          const shadedIndex = row.findIndex((b) => b.shaded);
          if (shadedIndex !== -1) {
            weightedSum += shadedIndex + 1; // A=1, B=2, C=3, D=4...
            weightedCount++;
          }
        }
      });
      item.weightedMean = weightedCount ? weightedSum / weightedCount : 0;

      // Calculate most popular choice
      const choiceCounts: number[] = Array(item.choices).fill(0);
      props.answers.forEach((entry) => {
        const row = entry.answers.mcq.find((b) => b.id === item.blockId)?.bubbles[
          item.itemIndex
        ];
        if (row) {
          const shadedIndex = row.findIndex((b) => b.shaded);
          if (shadedIndex !== -1) {
            choiceCounts[shadedIndex]++;
          }
        }
      });
      const maxCount = Math.max(...choiceCounts);
      item.popularity = maxCount;
    });

    if (sortMode.value !== "standard") {
      result[section].sort((a, b) => {
        if (sortMode.value === "asc") {
          return (a.avgScore ?? 0) - (b.avgScore ?? 0);
        } else if (sortMode.value === "desc") {
          return (b.avgScore ?? 0) - (a.avgScore ?? 0);
        } else if (sortMode.value === "weighted-asc") {
          return (a.weightedMean ?? 0) - (b.weightedMean ?? 0);
        } else if (sortMode.value === "weighted-desc") {
          return (b.weightedMean ?? 0) - (a.weightedMean ?? 0);
        } else if (sortMode.value === "popularity") {
          return (b.popularity ?? 0) - (a.popularity ?? 0); // Most popular first
        }
        return 0;
      });
    }
  }

  return result;
});

const sectionStats = computed(() => {
  const stats: Record<string, ReturnType<typeof calculateStats>> = {};

  for (const section in sectionItems.value) {
    const scores = sectionItems.value[section].map((i) => i.avgScore ?? 0);
    stats[section] = calculateStats(scores);
  }

  return stats;
});

// Blanks & Freeform Logic
const blankStats = computed(() => {
  const result: Record<
    string,
    {
      scores: number[];
      stats: ReturnType<typeof calculateStats>;
    }
  > = {};

  props.answers.forEach((entry) => {
    entry.answers.Blanks?.forEach((blank) => {
      const section =
        props.omr_sheet_snapshot?.OMRSheet.Blanks?.find((b) => b.id === blank.id)
          ?.section || "Unknown";
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
  props.answers.some((entry) => entry.answers.Blanks?.length > 0)
);

const freeformStats = computed(() => {
  const result: Record<
    string,
    {
      instruction: string;
      scores: number[];
      stats: ReturnType<typeof calculateStats>;
    }
  > = {};

  props.answers.forEach((entry) => {
    entry.answers.Freeform?.forEach((freeform) => {
      const instruction =
        props.omr_sheet_snapshot?.OMRSheet.Freeform?.find((f) => f.id === freeform.id)
          ?.Instruction || freeform.id;
      if (!result[freeform.id]) {
        result[freeform.id] = {
          instruction,
          scores: [],
          stats: calculateStats([]),
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

const hasFreeform = computed(() => Object.keys(freeformStats.value).length > 0);

// Helper to find correct option index for a given block and item
const getCorrectIndex = (blockId: string, itemIndex: number): number | null => {
  const mcqBlocks = (props.omr_sheet_snapshot?.OMRSheet.MCQ || []) as any[];
  const mcqBlock = mcqBlocks.find((b) => b.id === blockId);
  const rowFromSnapshot = mcqBlock?.bubbles?.[itemIndex];
  let idx = Array.isArray(rowFromSnapshot)
    ? rowFromSnapshot.findIndex((opt: any) => opt?.isCorrect)
    : -1;
  if (idx !== -1) return idx;
  for (const entry of props.answers) {
    const block = entry.answers.mcq.find((b) => b.id === blockId);
    const row = block?.bubbles?.[itemIndex];
    if (Array.isArray(row)) {
      idx = row.findIndex((opt: any) => opt?.isCorrect);
      if (idx !== -1) return idx;
    }
  }
  return null;
};

// Build per-bar colors: green for correct, red for incorrect.
// If correct cannot be determined, default to gray.
const getBarColors = (blockId: string, itemIndex: number, choices: number): string[] => {
  const correct = getCorrectIndex(blockId, itemIndex);
  const green = "rgba(34,197,94,0.8)";
  const red = "rgba(239,68,68,0.8)";
  const gray = "rgba(107,114,128,0.5)";
  return Array.from({ length: choices }, (_, i) =>
    correct !== null ? (i === correct ? green : red) : gray
  );
};

function exportFullStatisticsXLSX() {
  // Dynamic import of xlsx library
  import('xlsx').then((XLSX) => {
    const wb = XLSX.utils.book_new();

    // MCQ Section - Main data
    const mcqData: any[][] = [];

    // Build MCQ section with proper headers
    for (const section of sections.value) {
      for (const item of sectionItems.value[section] || []) {
        const dist =
          distribution.value?.[section]?.[`${item.blockId}:${item.itemIndex}`] ||
          Array(item.choices).fill(0);

        // Get correct option
        let correctIndex: number | null = null;
        const bubbleStructure = props.omr_sheet_snapshot.OMRSheet.MCQ.find(
          (b) => b.id === item.blockId
        )?.bubbles?.[item.itemIndex];
        if (bubbleStructure) {
          correctIndex = bubbleStructure.findIndex((opt) => opt?.isCorrect);
        }

        const correctAnswer =
          correctIndex !== null && correctIndex >= 0
            ? String.fromCharCode(65 + correctIndex)
            : "-";

        // Calculate stats based on correct scores
        const scores: number[] = [];
        props.answers.forEach((entry) => {
          const row = entry.answers.mcq.find((b) => b.id === item.blockId)?.bubbles[
            item.itemIndex
          ];
          const shaded = row?.find((b) => b.shaded);
          scores.push(shaded?.isCorrect ? 1 : 0);
        });

        const stats = calculateStats(
          scores.filter((s) => typeof s === "number") as number[]
        );

        // Build header row for this item (only once)
        if (mcqData.length === 0) {
          const choiceLabels = Array.from({ length: item.choices }, (_, i) =>
            String.fromCharCode(65 + i)
          );
          const header = [
            "MCQ Section",
            "Question",
            "Correct Answer",
            ...choiceLabels,
            "Mean",
            "Median",
            "Mode",
          ];
          mcqData.push(header);
        }

        const row = [
          section,
          `Q${item.displayIndex}`,
          correctAnswer,
          ...dist.map((val) => val),
          stats.mean,
          stats.median,
          typeof stats.mode === "number" ? stats.mode : "-",
        ];

        mcqData.push(row);
      }
    }

    // Add MCQ sheet
    if (mcqData.length > 0) {
      const wsMcq = XLSX.utils.aoa_to_sheet(mcqData);
      XLSX.utils.book_append_sheet(wb, wsMcq, 'MCQ Distribution');
    }

    // Freeform Section
    if (hasFreeform.value) {
      const freeformData: any[][] = [];
      freeformData.push(["Freeform ID", "Instruction", "Mean", "Median", "Mode"]);

      for (const id in freeformStats.value) {
        const stats = freeformStats.value[id];
        freeformData.push([
          id,
          stats.instruction,
          stats.stats.mean,
          stats.stats.median,
          typeof stats.stats.mode === "number" ? stats.stats.mode : "-",
        ]);
      }

      const wsFreeform = XLSX.utils.aoa_to_sheet(freeformData);
      XLSX.utils.book_append_sheet(wb, wsFreeform, 'Freeform Statistics');
    }

    // Blanks Section
    if (hasBlanks.value) {
      const blanksData: any[][] = [];
      blanksData.push(["Blank Section", "Mean", "Median", "Mode"]);

      for (const section in blankStats.value) {
        const stats = blankStats.value[section].stats;
        blanksData.push([
          section,
          stats.mean,
          stats.median,
          typeof stats.mode === "number" ? stats.mode : "-",
        ]);
      }

      const wsBlanks = XLSX.utils.aoa_to_sheet(blanksData);
      XLSX.utils.book_append_sheet(wb, wsBlanks, 'Blanks Statistics');
    }

    // Generate XLSX file and trigger download
    XLSX.writeFile(wb, `assessment_statistics_${Date.now()}.xlsx`);
  });
}
</script>

<template>
  <div class="mt-8 space-y-4 bg-card border border-border p-6 rounded-xl shadow-sm">
    <!-- Main Header -->
    <h2 class="text-2xl font-semibold text-foreground">Data and Analytics</h2>

    <!-- Subheader -->
    <p class="text-sm text-muted-foreground">
      See the answer distribution of each question, along with the mean, median, and mode
      scores for each section. You can also export the full statistics as an XLSX file for
      further analysis.
    </p>

    <!-- Export Button -->
    <div class="flex justify-end">
      <button @click="exportFullStatisticsXLSX"
        class="inline-flex items-center px-4 py-2 text-sm font-medium bg-primary text-primary-foreground rounded-lg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all">
        Export Statistics (XLSX)
      </button>
    </div>
  </div>

  <div class="w-full max-w-6xl mx-auto px-4">
    <!-- Blanks Section -->
    <Card v-if="hasBlanks" class="mt-6 bg-card shadow-sm rounded-xl">
      <CardHeader
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center cursor-pointer text-md font-semibold gap-2"
        @click="collapsedSections.blanks = !collapsedSections.blanks">
        <span>Blanks Sections Statistics</span>
        <span class="text-sm font-normal text-muted-foreground">
          {{ collapsedSections.blanks ? "Show" : "Hide" }}
        </span>
      </CardHeader>
      <CardContent v-if="!collapsedSections.blanks">
        <div v-for="(stats, section) in blankStats" :key="section" class="mb-4 last:mb-0">
          <h3 class="font-medium text-gray-700 mb-2">{{ section }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div v-for="stat in ['mean', 'median', 'mode']" :key="stat"
              class="bg-card p-3 rounded shadow-sm border border-border">
              <div class="capitalize">{{ stat }}</div>
              <div class="font-semibold">
                {{ stats.stats[stat as keyof typeof stats.stats] }}
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Freeform Section -->
    <Card v-if="hasFreeform" class="mt-6 bg-card shadow-sm rounded-xl">
      <CardHeader
        class="flex flex-col sm:flex-row justify-between items-start sm:items-center cursor-pointer text-md font-semibold gap-2"
        @click="collapsedSections.freeform = !collapsedSections.freeform">
        <span>Freeform Sections Statistics</span>
        <span class="text-sm font-normal text-muted-foreground">
          {{ collapsedSections.freeform ? "Show" : "Hide" }}
        </span>
      </CardHeader>
      <CardContent v-if="!collapsedSections.freeform">
        <div v-for="(stats, id) in freeformStats" :key="id" class="mb-4 last:mb-0">
          <h3 class="font-medium mb-2">{{ stats.instruction }}</h3>
          <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 text-sm">
            <div v-for="stat in ['mean', 'median', 'mode']" :key="stat"
              class="bg-card p-3 rounded shadow-sm border border-border">
              <div class="capitalize">{{ stat }}</div>
              <div class="font-semibold">
                {{ stats.stats[stat as keyof typeof stats.stats] }}
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <hr class="my-6" />

    <!-- MCQ Section Header and Sorting -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2 mb-4">
      <div class="text-lg font-semibold">MCQ Sections</div>

      <div class="flex items-center gap-2">
        <label class="text-sm text-gray-600">Sort by average:</label>
        <select v-model="sortMode"
          class="px-3 py-2 border border-gray-300 rounded-md shadow-sm text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:border-gray-600 dark:text-white">
          <option value="standard">Standard Order</option>
          <option value="desc">Hardest First (Correctness)</option>
          <option value="asc">Easiest First (Correctness)</option>
          <option value="weighted-desc">Highest Avg Choice (A=1, B=2...)</option>
          <option value="weighted-asc">Lowest Avg Choice (A=1, B=2...)</option>
          <option value="popularity">Most Popular Answer</option>
        </select>
      </div>
    </div>

    <!-- MCQ Sections -->
    <div class="space-y-10">
      <div v-for="section in sections" :key="section" class="border border-border-secondary rounded shadow-sm">
        <div
          class="flex flex-col sm:flex-row sm:items-center sm:justify-between p-4 gap-2 cursor-pointer bg-primary-foreground"
          @click="collapsedSections[section] = !collapsedSections[section]">
          <div>
            <h2 class="text-lg font-bold text-foreground">Section: {{ section }}</h2>
            <p class="text-sm text-muted-foreground">
              Mean Score: {{ sectionStats[section]?.mean }} &nbsp;• Median Score:
              {{ sectionStats[section]?.median }} &nbsp;• Mode Score:
              {{ sectionStats[section]?.mode }}
            </p>
          </div>
          <div class="text-sm text-muted-foreground">
            {{ collapsedSections[section] ? "Show Details" : "Hide Details" }}
            {{ collapsedSections[section] ? "Expand" : "Collapse" }}
          </div>
        </div>

        <div v-if="!collapsedSections[section]" class="p-4">
          <div v-for="item in sectionItems[section]" :key="`${item.blockId}:${item.itemIndex}`" class="mb-6">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-2 gap-2">
              <div class="text-sm font-medium">
                Question {{ item.displayIndex }} - Avg:
                {{ (item.avgScore ?? 0).toFixed(2) }}
              </div>
              <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded-full" :class="{
                  'bg-green-600': item.avgScore === 1,
                  'bg-red-500': item.avgScore === 0,
                  'bg-yellow-400': item.avgScore > 0 && item.avgScore < 1,
                }" />
                <div class="text-xs text-gray-600">Correctness Indicator</div>
              </div>
            </div>

            <div class="h-[200px] sm:h-[180px] md:h-[150px]">
              <Bar :data="{
                labels: Array.from({ length: item.choices }, (_, i) =>
                  String.fromCharCode(65 + i)
                ),
                datasets: [
                  {
                    label: 'Responses',
                    backgroundColor: getBarColors(
                      item.blockId,
                      item.itemIndex,
                      item.choices
                    ),
                    data:
                      distribution[section]?.[`${item.blockId}:${item.itemIndex}`] ||
                      Array(item.choices).fill(0),
                  },
                ],
              }" :options="{
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                  y: {
                    beginAtZero: true,
                    title: { display: true, text: 'Number of responses' },
                  },
                  x: {
                    title: { display: true, text: 'Options' },
                  },
                },
                plugins: {
                  legend: { display: false },
                  tooltip: {
                    callbacks: {
                      label: (context) => {
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage =
                          total > 0 ? Math.round((context.raw / total) * 100) : 0;
                        return `${context.label}: ${context.raw} (${percentage}%)`;
                      },
                    },
                  },
                },
              }" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
