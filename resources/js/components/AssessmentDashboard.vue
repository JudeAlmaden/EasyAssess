<template>
  <div class="space-y-8">
    <!-- Header Section -->
    <div class="space-y-2">
      <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Assessment Overview</h1>
      <p class="text-lg text-muted-foreground max-w-3xl">
        Detailed insights and management tools for your assessment:
        <span class="font-medium text-gray-900">{{
          assessment.title || "Untitled Assessment"
          }}</span>
      </p>
    </div>

    <!-- Assessment Details Card -->
    <div class="rounded-xl bg-card shadow-sm border overflow-hidden">
      <div class="p-6 sm:p-8">
        <div class="flex items-start justify-between">
          <div class="space-y-2">
            <h2 class="text-2xl font-semibold">
              {{ assessment.title || "Untitled Assessment" }}
            </h2>
            <p class="text-muted-foreground">
              {{ assessment.description || "No description provided" }}
            </p>
          </div>
          <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-sm font-medium text-blue-700">
            Active
          </span>
        </div>
      </div>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
      <!-- Recorded Answers -->
      <div class="overflow-hidden rounded-lg shadow-sm border border-accent bg-card">
        <div class="p-5">
          <div class="flex items-start">
            <div class="flex-shrink-0 rounded-md bg-blue-500 p-3">
              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dt class="truncate text-sm font-medium text">Recorded Answers</dt>
              <dd class="flex items-baseline">
                <p class="text-2xl font-semibold">{{ assessment.answers.length }}</p>
              </dd>
            </div>
          </div>
        </div>
      </div>

      <!-- Dictionary Stats -->
      <div v-if="assessment.person_dictionary_snapshot"
        class="overflow-hidden rounded-lg border-accent bg shadow-sm border">
        <div class="p-5">
          <div class="flex items-start">
            <div class="flex-shrink-0 rounded-md bg-purple-500 p-3">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dt class="truncate text-sm font-medium">Respondent List used</dt>
              <dd class="flex items-baseline">
                <p class="text-xl font-semibold">
                  {{ assessment.person_dictionary_snapshot.name || "Untitled" }}
                </p>
              </dd>
              <p class="mt-1 text-sm text-gray-500">
                {{ assessment.person_dictionary_snapshot.data.length }} entries
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- OMR Sheet Stats -->
      <div v-if="assessment.omr_sheet_snapshot"
        class="overflow-hidden rounded-lg border-accent bg-card shadow-sm border">
        <div class="p-5">
          <div class="flex items-start">
            <div class="flex-shrink-0 rounded-md bg-green-500 p-3">
              <svg class="h-6 w-6 e" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dt class="truncate text-sm font-medium">OMR Sheet Sections</dt>
              <dd class="flex items-baseline">
                <p class="text-2xl font-semibold">
                  {{ Object.keys(assessment.omr_sheet_snapshot).length }}
                </p>
              </dd>
            </div>
          </div>
        </div>
      </div>

      <!-- Share Code Stats -->
      <div class="overflow-hidden rounded-lg shadow-sm border border-accent bg-card">
        <div class="p-5">
          <div class="flex items-start">
            <div class="flex-shrink-0 rounded-md bg-amber-500 p-3">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
              </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dt class="truncate text-sm font-medium">Share Code</dt>
              <dd v-if="localShareCode" class="space-y-2">
                <div class="flex items-center">
                  <div class="text-xs font-mono w-full">{{ localShareCode.code }}</div>
                  <span :class="[
                    'ml-2 px-2 py-0.5 rounded-full text-xs font-medium',
                    localShareCode.enabled
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800',
                  ]">
                    {{ localShareCode.enabled ? "On" : "Off" }}
                  </span>
                </div>
                <p class="text-xs text-gray-500">
                  Expires:
                  {{
                    localShareCode.expires_at
                      ? new Date(localShareCode.expires_at).toLocaleString()
                      : "Never"
                  }}
                </p>
                <button v-if="localShareCode.enabled" @click="showQRCodeModal = true"
                  class="text-xs text-amber-600 hover:text-amber-700 font-medium flex items-center gap-1">
                  <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                  </svg>
                  Show QR Code
                </button>
              </dd>
              <dd v-else class="text-sm text-gray-500 italic">No active share code</dd>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Action Cards Section -->
    <div class="space-y-5">
      <h3 class="text-lg font-medium text-foreground">Quick Actions</h3>

      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
        <!-- Record Answers Card -->
        <div class="overflow-hidden rounded-lg bg-card shadow-sm border border-border col-span-2">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-md bg-primary/10 p-3">
                <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-base font-medium text-foreground">Record Answers</h3>
                <p class="text-sm text-muted-foreground">
                  Enter or update assessment responses
                </p>
              </div>
            </div>
            <div class="mt-5">
              <a :href="route('assessment.record.view', [assessment.id])"
                class="w-full flex justify-center items-center gap-2 px-6 py-3 rounded-lg shadow-lg text-base font-semibold bg-blue-600 text-white hover:bg-blue-700 hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
                Go to Recording
              </a>
            </div>
          </div>
        </div>

        <!-- Share Management Card -->
        <div class="overflow-hidden rounded-lg bg-card shadow-sm border border-border">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-md bg-purple-100 dark:bg-purple-900/20 p-3">
                <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-base font-medium text-foreground">Sharing Options</h3>
                <p class="text-sm text-muted-foreground">
                  Manage access to this assessment
                </p>
              </div>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3">
              <button @click="showShareCodeModal = true"
                class="flex justify-center items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 transition">
                Generate Link
              </button>

              <button @click="localShareCode?.enabled ? copyAssistantLink() : null" :disabled="!localShareCode?.enabled"
                :class="[
                  'flex justify-center items-center px-4 py-2 rounded-md text-sm font-medium transition',
                  localShareCode?.enabled
                    ? 'bg-muted text-foreground hover:bg-muted/80'
                    : 'bg-muted text-muted-foreground cursor-not-allowed',
                ]">
                Copy Link
              </button>

              <button @click="disableShareCode" :disabled="!localShareCode?.enabled" :class="[
                'col-span-2 flex justify-center items-center px-4 py-2 rounded-md text-sm font-medium transition',
                localShareCode?.enabled
                  ? 'bg-destructive text-destructive-foreground hover:bg-destructive/90'
                  : 'bg-muted text-muted-foreground cursor-not-allowed',
              ]">
                Disable Access
              </button>
            </div>
          </div>
        </div>

        <!-- Download Card -->
        <div class="overflow-hidden rounded-lg bg-card shadow-sm border border-border">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0 rounded-md bg-green-100 dark:bg-green-900/20 p-3">
                <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24"
                  stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                </svg>
              </div>
              <div class="ml-4">
                <h3 class="text-base font-medium text-foreground">Download OMR Sheet</h3>
                <p class="text-sm text-muted-foreground">Download assessment materials</p>
              </div>
            </div>
            <div class="mt-5">
              <Link :href="route('assessment.download', [assessment.id])"
                class="w-full flex justify-center items-center px-4 py-2 rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition">
              Download Sheet
              </Link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showShareCodeModal"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div class="bg-card border border-border rounded-xl p-6 w-full max-w-md shadow-xl space-y-4">
        <h2 class="text-lg font-semibold text-foreground">Generate Share Code</h2>
        <div class="space-y-2">
          <label class="block text-sm font-medium text-foreground">Expiration</label>
          <select v-model="expiresIn"
            class="w-full px-3 py-2 rounded-md border border-border bg-background text-foreground text-sm">
            <option value="">No Expiration</option>
            <option value="15">15 minutes</option>
            <option value="60">1 hour</option>
            <option value="1440">1 day</option>
            <option value="10080">1 week</option>
            <option value="custom">Custom…</option>
          </select>

          <div v-if="expiresIn === 'custom'" class="pt-2">
            <label class="block text-sm text-foreground mb-1">Custom (minutes)</label>
            <input type="number" v-model="customMinutes" min="1"
              class="w-full rounded border border-border bg-background text-foreground px-3 py-2 text-sm"
              placeholder="Enter minutes" />
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button @click="showShareCodeModal = false"
            class="px-4 py-2 text-sm bg-muted rounded hover:bg-muted/70 text-foreground">
            Cancel
          </button>
          <button @click="submitShareCode" class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded">
            Generate
          </button>
        </div>
      </div>
    </div>

    <!-- QR Code Modal -->
    <div v-if="showQRCodeModal" @click="showQRCodeModal = false"
      class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-50">
      <div @click.stop class="bg-card border border-border rounded-xl p-6 w-full max-w-md shadow-xl space-y-4">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-foreground">Share QR Code</h2>
          <button @click="showQRCodeModal = false" class="text-muted-foreground hover:text-foreground">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="flex flex-col items-center space-y-4">
          <div class="bg-white p-4 rounded-lg border-2 border-gray-200">
            <canvas ref="qrCanvas"></canvas>
          </div>

          <div class="text-center space-y-2">
            <p class="text-sm text-muted-foreground">
              Scan this QR code to access the assessment
            </p>
            <p class="text-xs font-mono text-foreground bg-muted px-3 py-1 rounded">
              {{ localShareCode?.code }}
            </p>
          </div>
        </div>

        <div class="flex justify-end gap-2 pt-2">
          <button @click="showQRCodeModal = false"
            class="px-4 py-2 text-sm bg-muted rounded hover:bg-muted/70 text-foreground">
            Close
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted, nextTick } from "vue";
import { Link } from "@inertiajs/vue3";
import QRCode from "qrcode";

const props = defineProps<{
  assessment: {
    id: number;
    title: string | null;
    description: string | null;
    answer_key: any;
    answers: any[];
    person_dictionary_snapshot: {
      name?: string;
      meta_columns: string[];
      data: { [key: string]: any }[];
    } | null;
    share_code: string | null;
    omr_sheet_snapshot: Record<string, any> | null;
  };
}>();

const showShareCodeModal = ref(false);
const showQRCodeModal = ref(false);
const expiresIn = ref("");
const customMinutes = ref("");
const qrCanvas = ref<HTMLCanvasElement | null>(null);

const localShareCode = ref<null | {
  code: string;
  enabled: boolean;
  expires_at: string | null;
  created_at: string;
}>(null);

watch(expiresIn, (val) => {
  if (val !== "custom") {
    customMinutes.value = "";
  }
});

async function submitShareCode() {
  const minutes = expiresIn.value === "custom" ? customMinutes.value : expiresIn.value;

  try {
    const response = await fetch(
      route("assessment.code.generate", [props.assessment.id]),
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN":
            document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
            "",
        },
        body: JSON.stringify({
          expires_in_minutes: minutes || null,
          enabled: true,
        }),
      }
    );

    if (!response.ok) throw new Error("Failed to generate share code.");

    await getLatestShareCode();

    showShareCodeModal.value = false;
    expiresIn.value = "";
    customMinutes.value = "";
  } catch (error) {
    console.error(error);
  }
}

async function disableShareCode() {
  try {
    const response = await fetch(
      route("assessment.code.disable", [props.assessment.id]),
      {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "X-CSRF-TOKEN":
            document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
            "",
        },
      }
    );

    if (!response.ok) throw new Error("Failed to disable share code.");

    await getLatestShareCode();
  } catch (error) {
    console.error(error);
    alert("There was an error disabling the share code.");
  }
}

async function getLatestShareCode() {
  try {
    const response = await fetch(route("assessment.code.get", [props.assessment.id]), {
      method: "POST",
      headers: {
        Accept: "application/json",
        "X-CSRF-TOKEN":
          document.querySelector('meta[name="csrf-token"]')?.getAttribute("content") ||
          "",
      },
    });

    if (!response.ok) {
      const errorText = await response.text();
      console.error("Error response body:", errorText);
      throw new Error(`Failed to fetch share code. Status: ${response.status}`);
    }

    const data = await response.json();
    localShareCode.value = data.share_code ?? null;
  } catch (error) {
    console.error("Fetch error:", error);
  }
}

function copyAssistantLink() {
  // Use window.location.origin to get the base URL dynamically
  const base = window.location.origin;

  const relative = route(
    "assessment.record.assistant.view",
    {
      id: props.assessment.id,
      code: localShareCode.value?.code,
    },
    false
  );

  const url = base + relative;

  if (navigator.clipboard && navigator.clipboard.writeText) {
    navigator.clipboard.writeText(url)
      .then(() => alert('Assistant link copied to clipboard.'))
      .catch(() => alert('Failed to copy assistant link.'));
  } else {
    // fallback for unsupported browsers
    const textarea = document.createElement('textarea');
    textarea.value = url;
    document.body.appendChild(textarea);
    textarea.select();
    try {
      document.execCommand('copy');
      alert('Assistant link copied to clipboard.');
    } catch (err) {
      console.log(err);
      alert('Copy failed.');
    }
    document.body.removeChild(textarea);
  }
}

function generateQRCode() {
  if (!qrCanvas.value || !localShareCode.value?.enabled) return;

  // Use window.location.origin to get the base URL dynamically
  const base = window.location.origin;

  const relative = route(
    "assessment.record.assistant.view",
    {
      id: props.assessment.id,
      code: localShareCode.value.code,
    },
    false
  ); // <-- important: relative route only

  const url = base + relative;

  QRCode.toCanvas(
    qrCanvas.value,
    url,
    {
      width: 200,
      margin: 2,
      color: {
        dark: "#000000",
        light: "#FFFFFF",
      },
    },
    (error) => {
      if (error) {
        console.error("Error generating QR code:", error);
      }
    }
  );
}

// Watch for modal opening to generate QR code
watch(showQRCodeModal, async (isOpen) => {
  if (isOpen && localShareCode.value?.enabled) {
    await nextTick();
    generateQRCode();
  }
});

onMounted(() => {
  getLatestShareCode();
});
</script>
