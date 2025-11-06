<!-- resources/js/Components/custom/ExpenseUpdateModal.vue -->
<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <!-- Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Panel -->
      <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-full max-w-2xl p-6 text-center">
            <DialogTitle class="text-xl font-bold text-white">
              Edit Expense
            </DialogTitle>

            <form @submit.prevent="submit">
              <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-6 text-left">
                <!-- Title -->
                <div>
                  <label class="block text-sm font-medium text-gray-300">Title</label>
                  <input
                    v-model="form.title"
                    type="text"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.title" class="mt-2 block text-red-500 text-sm">
                    {{ form.errors.title }}
                  </span>
                </div>

                <!-- Amount -->
                <div>
                  <label class="block text-sm font-medium text-gray-300">Amount</label>
                  <input
                    v-model.number="form.amount"
                    type="number" step="0.01" min="0"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.amount" class="mt-2 block text-red-500 text-sm">
                    {{ form.errors.amount }}
                  </span>
                </div>

                <!-- Expense Date -->
                <div>
                  <label class="block text-sm font-medium text-gray-300">Expense Date</label>
                  <input
                    v-model="form.expense_date"
                    type="date"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.expense_date" class="mt-2 block text-red-500 text-sm">
                    {{ form.errors.expense_date }}
                  </span>
                </div>

                <!-- Note -->
                <div>
                  <label class="block text-sm font-medium text-gray-300">Note</label>
                  <input
                    v-model="form.note"
                    type="text"
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.note" class="mt-2 block text-red-500 text-sm">
                    {{ form.errors.note }}
                  </span>
                </div>
              </div>

              <div class="mt-6 space-x-4">
                <button
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-60"
                  type="submit"
                  :disabled="form.processing || !selectedExpense?.id"
                  @click="playClickSound"
                >
                  <span v-if="form.processing">Saving…</span>
                  <span v-else>Save</span>
                </button>

                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400 disabled:opacity-60"
                  type="button"
                  :disabled="form.processing"
                  @click="() => { playClickSound(); $emit('update:open', false); }"
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
import { computed, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open", "updated"]);

const props = defineProps({
  open: { type: Boolean, required: true },
  selectedExpense: { type: Object, default: null },
  // Optional Ziggy route name override
  updateRouteName: { type: String, default: "expenses.update" },
});

const playClickSound = () => {
  try { new Audio("/sounds/click-sound.mp3").play(); } catch {}
};

const form = useForm({
  title: "",
  amount: null,
  expense_date: "",
  note: "",
});

// Normalize date to YYYY-MM-DD for input[type=date]
const toInputDate = (val) => {
  if (!val) return "";
  const d = new Date(val);
  if (Number.isNaN(d.getTime())) return "";
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
};

// Prefill when selected changes
watch(
  () => props.selectedExpense,
  (exp) => {
    if (!exp) return;
    form.title = exp.title ?? "";
    form.amount = exp.amount ?? null;
    form.expense_date = toInputDate(exp.expense_date ?? exp.date ?? "");
    form.note = exp.note ?? "";
  },
  { immediate: true }
);

// Build URL with Ziggy if present, else REST fallback
const updateUrl = computed(() => {
  const id = props.selectedExpense?.id;
  if (!id) return null;
  try {
    return typeof route === "function" ? route(props.updateRouteName, id) : `/expenses/${id}`;
  } catch {
    return `/expenses/${id}`;
  }
});

const submit = () => {
  if (!props.selectedExpense?.id || !updateUrl.value) return;

  form.put(updateUrl.value, {
    preserveScroll: true,
    onSuccess: () => {
      emit("updated", { ...props.selectedExpense, ...form.data() });
      emit("update:open", false);
    },
  });
};
</script>
