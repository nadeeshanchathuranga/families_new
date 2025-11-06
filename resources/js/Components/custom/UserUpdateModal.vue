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
          <DialogPanel class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl max-w-md w-full p-6">
            <DialogTitle class="text-xl font-bold text-white text-center">
              Edit User
            </DialogTitle>

            <form class="mt-6 space-y-5" @submit.prevent="submit">
              <!-- Name -->
              <div>
                <label class="block text-sm text-gray-300">Name</label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  class="mt-2 w-full rounded-md px-4 py-2 text-black focus:outline-none focus:ring focus:ring-blue-600"
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-400">{{ form.errors.name }}</p>
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm text-gray-300">Email</label>
                <input
                  v-model="form.email"
                  type="email"
                  required
                  class="mt-2 w-full rounded-md px-4 py-2 text-black focus:outline-none focus:ring focus:ring-blue-600"
                />
                <p v-if="form.errors.email" class="mt-2 text-sm text-red-400">{{ form.errors.email }}</p>
              </div>

              <!-- Role -->
              <div>
                <label class="block text-sm text-gray-300">Role</label>

                <!-- Admin: read-only -->
                <div v-if="isAdminUser" class="mt-2">
                  <span class="inline-flex items-center gap-2 rounded-md bg-red-100 px-3 py-1 text-sm font-semibold text-red-700">
                    Admin
                  </span>
                  <p class="mt-1 text-xs text-gray-300">Admin role cannot be changed here.</p>
                </div>

                <!-- Others: choose --><!-- Role -->
<div>
 
  <!-- If Admin user → block editing -->
  <div v-if="isAdminUser" class="mt-2">
    
    <p class="mt-1 text-xs text-gray-300">Admin role cannot be changed here.</p>
  </div>

  <!-- Otherwise → allow Manager / Cashier -->
  <select
    v-else
    v-model="form.role_type"
    required
    class="mt-2 w-full rounded-md px-4 py-2 text-black focus:outline-none focus:ring focus:ring-blue-600"
  >
    <option value="Manager">Manager</option>
    <option value="Cashier">Cashier</option>
  </select>

  <p v-if="form.errors.role_type" class="mt-2 text-sm text-red-400">
    {{ form.errors.role_type }}
  </p>
</div>

               
              </div>

              <!-- Password (optional) -->
              <div>
                <label class="block text-sm text-gray-300">Password (optional)</label>
                <div class="mt-2 relative">
                  <input
                    :type="showPwd ? 'text' : 'password'"
                    v-model="form.password"
                    minlength="8"
                    placeholder="Leave blank to keep current"
                    class="w-full rounded-md px-4 py-2 pr-14 text-black focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <button
                    type="button"
                    class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-300 hover:text-white"
                    @click="showPwd = !showPwd"
                  >
                    {{ showPwd ? 'Hide' : 'Show' }}
                  </button>
                </div>
                <p v-if="form.errors.password" class="mt-2 text-sm text-red-400">{{ form.errors.password }}</p>
              </div>

              <!-- Actions -->
              <div class="pt-2 flex items-center justify-end gap-3">
                <button
                  type="button"
                  class="px-4 py-2 rounded bg-gray-300 text-gray-800 hover:bg-gray-400"
                  @click="$emit('update:open', false)"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  class="px-5 py-2 rounded bg-blue-600 font-semibold text-white hover:bg-blue-700 disabled:opacity-60"
                  :disabled="form.processing || !user"
                >
                  {{ form.processing ? 'Saving...' : 'Save' }}
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
import { toRefs, watch, ref, computed } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);
const isAdminUser = computed(() => user.value?.role_type === "Admin");
const props = defineProps({
  open: { type: Boolean, required: true },
  user: { type: Object, default: null }, // selected user to edit
});

const { open, user } = toRefs(props);

const showPwd = ref(false);

// Base form
const form = useForm({
  name: "",
  email: "",
  role_type: "",
  password: "", // optional
});

// When modal opens or user changes, hydrate the form
watch(
  () => user.value,
  (u) => {
    form.reset();
    if (u) {
      form.name = u.name ?? "";
      form.email = u.email ?? "";
      form.role_type = u.role_type ?? "Cashier";
      form.password = "";
    }
  },
  { immediate: true }
);

 

// Submit
const submit = () => {
  if (!user.value?.id) return;

  // omit empty password from payload
  form.transform((data) => {
    const out = { ...data };
    if (!out.password) delete out.password;
    // If Admin, lock role_type to Admin regardless of UI
    if (isAdminUser.value) out.role_type = "Admin";
    return out;
  });

  // Prefer named route if Ziggy is available; otherwise use `/users/${id}`
  const urlOrRoute = typeof route === "function"
    ? route("users.update", user.value.id)
    : `/users/${user.value.id}`;

  form.put(urlOrRoute, {
    preserveScroll: true,
    onSuccess: () => {
      showPwd.value = false;
      emit("update:open", false);
    },
  });
};
</script>
