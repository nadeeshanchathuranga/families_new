<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <!-- Modal Overlay -->
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-black/50" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel
            class="bg-white rounded-[20px] shadow-xl max-w-md w-full p-6 text-center border border-gray-200"
          >
            <h3 class="text-lg font-semibold text-gray-900">Delete Expense</h3>

            <p class="mt-3 text-[15px] text-gray-700">
              Are you sure you want to delete
              <span class="font-medium">
                {{ selectedExpense?.title ?? 'this expense' }}
              </span>
              ? This action cannot be undone.
            </p>

            <p v-if="errorMsg" class="mt-4 text-sm text-red-600">
              {{ errorMsg }}
            </p>

            <!-- Modal Buttons -->
            <div class="mt-6 space-x-4">
              <button
                class="px-6 py-2 text-[15px] text-gray-700 bg-gray-300 rounded hover:bg-gray-400 disabled:opacity-60"
                @click="() => { playClickSound(); $emit('update:open', false); }"
                :disabled="form.processing"
              >
                Cancel
              </button>

              <button
                class="px-6 py-2 text-[15px] text-white bg-red-600 rounded hover:bg-red-700 disabled:opacity-60"
                @click.prevent="onDelete"
                :disabled="form.processing || !selectedExpense?.id"
              >
                <span v-if="form.processing">Deleting…</span>
                <span v-else>Delete</span>
              </button>
            </div>
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
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open", "deleted"]);

const props = defineProps({
  open: { type: Boolean, required: true },
  selectedExpense: { type: Object, default: null },
  // Optional: if you use Ziggy and your route name differs, override this
  destroyRouteName: { type: String, default: "expenses.destroy" },
});

const form = useForm({});
const errorMsg = ref("");

const playClickSound = () => {
  try { new Audio("/sounds/click-sound.mp3").play(); } catch {}
};

// Build the URL: prefer route() if available, else fallback to REST
const destroyUrl = computed(() => {
  const id = props.selectedExpense?.id;
  if (!id) return null;
  try {
    return typeof route === "function" ? route(props.destroyRouteName, id) : `/expenses/${id}`;
  } catch {
    return `/expenses/${id}`;
  }
});

const onDelete = () => {
  playClickSound();
  errorMsg.value = "";

  if (!props.selectedExpense?.id || !destroyUrl.value) return;

  form.delete(destroyUrl.value, {
    preserveScroll: true,
    onSuccess: () => {
      emit("update:open", false);
      emit("deleted", props.selectedExpense); // let parent remove row / toast
    },
    onError: (errors) => {
      errorMsg.value =
        errors?.message ||
        "Failed to delete expense. It may be linked to other records.";
    },
  });
};
</script>
