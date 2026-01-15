<script setup>
import { defineEmits, defineProps } from 'vue'

const props = defineProps({
  modelValue: { type: Boolean, required: true },
  title: { type: String, default: 'Modal Title' },
  showFooter: { type: Boolean, default: true },
  confirmDisabled: { type: Boolean, default: false }, 
});


const emit = defineEmits(['update:modelValue', 'confirm', 'cancel'])

function closeModal() {
  emit('update:modelValue', false)
  emit('cancel')
}

function confirmModal() {
  emit('confirm')
  emit('update:modelValue', false)
}
</script>

<template>
  <transition name="modal-fade">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="closeModal"
    >
      <!-- Backdrop -->
      <div
        class="absolute inset-0 bg-black bg-opacity-50 dark:bg-opacity-70 transition-opacity duration-300"
        :class="{ 'opacity-0': !modelValue, 'opacity-100': modelValue }"
      ></div>

      <!-- Modal -->
      <div
        class="relative z-10 w-full max-w-md transform transition-all duration-300 ease-out"
        :class="{
          'scale-95 opacity-0': !modelValue,
          'scale-100 opacity-100': modelValue
        }"
      >
        <div
          class="bg-white dark:bg-gray-900 rounded-xl shadow-2xl overflow-hidden border border-gray-200 dark:border-gray-600"
        >
          <!-- Header -->
          <div
            class="px-6 py-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-900"
          >
            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-100">
              {{ title }}
            </h2>
          </div>

          <!-- Body slot -->
          <div class="p-6 space-y-4">
            <slot/>
          </div>

          <!-- Footer -->
          <div v-if="showFooter"
            class="px-6 py-4 bg-gray-50 dark:bg-gray-900 border-t border-gray-100 dark:border-gray-700 flex justify-end space-x-3"
          >
            <button
              @click="closeModal"
              class="px-4 py-2 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors font-medium"
            >
              Cancel
            </button>
          <button
            @click="confirmModal"
            :disabled="confirmDisabled"
            class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors shadow-sm focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Confirm
          </button>
          </div>
        </div>
      </div>
    </div>
  </transition>
</template>

<style>
/* Modal transition animations */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

/* Focus styles for better accessibility */
button:focus,
input:focus,
textarea:focus {
    outline: none;
    ring: 2px;
}
</style>