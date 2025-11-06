<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 transition-opacity bg-black bg-opacity-50" />
      </TransitionChild>

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
          <DialogPanel class="bg-white border rounded-xl shadow-xl max-w-md w-full p-6 text-center">
            <p class="text-gray-700 text-[15px]">
              Are you sure you want to delete this expense? This action cannot be undone.
            </p>

            <div class="mt-6 space-x-4">
              <button
                class="px-6 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                @click="() => { playClickSound(); emit('update:open', false); }"
              >
                Cancel
              </button>

              <button
                class="px-6 py-2 text-white bg-red-600 rounded hover:bg-red-700"
                @click.prevent="() => { playClickSound(); deleteExpense(); }"
              >
                Delete
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['update:open', 'deleted']);

const { open, expense } = defineProps({
  open: { type: Boolean, required: true },
  expense: { type: Object, default: null }
});

const playClickSound = () => {
  const clickSound = new Audio('/sounds/click-sound.mp3');
  clickSound.play();
};

const form = useForm({});

const deleteExpense = () => {
  if (!expense?.id) return;

  form.delete(`/expenses/${expense.id}`, {
    onSuccess: () => {
      emit('deleted', expense.id);  // update parent table
      emit('update:open', false);   // close modal
    },
    onError: (errors) => {
      console.error('Delete failed:', errors);
    }
  });
};
</script>
