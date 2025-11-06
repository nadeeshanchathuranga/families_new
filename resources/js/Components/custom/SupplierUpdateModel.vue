<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="$emit('update:open', false)">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 lg:p-8 overflow-y-auto">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="w-full max-w-2xl transform overflow-hidden rounded-2xl bg-white p-8 text-left align-middle shadow-xl transition-all dark:bg-gray-900">
            <DialogTitle class="text-2xl font-bold text-gray-900   text-center">
              Edit Supplier
            </DialogTitle>

            <form @submit.prevent="submit" enctype="multipart/form-data" class="mt-6 space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Supplier Name</label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700     shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                  <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700     shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                  <p v-if="form.errors.email" class="text-sm text-red-600 mt-1">{{ form.errors.email }}</p>
                </div>

                <!-- Address -->
                <div class="md:col-span-2">
                  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Address</label>
                  <input
                    v-model="form.address"
                    type="text"
                    required
                    class="mt-1 w-full rounded-md border-gray-300 dark:border-gray-700     shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                  <p v-if="form.errors.address" class="text-sm text-red-600 mt-1">{{ form.errors.address }}</p>
                </div>

                <!-- Phone Contacts -->
<div class="md:col-span-2">
  <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Phone Contacts</label>
  <div
    v-for="(contact, index) in form.phone_numbers"
    :key="index"
    class="grid grid-cols-12 gap-2 items-center mt-2"
  >
    <!-- Contact Name -->
    <input
      v-model="form.phone_numbers[index].name"
      type="text"
      placeholder="Contact Name"
      required
      class="col-span-4 rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    />

    <!-- Contact Phone -->
    <input
      v-model="form.phone_numbers[index].phone"
      type="tel"
      inputmode="numeric"
      maxlength="10"
      minlength="10"
      pattern="[0-9]*"
      @input="form.phone_numbers[index].phone = form.phone_numbers[index].phone.replace(/\D/g, '')"
      placeholder="Phone Number"
      required
      class="col-span-6 rounded-md border-gray-300 dark:border-gray-700 shadow-sm focus:border-blue-500 focus:ring-blue-500"
    />

    <!-- Remove Button -->
    <button
      v-if="form.phone_numbers.length > 1"
      type="button"
      @click="removePhoneNumber(index)"
      class="col-span-2 text-sm text-white bg-red-600 hover:bg-red-700 px-3 py-1 rounded-md"
    >
      Remove
    </button>

    <!-- Optional Phone Validation -->
    <p
      v-if="form.phone_numbers[index].phone && form.phone_numbers[index].phone.length !== 10"
      class="col-span-12 text-red-600 text-xs"
    >
      Phone number must be exactly 10 digits.
    </p>
  </div>

  <button
    type="button"
    @click="addPhoneNumber"
    class="mt-3 inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-md"
  >
    + Add Contact
  </button>
</div>


                <!-- Image Upload -->
                <div class="md:col-span-2">
                  <label for="image" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Image</label>
                  <div class="mt-2 flex items-center space-x-4">
                    <img
                      v-if="selectedSupplier.image"
                      :src="`/${selectedSupplier.image}`"
                      alt="Supplier"
                      class="h-20 w-20 rounded-md object-cover border"
                    />
                    <p v-else class="text-sm text-gray-500 dark:text-gray-400">No image available</p>
                  </div>
                  <input
                    type="file"
                    id="image"
                    @change="handleImageUpload"
                    class="mt-2 block w-full text-sm text-gray-900   file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700"
                  />
                  <p v-if="form.errors.image" class="text-sm text-red-600 mt-1">{{ form.errors.image }}</p>
                </div>
              </div>

              <!-- Action Buttons -->
              <div class="flex justify-end space-x-4 pt-6 border-t dark:border-gray-700">
                <button
                  type="button"
                  @click="() => { playClickSound(); emit('update:open', false); }"
                  class="px-4 py-2 rounded-md text-white   bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  @click="playClickSound"
                  class="px-4 py-2 rounded-md bg-blue-600 text-white hover:bg-blue-700"
                >
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
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const { open, selectedSupplier } = defineProps({
  open: Boolean,
  selectedSupplier: Object,
});

const form = useForm({
  name: "",
  email: "",
  address: "",
  image: null,
  phone_numbers: [
    { name: "", phone: "" },
  ],
});

const playClickSound = () => {
  new Audio("/sounds/click-sound.mp3").play();
};

const handleImageUpload = (event) => {
  form.image = event.target.files[0];
};

const addPhoneNumber = () => {
  form.phone_numbers.push({ name: "", phone: "" });
};

const removePhoneNumber = (index) => {
  if (form.phone_numbers.length > 1) {
    form.phone_numbers.splice(index, 1);
  }
};

watch(
  () => selectedSupplier,
  (newVal) => {
    if (newVal) {
      form.name = newVal.name || "";
      form.email = newVal.email || "";
      form.address = newVal.address || "";
      form.image = null;
      form.phone_numbers = newVal.numbers?.map((n) => ({
        name: n.name || "",
        phone: n.number || "",
      })) || [{ name: "", phone: "" }];
    } else {
      form.reset();
      form.phone_numbers = [{ name: "", phone: "" }];
    }
  },
  { immediate: true }
);


const submit = () => {
  if (selectedSupplier?.id) {
    form.post(`/suppliers/${selectedSupplier.id}`, {
      onSuccess: () => {
        form.reset();
        form.phone_numbers = [""];
        emit("update:open", false);
      },
    });
  }
};
</script>

<style scoped>
/* Optional: Add custom animations or transitions if needed */
</style>
