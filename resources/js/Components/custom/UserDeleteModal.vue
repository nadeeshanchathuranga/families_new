<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">
      <!-- Overlay -->
      <TransitionChild as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/75" />
      </TransitionChild>

      <!-- Panel -->
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild as="template"
          enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="w-full max-w-md rounded-[20px] border border-gray-600 bg-white p-6 text-center shadow-xl">
            <p class="text-[15px] text-gray-700">
              Are you sure you want to delete this user? This action cannot be undone.
            </p>

            <div class="mt-6 space-x-4">
              <button
                class="px-6 py-2 text-[15px] text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                @click="$emit('update:open', false)"
              >
                Cancel
              </button>

              <!-- ✅ Always enabled -->
              <button
                class="px-6 py-2 text-[15px] text-white bg-red-600 rounded hover:bg-red-700"
                @click.prevent="deleteItem"
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
import { Dialog, DialogPanel, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { toRefs } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);
const props = defineProps({
  open: { type: Boolean, required: true },
  selectedUser: { type: Object, default: null },
});

const { open, selectedUser } = toRefs(props);
const form = useForm({});

const deleteItem = () => {
  if (!selectedUser.value?.id) return;
  form.delete(`/users/${selectedUser.value.id}`, {
    preserveScroll: true,
    onSuccess: () => emit("update:open", false),
    onError: (errors) => console.error("Delete failed:", errors),
  });
};
</script>
