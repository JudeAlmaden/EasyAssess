<script setup lang="ts">
import { ref, computed, nextTick } from "vue";
import { Trash2 } from "lucide-vue-next";

const emit = defineEmits<{
  (e: "update:columnName", oldName: string, newName: string): void;
  (e: "update:personData", personId: number, column: string, newValue: string): void;
  (e: "delete:column", columnName: string): void;
  (e: "delete:person", personId: number): void;
}>();

const props = defineProps<{
  columns: string[];
  persons: { data: Record<string, string> }[];
}>();

const columns = ref(props.columns);
const persons = computed(() => props.persons);

const editingColumn = ref<string | null>(null);
const newColumnName = ref<string>("");

interface EditingCell {
  personId: number;
  column: string;
}
const editingCell = ref<EditingCell | null>(null);
const newCellValue = ref<string>("");

const startColumnEdit = (col: string) => {
  if (col === "Id" || col === "Name") return;
  editingColumn.value = col;
  newColumnName.value = col;
  nextTick(() => {
    const input = document.getElementById(`column-edit-${col}`) as HTMLInputElement;
    input?.focus();
  });
};

const saveColumnName = (oldName: string) => {
  if (editingColumn.value && newColumnName.value.trim() !== "" && newColumnName.value !== oldName) {
    emit("update:columnName", oldName, newColumnName.value.trim());
  }
  editingColumn.value = null;
  newColumnName.value = "";
};

const cancelColumnEdit = () => {
  editingColumn.value = null;
  newColumnName.value = "";
};

const startCellEdit = (personId: number, col: string, currentValue: string) => {
  if (col === "Id") return;
  editingCell.value = { personId, column: col };
  newCellValue.value = currentValue;
  nextTick(() => {
    const input = document.getElementById(`cell-edit-${personId}-${col}`) as HTMLInputElement;
    input?.focus();
  });
};

const saveCellData = (personId: number, column: string) => {
  if (editingCell.value && editingCell.value.personId === personId && editingCell.value.column === column) {
    emit("update:personData", personId, column, newCellValue.value);
  }
  editingCell.value = null;
  newCellValue.value = "";
};

const cancelCellEdit = () => {
  editingCell.value = null;
  newCellValue.value = "";
};

const deleteColumn = (columnName: string) => {
  emit("delete:column", columnName);
};

const deletePerson = (personId: number) => {
  emit("delete:person", personId);
};

const getDisplayValue = (value: string | undefined): string => {
  if (value === undefined || value === null || value.trim() === "") {
    return "---N/A---";
  }
  return value;
};
</script>

<template>
  <h3 class="text-lg font-semibold text-gray-700 mb-4">Current Entries</h3>

  <div class="overflow-x-auto rounded-lg border border-gray-200 shadow-sm">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="col in columns"
            :key="col"
            class="group px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
          >
            <div class="relative w-full h-full flex items-center justify-between">
              <span
                v-if="editingColumn !== col"
                class="block cursor-pointer flex-grow"
                @click="startColumnEdit(col)"
              >
                {{ col }}
              </span>
              <input
                v-else
                :id="`column-edit-${col}`"
                v-model="newColumnName"
                @blur="saveColumnName(col)"
                @keyup.enter="saveColumnName(col)"
                @keyup.escape="cancelColumnEdit"
                class="block w-full px-2 py-1 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm flex-grow"
              />

              <button
                v-if="col !== 'Id' && col !== 'Name'"
                @click="deleteColumn(col)"
                class="ml-2 text-red-600 hover:text-red-900 opacity-0 group-hover:opacity-100 transition-opacity duration-150 p-1"
                title="Delete column"
              >
                <Trash2 class="h-4 w-4" />
              </button>
            </div>
          </th>
        </tr>
      </thead>

      <tbody class="bg-white divide-y divide-gray-200">
        <tr
          v-for="person in persons"
          :key="person.data['Id']"
          class="group hover:bg-gray-50 transition-colors duration-150 relative"
        >
          <td
            v-for="(col, colIndex) in columns"
            :key="col"
            class="px-6 py-4 text-sm text-gray-900 whitespace-nowrap overflow-hidden text-ellipsis"
            :class="{ 'pr-12': colIndex === columns.length - 1 }"
            style="max-width: 200px;"
            :title="person.data[col]"
          >
            <div class="relative w-full h-full">
              <span
                v-if="!(editingCell && editingCell.personId === Number(person.data['Id']) && editingCell.column === col)"
                class="block cursor-pointer"
                @click="startCellEdit(Number(person.data['Id']), col, person.data[col])"
              >
                <span v-if="getDisplayValue(person.data[col]) === '---N/A---'" class="italic text-gray-500">
                  N/A
                </span>
                <span v-else>
                  {{ getDisplayValue(person.data[col]) }}
                </span>
              </span>
              <input
                v-else
                :id="`cell-edit-${person.data['Id']}-${col}`"
                v-model="newCellValue"
                @blur="saveCellData(Number(person.data['Id']), col)"
                @keyup.enter="saveCellData(Number(person.data['Id']), col)"
                @keyup.escape="cancelCellEdit"
                class="block w-full px-2 py-1 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
            </div>
          </td>
          <td
            class="absolute right-0 top-0 h-full flex items-center justify-center pr-2"
            style="width: 48px;"
          >
            <button
              @click="deletePerson(Number(person.data['Id']))"
              class="text-red-600 hover:text-red-900 opacity-0 group-hover:opacity-100 transition-opacity duration-150 p-1"
              title="Delete person"
            >
              <Trash2 class="h-5 w-5" />
            </button>
          </td>
        </tr>

        <tr v-if="persons.length === 0">
          <td
            :colspan="columns.length"
            class="px-6 py-4 text-center text-sm text-gray-500 italic"
          >
            No persons added yet.
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <div
    v-if="columns.length === 0"
    class="mt-4 p-4 bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-md text-sm"
  >
    <p>No columns defined yet. This table will be empty.</p>
  </div>
</template>
