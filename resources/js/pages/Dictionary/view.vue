<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from "vue";
import { Save, Plus, MoreVertical, CheckCircle } from "lucide-vue-next";
import AppLayout from "@/layouts/AppLayout.vue";
import { Card, CardContent, CardHeader } from "@/components/ui/card";
import PersonTable from "@/components/PersonTable.vue";
import type { BreadcrumbItem } from "@/types";

const props = defineProps<{
  dictionary: {
    id: number;
    name: string;
    description: string;
    meta_columns: string[];
    persons: { id: number; data: Record<string, string> }[];
  };
  access_level: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: "Person Dictionary", href: "/dictionary" },
  {
    title: props.dictionary?.name ?? "Untitled Dictionary",
    href: `/dictionary/${props.dictionary?.id}`,
  },
];

const columns = ref<string[]>(
  [...props.dictionary.meta_columns].filter((col) => col !== "Id")
);

const persons = ref(
  (props.dictionary.persons ?? []).map((p, i) => ({
    id: i + 1,
    data: { ...p.data }
  }))
);

if (!columns.value.includes("Id")) {
  columns.value.unshift("Id");
}
updatePersonIds();

const newPerson = ref<Record<string, string>>({});
columns.value
  .filter((col) => col !== "Id")
  .forEach((col) => {
    newPerson.value[col] = "";
  });

const hasUnsavedChanges = ref(false);
const showSavedConfirmation = ref(false);

function addPerson() {
  const isNameValid = newPerson.value.Name?.trim() !== "";

  if (!isNameValid) {
    alert("Please fill in the 'Name' field for the new person.");
    return;
  }

  persons.value.push({ id: -1, data: { ...newPerson.value } });
  hasUnsavedChanges.value = true;

  columns.value.forEach((col) => {
    if (col !== "Id") newPerson.value[col] = "";
  });

  updatePersonIds();
}

const newColumnName = ref("");
const showMoreOptionsDropdown = ref(false);
const dropdownRef = ref<HTMLElement | null>(null);

const isAddPersonFormCollapsed = ref(true);

function handleAddColumn() {
  if (columns.value.filter((col) => col !== "Id" && col !== "Name").length >= 5) {
    alert("You can only have up to 5 additional data columns.");
    return;
  }

  const trimmedCol = newColumnName.value.trim();
  if (
    !trimmedCol ||
    columns.value.includes(trimmedCol) ||
    trimmedCol.toLowerCase() === "id" ||
    trimmedCol.toLowerCase() === "name"
  ) {
    alert("Invalid column name or column already exists (Id and Name are reserved).");
    return;
  }

  columns.value.push(trimmedCol);
  persons.value.forEach((p) => (p.data[trimmedCol] = ""));
  newPerson.value[trimmedCol] = "";
  newColumnName.value = "";
  hasUnsavedChanges.value = true;
  showMoreOptionsDropdown.value = false;
}

function updatePersonIds() {
  persons.value.forEach((person, index) => {
    person.data["Id"] = (index + 1).toString();
  });
}

const handleUpdateColumnName = (oldName: string, newName: string) => {
  const index = columns.value.indexOf(oldName);
  if (index !== -1) {
    columns.value[index] = newName;
    hasUnsavedChanges.value = true;

    persons.value.forEach((person) => {
      if (person.data[oldName] !== undefined) {
        person.data[newName] = person.data[oldName];
        delete person.data[oldName];
      }
    });

    if (newPerson.value[oldName] !== undefined) {
      newPerson.value[newName] = newPerson.value[oldName];
      delete newPerson.value[oldName];
    }
  }
};

const handleUpdatePersonData = (
  personId: number,
  column: string,
  newValue: string
) => {
  const person = persons.value.find((p) => p.id === personId);
  if (person) {
    person.data[column] = newValue;
    hasUnsavedChanges.value = true;
  }
};

const handleDeleteColumn = (columnName: string) => {
  if (columnName === "Id" || columnName === "Name") {
    alert("The 'Id' and 'Name' columns cannot be deleted.");
    return;
  }
  const index = columns.value.indexOf(columnName);
  if (index !== -1) {
    columns.value.splice(index, 1);
    persons.value.forEach((person) => delete person.data[columnName]);
    delete newPerson.value[columnName];
    hasUnsavedChanges.value = true;
  }
};

const handleDeletePerson = (personId: number) => {
  const index = persons.value.findIndex((p) => p.id === personId);
  if (index !== -1) {
    persons.value.splice(index, 1);
    updatePersonIds();
    hasUnsavedChanges.value = true;
  }
};

async function saveDictionary() {
  try {
    const csrfToken = getCsrfToken();
    const response = await fetch(`/api/dictionaries/`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "X-CSRF-TOKEN": csrfToken,
      },
      body: JSON.stringify({
        columns: columns.value,
        persons: persons.value,
        id: props.dictionary.id
      }),
    });

    if (!response.ok) throw new Error("Failed to save dictionary.");

    hasUnsavedChanges.value = false;
    showSavedConfirmation.value = true;
    setTimeout(() => {
      showSavedConfirmation.value = false;
    }, 3000);

    showMoreOptionsDropdown.value = false;
  } catch (err) {
    console.error(err);
    alert("An error occurred while saving.");
  }
}

function getCsrfToken(): string {
  const metaTag = document.querySelector(
    'meta[name="csrf-token"]'
  ) as HTMLMetaElement | null;
  if (!metaTag) throw new Error("CSRF token meta tag not found");
  const token = metaTag.content;
  if (!token) throw new Error("CSRF token content is empty");
  return token;
}

const handleClickOutside = (event: MouseEvent) => {
  if (dropdownRef.value && !dropdownRef.value.contains(event.target as Node)) {
    showMoreOptionsDropdown.value = false;
  }
};

const handleBeforeUnload = (event: BeforeUnloadEvent) => {
  if (hasUnsavedChanges.value) {
    event.preventDefault();
    event.returnValue = '';
  }
};

onMounted(() => {
  document.addEventListener("click", handleClickOutside);
  window.addEventListener('beforeunload', handleBeforeUnload);
});

onUnmounted(() => {
  document.removeEventListener("click", handleClickOutside);
  window.removeEventListener('beforeunload', handleBeforeUnload);
});

watch(persons, () => {
  hasUnsavedChanges.value = true;
}, { deep: true });

watch(columns, () => {
  hasUnsavedChanges.value = true;
}, { deep: true });
</script>


<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 md:p-8 lg:p-10 space-y-8">
      <Card class="rounded-xl bg-white shadow-sm  border border-gray-100 transition-all hover:shadow-md overflow-visible">
        <CardHeader>
          <div class="flex items-start justify-between">
            <div class="flex-1 min-w-0">
              <h2 class="text-2xl font-bold text-gray-900 truncate">
                {{ props.dictionary.name }}
              </h2>
              <p class="text-sm text-gray-600 line-clamp-2">
                {{ props.dictionary.description }}
              </p>
            </div>

            <div class="flex items-center space-x-2">
              <button
                @click="isAddPersonFormCollapsed = !isAddPersonFormCollapsed"
                class="p-2 rounded-lg hover:bg-white/50 transition-all duration-200 shadow-xs border border-gray-200 bg-white"
                aria-label="Toggle Add Person Form"
              >
                <Plus class="w-5 h-5 text-blue-600" />
              </button>

              <div class="relative" ref="dropdownRef">
                <button
                  @click="showMoreOptionsDropdown = !showMoreOptionsDropdown"
                  class="p-2 rounded-lg hover:bg-white/50 transition-all duration-200 shadow-xs border border-gray-200 bg-white"
                  aria-label="More options"
                >
                  <MoreVertical class="w-5 h-5 text-gray-600" />
                </button>

                <Transition
                  enter-active-class="transition duration-100 ease-out"
                  enter-from-class="transform scale-95 opacity-0"
                  enter-to-class="transform scale-100 opacity-100"
                  leave-active-class="transition duration-75 ease-in"
                  leave-from-class="transform scale-100 opacity-100"
                  leave-to-class="transform scale-95 opacity-0"
                >
                  <div
                    v-if="showMoreOptionsDropdown"
                    class="absolute right-0 w-56 bg-white rounded-lg shadow-lg ring-1 ring-black/5 z-10 overflow-hidden"
                  >
                    <div class="p-3 border-b border-gray-100">
                      <label class="block text-xs font-medium text-gray-500 mb-1">New Column</label>
                      <div class="flex gap-2">
                        <input
                          type="text"
                          v-model="newColumnName"
                          @keyup.enter="handleAddColumn"
                          class="flex-1 text-sm px-3 py-2 border border-gray-200 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Column name"
                        />
                        <button
                          @click="handleAddColumn"
                          :disabled="columns.filter(col => col !== 'Id' && col !== 'Name').length >= 5 || !newColumnName.trim()"
                          class="px-3 py-2 bg-blue-600 text-white rounded-md disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                          <Plus class="w-4 h-4" />
                        </button>
                      </div>
                    </div>
                    <div class="py-1">
                      <button
                        @click="saveDictionary"
                        class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors"
                      >
                        <Save class="w-4 h-4 mr-3 text-gray-400" />
                        Save Changes
                      </button>
                    </div>
                  </div>
                </Transition>
              </div>
            </div>
          </div>
        </CardHeader>

        <div class="px-6 ">
          <Transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform opacity-0 -translate-y-2"
            enter-to-class="transform opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform opacity-100 translate-y-0"
            leave-to-class="transform opacity-0 -translate-y-2"
          >
            <div v-show="!isAddPersonFormCollapsed" class="mt-4">
              <div class="space-y-4">
                <div v-if="columns.length > 1" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div v-for="col in columns.filter((c) => c !== 'Id')" :key="col">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                      {{ col }}
                      <span v-if="col === 'Name'" class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="newPerson[col]"
                      type="text"
                      class="block w-full px-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-blue-500 focus:border-blue-500 shadow-sm"
                      :placeholder="`Enter ${col.toLowerCase()}`"
                      :required="col === 'Name'"
                    />
                  </div>
                </div>

                <div v-else class="p-4 bg-blue-50/50 rounded-lg border border-blue-100 text-sm text-blue-800">
                  <p>Please add columns before adding persons.</p>
                </div>

                <div class="flex justify-end pt-2">
                  <button
                    @click="addPerson"
                    :disabled="columns.length <= 1 || !newPerson.Name"
                    class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-sm hover:opacity-90 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                  >
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
          <PersonTable
            :columns="columns"
            :persons="persons"
            @update:columnName="handleUpdateColumnName"
            @update:personData="handleUpdatePersonData"
            @delete:column="handleDeleteColumn"
            @delete:person="handleDeletePerson"
          />
        </CardContent>
      </Card>
    </div>

    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-x-full opacity-0"
      enter-to-class="transform translate-x-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-x-0 opacity-100"
      leave-to-class="transform -translate-x-full opacity-0"
    >
      <div
        v-if="hasUnsavedChanges"
        class="fixed bottom-4 left-4 p-4 bg-yellow-500 text-white rounded-lg shadow-lg flex items-center space-x-3 z-50"
      >
        <p class="text-sm font-medium">You have unsaved changes!</p>
        <button
          @click="saveDictionary"
          class="px-3 py-1 bg-white text-yellow-700 rounded-md text-sm font-semibold hover:bg-gray-100 transition-colors"
        >
          Save Now
        </button>
      </div>
    </Transition>

    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform -translate-x-full opacity-0"
      enter-to-class="transform translate-x-0 opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform translate-x-0 opacity-100"
      leave-to-class="transform -translate-x-full opacity-0"
    >
      <div
        v-if="showSavedConfirmation"
        class="fixed bottom-4 left-4 p-4 bg-green-500 text-white rounded-lg shadow-lg flex items-center space-x-3 z-50"
      >
        <CheckCircle class="w-5 h-5 flex-shrink-0" />
        <p class="text-sm font-medium">Changes Saved!</p>
      </div>
    </Transition>

  </AppLayout>
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
```