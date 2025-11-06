<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">
      <!-- Overlay -->
      <TransitionChild as="template"
        enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-gray-500/75" />
      </TransitionChild>

      <!-- Panel -->
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild as="template"
          enter="ease-out duration-300" enter-from="opacity-0 scale-95" enter-to="opacity-100 scale-100"
          leave="ease-in duration-200" leave-from="opacity-100 scale-100" leave-to="opacity-0 scale-95">
          <DialogPanel class="w-full max-w-md rounded-[20px] border-4 border-blue-600 bg-black p-6 text-white shadow-xl">
            <DialogTitle class="text-xl font-bold">Create User</DialogTitle>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
              <!-- Name -->
              <div>
                <label class="block text-sm text-gray-300">Name</label>
                <input v-model="form.name" type="text" required
                       class="mt-2 w-full rounded-md px-4 py-2 text-black focus:outline-none focus:ring focus:ring-blue-600" />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-400">{{ form.errors.name }}</p>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm text-gray-300">Email</label>
                <input v-model="form.email" type="email" required
                       class="mt-2 w-full rounded-md px-4 py-2 text-black focus:outline-none focus:ring focus:ring-blue-600" />
                <p v-if="form.errors.email" class="mt-2 text-sm text-red-400">{{ form.errors.email }}</p>
              </div>

              <!-- Role (Admin intentionally omitted) -->
              <div>
                <label class="block text-sm text-gray-300">Role</label>
                <select v-model="form.role_type" required
                        class="mt-2 w-full rounded-md px-4 py-2 text-black focus:outline-none focus:ring focus:ring-blue-600">
                  <option value="Manager">Manager</option>
                  <option value="Cashier">Cashier</option>
                </select>
                <p v-if="form.errors.role_type" class="mt-2 text-sm text-red-400">{{ form.errors.role_type }}</p>
              </div>

              <!-- Password -->
              <div>
                <label class="block text-sm text-gray-300">Password</label>
                <div class="mt-2 relative">
                  <input :type="showPwd ? 'text' : 'password'" v-model="form.password" required minlength="8"
                         class="w-full rounded-md px-4 py-2 pr-14 text-black focus:outline-none focus:ring focus:ring-blue-600" />
                  <button type="button" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-600"
                          @click="showPwd = !showPwd">
                    {{ showPwd ? 'Hide' : 'Show' }}
                  </button>
                </div>
                <p v-if="form.errors.password" class="mt-2 text-sm text-red-400">{{ form.errors.password }}</p>
              </div>

              <!-- Actions -->
              <div class="pt-2 flex items-center justify-end gap-3">
                <button type="button" class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400"
                        @click="$emit('update:open', false)">Cancel</button>
                <button type="submit"
                        class="px-5 py-2 rounded bg-blue-600 font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                        :disabled="form.processing">
                  Save
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
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from "@headlessui/vue";
import { toRefs, ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);
const props = defineProps({ open: { type: Boolean, required: true } });
const { open } = toRefs(props);

const showPwd = ref(false);

const form = useForm({
  name: "",
  email: "",
  role_type: "Cashier", // default
  password: "",
});

// Optional: clear errors when closing
watch(open, (val) => {
  if (!val) form.clearErrors();
});

const submit = () => {
  form.post(route("users.store"), {
    preserveScroll: true,
    onSuccess: () => {
      form.reset();
      emit("update:open", false);
    },
  });
};
</script>
