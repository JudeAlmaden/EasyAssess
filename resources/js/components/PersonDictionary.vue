<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from "vue";
import { Save, Plus, MoreVertical, CheckCircle } from "lucide-vue-next";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import PersonTable from "@/components/PersonTable.vue";

const props = withDefaults(defineProps<{
  dictionary: {
    id: number;
    name: string;
    description: string;
    meta_columns: string[];
    persons: { id: number; data: Record<string, string> }[];
  };
  access_level: string;
  saveUrl: string;
  allow_delete: boolean;
  canAddPerson: boolean;
  canImport?: boolean;
}>(), {
  canImport: true
});

const columns = ref<string[]>([...props.dictionary.meta_columns].filter(col => col !== "Id"));
if (!columns.value.includes("Id")) columns.value.unshift("Id");

// ✅ Preserve the original backend ID
const persons = ref(
  (props.dictionary.persons ?? []).map((p) => ({
    id: parseInt(p.data["Id"] ?? p.id),
    data: { ...p.data }
  }))
);

// ✅ Only assign new Id if it's not set already (for new persons only)
function updatePersonIds() {
  persons.value.forEach((person) => {
    if (!person.data["Id"]) {
      person.data["Id"] = person.id.toString();
    }
  });
}
updatePersonIds();

const newPerson = ref<Record<string, string>>({});
columns.value.filter(col => col !== "Id").forEach(col => (newPerson.value[col] = ""));

const hasUnsavedChanges = ref(false);
const showSavedConfirmation = ref(false);
const newColumnName = ref("");
const showMoreOptionsDropdown = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);
const isAddPersonFormCollapsed = ref(true);

function addPerson() {
  if (!props.canAddPerson) return;
  if (!newPerson.value.Name?.trim()) return alert("Please fill in the 'Name' field for the new person.");

  const nextId = Math.max(0, ...persons.value.map(p => p.id)) + 1;

  persons.value.push({
    id: nextId,
    data: { ...newPerson.value, Id: nextId.toString() }
  });

  columns.value.forEach(col => {
    if (col !== "Id") newPerson.value[col] = "";
  });

  hasUnsavedChanges.value = true;
}

function handleAddColumn() {
  const trimmed = newColumnName.value.trim();
  if (
    !trimmed ||
    columns.value.includes(trimmed) ||
    ["id", "name"].includes(trimmed.toLowerCase()) ||
    columns.value.filter(col => col !== "Id" && col !== "Name").length >= 5
  ) {
    return alert("Invalid column name or limit reached.");
  }
  columns.value.push(trimmed);
  persons.value.forEach(p => (p.data[trimmed] = ""));
  newPerson.value[trimmed] = "";
  newColumnName.value = "";
  hasUnsavedChanges.value = true;
  showMoreOptionsDropdown.value = false;
}

function handleUpdateColumnName(oldName: string, newName: string) {
  const idx = columns.value.indexOf(oldName);
  if (idx === -1) return;
  columns.value[idx] = newName;
  persons.value.forEach(p => {
    p.data[newName] = p.data[oldName];
    delete p.data[oldName];
  });
  newPerson.value[newName] = newPerson.value[oldName];
  delete newPerson.value[oldName];
  hasUnsavedChanges.value = true;
}

function handleUpdatePersonData(id: number, col: string, val: string) {
  const p = persons.value.find(p => p.id === id);
  if (p) p.data[col] = val;
  hasUnsavedChanges.value = true;
}

function handleDeleteColumn(name: string) {
  if (["Id", "Name"].includes(name)) return alert("Cannot delete reserved column.");
  columns.value = columns.value.filter(c => c !== name);
  persons.value.forEach(p => delete p.data[name]);
  delete newPerson.value[name];
  hasUnsavedChanges.value = true;
  showMoreOptionsDropdown.value = false;
}

function handleDeletePerson(id: number) {
  persons.value = persons.value.filter(p => p.id !== id);
  hasUnsavedChanges.value = true;
}

async function saveDictionary() {
  try {
    const token = getCsrfToken();
    const res = await fetch(props.saveUrl, {
      method: "POST",
      headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": token },
      body: JSON.stringify({ columns: columns.value, persons: persons.value, id: props.dictionary.id })
    });
    if (!res.ok) throw new Error("Failed to save.");
    hasUnsavedChanges.value = false;
    showSavedConfirmation.value = true;
    setTimeout(() => (showSavedConfirmation.value = false), 3000);
    showMoreOptionsDropdown.value = false;

    const data = await res.json();
    console.log("Save successful:", data);
  } catch (e) {
    console.error(e);
    alert("An error occurred while saving.");
  }
}

function getCsrfToken(): string {
  const tag = document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement;
  if (!tag?.content) throw new Error("Missing CSRF token");
  return tag.content;
}

function handleClickOutside(event: MouseEvent) {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    showMoreOptionsDropdown.value = false;
  }
}

function handleBeforeUnload(e: BeforeUnloadEvent) {
  if (hasUnsavedChanges.value) {
    e.preventDefault();
    e.returnValue = '';
  }
}

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  window.addEventListener("beforeunload", handleBeforeUnload);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener("beforeunload", handleBeforeUnload);
});

watch(persons, () => (hasUnsavedChanges.value = true), { deep: true });
watch(columns, () => (hasUnsavedChanges.value = true), { deep: true });



//Imprt and export

function handleExportCSV() {
  const headers = columns.value;
  const rows = persons.value.map(p => headers.map(h => p.data[h] ?? ""));

  let csv = headers.join(",") + "\n";
  csv += rows.map(r => r.map(cell => `"${cell}"`).join(",")).join("\n");

  const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
  const link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.download = `${props.dictionary.name || "dictionary"}.csv`;
  link.click();
}

function handleImportCSVInternal(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = (e) => {
    const text = e.target?.result as string;
    parseImportedCSV(text);
  };
  reader.readAsText(file);
}

function parseImportedCSV(csv: string) {
  const lines = csv.trim().split("\n");
  if (lines.length < 2) {
    alert("CSV must contain at least a header and one row.");
    return;
  }

  const headers = lines[0].split(",").map(h => h.replace(/(^"|"$)/g, "").trim());

  // ✅ Ensure 'Id' and 'Name' columns are present
  if (!headers.includes("Id") || !headers.includes("Name")) {
    alert("CSV must include both 'Id' and 'Name' columns.");
    return;
  }

  const dataRows = lines.slice(1);
  columns.value = [...headers];
  persons.value = dataRows.map((line, index) => {
    const values = line.split(",").map(v => v.replace(/(^"|"$)/g, "").trim());
    const data: Record<string, string> = {};
    headers.forEach((h, i) => {
      data[h] = values[i] ?? "";
    });
    return {
      id: parseInt(data["Id"]) || index + 1,
      data
    };
  });
}

const showTutorialModal = ref(false);
</script>

<template>
  <div class="p-6 md:p-8 lg:p-10 space-y-8">
    <Card class="rounded-xl bg-card shadow-sm  border border-gray-100 transition-all hover:shadow-md overflow-visible">
      <CardHeader>
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
          <div class="flex-1 min-w-0">
            <h2 class="text-3xl font-extrabold leading-tight truncate">
              {{ props.dictionary.name }}
            </h2>
            <p class="mt-1 text-base text-muted-foreground line-clamp-2">
              {{ props.dictionary.description }}
            </p>
          </div>

          <div class="flex items-center space-x-3">
            <button v-if="props.canAddPerson" @click="isAddPersonFormCollapsed = !isAddPersonFormCollapsed"
              class="flex items-center justify-center p-2.5 rounded-full bg-blue-600 text-white shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
              :aria-expanded="isAddPersonFormCollapsed ? 'true' : 'false'" aria-controls="add-person-form"
              aria-label="Add new person" title="Add new person">
              <Plus class="w-5 h-5" />
              <span class="sr-only">Add New Person</span>
            </button>

            <div class="relative overflow-visible w-auto" ref="dropdownRef">
              <button @click="showMoreOptionsDropdown = !showMoreOptionsDropdown"
                class="flex items-center justify-center p-2.5 rounded-full bg-gray-200 text-gray-700 shadow-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition-all duration-200"
                :aria-expanded="showMoreOptionsDropdown ? 'true' : 'false'" aria-haspopup="true"
                aria-label="More options" title="More options">
                <MoreVertical class="w-5 h-5" />
                <span class="sr-only">More Options</span>
              </button>

              <Transition enter-active-class="transition duration-100 ease-out"
                enter-from-class="transform scale-95 opacity-0" enter-to-class="transform scale-100 opacity-100"
                leave-active-class="transition duration-75 ease-in" leave-from-class="transform scale-100 opacity-100"
                leave-to-class="transform scale-95 opacity-0">
                <div v-if="showMoreOptionsDropdown"
                  class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl ring-1 ring-black/5 z-10 overflow-visible transform origin-top-right"
                  role="menu" aria-orientation="vertical" aria-labelledby="menu-button" tabindex="-1">
                  <div class="p-4 border-b border-gray-100">
                    <label for="new-column-input" class="block text-xs font-semibold text-gray-600 mb-2">Add New
                      Column</label>
                    <div class="flex gap-1.5 items-stretch"> <!-- Modified container -->
                      <input id="new-column-input" type="text" v-model="newColumnName" @keyup.enter="handleAddColumn"
                        class="flex-1 min-w-0 text-sm px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 placeholder-gray-400"
                        placeholder="e.g., Student ID" aria-label="New column name" />
                      <button @click="handleAddColumn"
                        :disabled="columns.filter(col => col !== 'Id' && col !== 'Name').length >= 5 || !newColumnName.trim()"
                        class="w-10 flex items-center justify-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors"
                        aria-label="Add column" title="Add column">
                        <Plus class="w-4 h-4" />
                      </button>
                    </div>
                  </div>


                  <div class="py-1" role="none">
                    <div class="py-1" role="none">
                      <!-- Save Changes Button -->
                      <button @click="saveDictionary"
                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                        role="menuitem" tabindex="-1">
                        <Save class="w-4 h-4 mr-3 text-gray-500" />
                        Save Changes
                      </button>

                      <!-- Export to CSV Button -->
                      <button @click="handleExportCSV"
                        class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150"
                        role="menuitem" tabindex="-1">
                        <Download class="w-4 h-4 mr-3 text-gray-500" />
                        Export to CSV
                      </button>

                      <!-- Import from CSV Button with (?) Tooltip -->
                      <div v-if="props.canImport" class="relative group">
                        <label
                          class="flex items-center w-full px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors duration-150 cursor-pointer"
                          title="CSV must include 'Id' and 'Name' columns">
                          <Upload class="w-4 h-4 mr-3 text-gray-500" />
                          Import from CSV

                          <!-- File Input (Hidden) -->
                          <input type="file" accept=".csv" class="hidden" @change="handleImportCSVInternal" />
                        </label>

                        <!-- Tooltip Icon (Separate to avoid layout issue) -->
                        <span
                          class="absolute top-1/2 right-3 -translate-y-1/2 text-gray-400 text-xs group-hover:text-gray-700 hover:underline cursor-pointer"
                          @click.stop="showTutorialModal = true" title="Click to view CSV format guide">
                          ?
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </Transition>
            </div>
          </div>
        </div>
      </CardHeader>

      <div class="px-6 ">
        <Transition enter-active-class="transition duration-150 ease-out"
          enter-from-class="transform opacity-0 -translate-y-2" enter-to-class="transform opacity-100 translate-y-0"
          leave-active-class="transition duration-100 ease-in" leave-from-class="transform opacity-100 translate-y-0"
          leave-to-class="transform opacity-0 -translate-y-2">
          <div v-show="!isAddPersonFormCollapsed" class="mt-4">
            <div class="space-y-4">
              <div v-if="columns.length > 1" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="col in columns.filter((c) => c !== 'Id')" :key="col">
                  <label class="block text-sm font-medium text-gray-700 mb-1">
                    {{ col }}
                    <span v-if="col === 'Name'" class="text-red-500">*</span>
                  </label>
                  <input v-model="newPerson[col]" type="text"
                    class="block w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                    :placeholder="`Enter ${col.toLowerCase()}`" :required="col === 'Name'" />
                </div>
              </div>

              <div v-else class="p-4 bg-blue-50/50 rounded-lg border border-blue-100 text-sm text-blue-800">
                <p>Please add columns before adding persons.</p>
              </div>

              <div class="flex justify-end pt-2">
                <button @click="addPerson" :disabled="columns.length <= 1 || !newPerson.Name"
                  class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm hover:opacity-90 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed">
                  <Plus class="w-4 h-4 mr-2" />
                  Add Person
                </button>
              </div>
            </div>
          </div>
        </Transition>
      </div>
    </Card>

    <Card class="rounded-lg shadow-lg overflow-hidden">
      <CardContent>
        <PersonTable :columns="columns" :persons="persons" :allow_delete="props.allow_delete"
          @update:columnName="handleUpdateColumnName" @update:personData="handleUpdatePersonData"
          @delete:column="handleDeleteColumn" @delete:person="handleDeletePerson" />
      </CardContent>
    </Card>
  </div>

  <!-- Import Tutorial Modal -->
  <div v-if="showTutorialModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="max-w-xl w-full rounded-lg shadow-lg p-6 relative">
      <button @click="showTutorialModal = false" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700">
        ✕
      </button>
      <h2 class="text-lg font-semibold mb-4">Import Tutorial</h2>
      <img src="/images/import-tutorial.png" alt="Import Tutorial" class="w-full rounded border" />
    </div>
  </div>

  <Transition enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform -translate-x-full opacity-0" enter-to-class="transform translate-x-0 opacity-100"
    leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-x-0 opacity-100"
    leave-to-class="transform -translate-x-full opacity-0">
    <div v-if="hasUnsavedChanges"
      class="fixed bottom-4 left-4 p-4 bg-yellow-500 text-white rounded-lg shadow-lg flex items-center space-x-3 z-50">
      <p class="text-sm font-medium">You have unsaved changes!</p>
      <button @click="saveDictionary"
        class="px-3 py-1  text-yellow-700 rounded-md text-sm font-semibold hover:bg-gray-100 transition-colors">
        Save Now
      </button>
    </div>
  </Transition>

  <Transition enter-active-class="transition duration-300 ease-out"
    enter-from-class="transform -translate-x-full opacity-0" enter-to-class="transform translate-x-0 opacity-100"
    leave-active-class="transition duration-200 ease-in" leave-from-class="transform translate-x-0 opacity-100"
    leave-to-class="transform -translate-x-full opacity-0">
    <div v-if="showSavedConfirmation"
      class="fixed bottom-4 left-4 p-4 bg-green-500 text-white rounded-lg shadow-lg flex items-center space-x-3 z-50">
      <CheckCircle class="w-5 h-5 flex-shrink-0" />
      <p class="text-sm font-medium">Changes Saved!</p>
    </div>
  </Transition>
</template>

<style scoped>
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease-out;
  overflow: hidden;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  opacity: 0;
  max-height: 0;
  padding-top: 0;
  padding-bottom: 0;
}

.slide-fade-enter-to,
.slide-fade-leave-from {
  opacity: 1;
  max-height: 500px;
}
</style>
