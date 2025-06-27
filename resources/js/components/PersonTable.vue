<script setup lang="ts">
import { ref, computed, nextTick } from "vue";
import { 
  Trash2, 
  FolderSearch, 
  Info 
} from "lucide-vue-next";

const emit = defineEmits<{
  (e: "update:columnName", oldName: string, newName: string): void;
  (e: "update:personData", personId: number, column: string, newValue: string): void;
  (e: "delete:column", columnName: string): void;
  (e: "delete:person", personId: number): void;
}>();

const props = defineProps<{
  columns: string[];
  persons: { data: Record<string, string> }[]; // each person has a 'data' object
  allow_delete: boolean;
}>();

const columns = computed(() => props.columns);
const persons = computed(() => props.persons);

// Search feature
const searchQuery = ref("");
const filteredPersons = computed(() => {
  if (!searchQuery.value.trim()) return persons.value;
  return persons.value.filter(p =>
    (p.data["Name"] || "")
      .toLowerCase()
      .includes(searchQuery.value.trim().toLowerCase())
  );
});

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
  <div class="space-y-6">
    <!-- Header and Search -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <h3 class="text-xl font-semibold text-primary">Current Entries</h3>
      <div class="relative w-full md:w-64">
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Search by name..."
          class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg shadow-sm text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all"
        />
        <MagnifyingGlass class="w-4 h-4 absolute left-3 top-3 text-gray-400" />
      </div>
    </div>

    <!-- Table Container -->
    <div class="bg-muted-foreground rounded-xl border border-muted shadow-xs overflow-hidden">
      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y 0 text-foreground divide-muted">
          <thead class="bg-primary-foreground text-primary">
            <tr>
              <th
                v-for="col in columns"
                :key="col"
                class="px-6 py-3 text-left text-xs font-medium  uppercase tracking-wider"
              >
              <div class="flex items-center gap-1">
                <!-- Text or Input -->
                <div class="flex items-center">
                  <span
                    v-if="editingColumn !== col"
                    class="truncate cursor-pointer transition-colors"
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
                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>

                <!-- Trash Icon -->
                <button
                  v-if="col !== 'Id' && col !== 'Name'"
                  @click.stop="deleteColumn(col)"
                  class="p-1 text-gray-400 hover:text-red-500 transition-colors"
                  title="Delete column"
                >
                  <Trash2 class="h-4 w-4" />
                </button>
              </div>

              </th>
              <th class="w-12"></th> <!-- Delete column spacer -->
            </tr>
          </thead>

          <tbody class="divide-y divide-muted text-primary-foreground bg-primary-foreground">
            <tr
              v-for="person in filteredPersons"
              :key="person.data['Id']"
              class="hover:bg-gray-50/50 transition-colors"
            >
              <td
                v-for="col in columns"
                :key="col"
                class="px-6 py-4 text-sm"
                :class="{
                  'text-foreground': getDisplayValue(person.data[col]) !== '---N/A---',
                  'text-primary': getDisplayValue(person.data[col]) === '---N/A---'
                }"
              >
                <div class="relative">
                  <span
                    v-if="!(editingCell && editingCell.personId === Number(person.data['Id']) && editingCell.column === col)"
                    class="block truncate cursor-pointer hover:text-blue-600 transition-colors"
                    @click="startCellEdit(Number(person.data['Id']), col, person.data[col])"
                  >
                    {{ getDisplayValue(person.data[col]) }}
                  </span>
                  <input
                    v-else
                    :id="`cell-edit-${person.data['Id']}-${col}`"
                    v-model="newCellValue"
                    @blur="saveCellData(Number(person.data['Id']), col)"
                    @keyup.enter="saveCellData(Number(person.data['Id']), col)"
                    @keyup.escape="cancelCellEdit"
                    class="block w-full px-3 py-1.5 text-sm border border-blue-300 rounded-md shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                  />
                </div>
              </td>
              <td class="px-2 py-4 text-right">
                <button
                  v-if="props.allow_delete"
                  @click="deletePerson(Number(person.data['Id']))"
                  class="p-1.5 text-gray-400 hover:text-red-500 rounded-full hover:bg-red-50 transition-colors"
                  title="Delete person"
                >
                  <Trash2 class="h-5 w-5" />
                </button>
              </td>
            </tr>

            <tr v-if="filteredPersons.length === 0">
              <td
                :colspan="columns.length + 1"
                class="px-6 py-8 text-center text-sm text-gray-500"
              >
                <div class="flex flex-col items-center justify-center space-y-2">
                  <FolderSearch class="w-8 h-8 text-gray-300" />
                  <p>No matching persons found</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Empty State -->
    <div
      v-if="columns.length === 0"
      class="p-6 bg-blue-50/50 rounded-xl border border-blue-100 text-sm text-blue-800 flex items-start gap-3"
    >
      <Info class="w-5 h-5 mt-0.5 flex-shrink-0 text-blue-500" />
      <p>No columns defined yet. Add columns to start managing data.</p>
    </div>
  </div>
</template>