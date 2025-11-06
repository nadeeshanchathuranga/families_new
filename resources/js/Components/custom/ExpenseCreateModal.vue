<template>
  <TransitionRoot as="template" :show="open">
    <Dialog
      class="relative z-10"
      @close="$emit('update:open', false)"
      :initialFocus="titleInput"
    >
      <!-- Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <!-- Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center">
            <!-- Close -->
            <button
              @click="$emit('update:open', false)"
              class="absolute text-2xl text-white top-4 right-4 hover:text-gray-300"
              aria-label="Close"
            >
              &times;
            </button>

            <!-- Title -->
            <DialogTitle class="text-xl font-bold text-white">
              Add Expense
            </DialogTitle>

            <form
              ref="formEl"
              class="mt-6 text-left"
              @submit.prevent="submit"
              @keydown="handleFormKeydown"
            >
              <div class="space-y-4">
                <!-- Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="title">
                    Title:
                  </label>
                  <input
                    ref="titleInput"
                    v-model="form.title"
                    type="text"
                    id="title"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.title" class="mt-1 text-red-500 block">
                    {{ form.errors.title }}
                  </span>
                </div>

                <!-- Amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="amount">
                    Amount:
                  </label>
                  <input
                    v-model="form.amount"
                    type="number"
                    step="0.01"
                    min="0"
                    id="amount"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.amount" class="mt-1 text-red-500 block">
                    {{ form.errors.amount }}
                  </span>
                </div>

                <!-- Expense Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="expense_date">
                    Expense Date:
                  </label>
                  <input
                    v-model="form.expense_date"
                    type="date"
                    id="expense_date"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.expense_date" class="mt-1 text-red-500 block">
                    {{ form.errors.expense_date }}
                  </span>
                </div>

                <!-- Note -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="note">
                    Note:
                  </label>
                  <textarea
                    v-model="form.note"
                    id="note"
                    rows="3"
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                    placeholder="Optional"
                  ></textarea>
                  <span v-if="form.errors.note" class="mt-1 text-red-500 block">
                    {{ form.errors.note }}
                  </span>
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-6 space-x-4">
                <button
                  ref="saveBtn"
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                >
                  Save
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  type="button"
                  @click="$emit('update:open', false)"
                >
                  Cancel
                </button>
              </div>
            </form>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { useForm } from "@inertiajs/vue3";
import { ref, watch, nextTick } from "vue";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: { type: Boolean, required: true },
  expenses: { type: Array, required: true },
});

const form = useForm({
  title: "",
  amount: "",
  expense_date: "",
  note: "",
});

const formEl = ref(null);
const titleInput = ref(null);
const saveBtn = ref(null);

// Focus Title first whenever the modal opens
watch(
  () => props.open,
  async (isOpen) => {
    if (isOpen) {
      await nextTick();
      titleInput.value?.focus();
      // select existing text if any
      if (titleInput.value?.select) titleInput.value.select();
    }
  }
);

// Submit helper used by Enter/Ctrl+Enter and the Save button
const submit = () => {
  form.post("/expenses", {
    onSuccess: () => {
      form.reset();
      emit("update:open", false);
    },
  });
};

// Arrow Up/Down navigation + Enter handling
const handleFormKeydown = (e) => {
  const target = e.target;
  const tag = target.tagName?.toLowerCase();

  // Build focusable list within the form (visible & enabled)
  const focusable = Array.from(
    formEl.value?.querySelectorAll(
      'input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
    ) || []
  ).filter((el) => el.offsetParent !== null);

  const idx = focusable.indexOf(target);
  if (idx === -1) return;

  // Arrow Down → next field
  if (e.key === "ArrowDown") {
    e.preventDefault();
    const next = focusable[Math.min(idx + 1, focusable.length - 1)];
    next?.focus();
    if (next?.select) next.select();
    return;
  }

  // Arrow Up → previous field
  if (e.key === "ArrowUp") {
    e.preventDefault();
    const prev = focusable[Math.max(idx - 1, 0)];
    prev?.focus();
    if (prev?.select) prev.select();
    return;
  }

  // Enter behavior:
  // - Inputs/selects: Enter submits
  // - Textarea: Ctrl+Enter submits (plain Enter leaves a newline)
  if (e.key === "Enter") {
    const isTextarea = tag === "textarea";
    if (isTextarea && !e.ctrlKey) return; // allow newline inside textarea
    e.preventDefault();
    submit();
  }
};
</script>
