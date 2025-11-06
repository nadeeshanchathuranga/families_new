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
        <div
          class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
        />
      </TransitionChild>

      <!-- Modal Content -->
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
    <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center"
          >
            <!-- Modal Title -->
            <DialogTitle class="text-xl font-bold text-white">
              Add Supplier
            </DialogTitle>



            <form @submit.prevent="submit" enctype="multipart/form-data">
              <!-- Modal Form -->
              <div class="grid grid-cols-2 gap-6 mt-6 text-left">
                <!-- Supplier Name -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="name">Supplier Name:</label>
                  <input
                    v-model="form.name"
                    type="text"
                    id="name"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.name" class="mt-2 text-red-500">
                    {{ form.errors.name }}
                  </span>
                </div>

                <!-- Email -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="email">Email:</label>
                  <input
                    v-model="form.email"
                    type="email"
                    id="email"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.email" class="mt-2 text-red-500">
                    {{ form.errors.email }}
                  </span>
                </div>

                <!-- Address -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="address">Address:</label>
                  <input
                    v-model="form.address"
                    type="text"
                    id="address"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.address" class="mt-2 text-red-500">
                    {{ form.errors.address }}
                  </span>
                </div>

                <!-- Supplier Image -->
                <div>
                  <label class="block text-sm font-medium text-gray-300" for="image">Supplier Image:</label>
                  <input
                    type="file"
                    id="image"
                    @change="handleImageUpload"
                    class="w-full px-4 py-2 mt-2 text-white bg-gray-800 rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.image" class="mt-2 text-red-500">
                    {{ form.errors.image }}
                  </span>
                </div>

                <!-- Phone Numbers (Full width col-span-2) -->
                <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-300 mb-2">
                  Phone Contacts:
                </label>
                <div
                  v-for="(contact, index) in form.phone_numbers"
                  :key="index"
                  class="grid grid-cols-12 gap-2 items-center mb-2"
                >
                  <!-- Name Input -->
                  <input
                    v-model="form.phone_numbers[index].name"
                    type="text"
                    placeholder="Name"
                    required
                    class="col-span-4 px-4 py-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />

                  <!-- Phone Input -->
                  <input
                    v-model="form.phone_numbers[index].phone"
                    type="tel"
                    inputmode="numeric"
                    maxlength="10"
                    minlength="10"
                    placeholder="Phone Number"
                    required
                    @input="validatePhoneNumber(index)"
                    @keypress="blockNonNumbers($event)"
                    class="col-span-4 px-4 py-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />

                  <!-- Remove Button -->
                  <button
                    type="button"
                    @click="removePhoneNumber(index)"
                    v-if="form.phone_numbers.length > 1"
                    class="col-span-2 px-2 py-1 text-white bg-red-600 rounded hover:bg-red-700"
                  >
                    Remove
                  </button>

                  <!-- Phone Validation Error -->
                  <span
                    v-if="form.phone_numbers[index].phone && form.phone_numbers[index].phone.length !== 10"
                    class="col-span-12 text-red-500 text-sm"
                  >
                    Phone number must be exactly 10 digits
                  </span>
                </div>

                <!-- Add Button -->
                <button
                  type="button"
                  @click="addPhoneNumber"
                  class="mt-2 px-3 py-1 text-sm text-white bg-green-600 rounded hover:bg-green-700"
                >
                  + Add Contact
                </button>

                <!-- Validation Error -->
                <span v-if="form.errors.phone_numbers" class="mt-2 text-red-500 block">
                  {{ form.errors.phone_numbers }}
                </span>
              </div>

              </div>

              <!-- Modal Buttons -->
              <div class="mt-6 text-center space-x-4">
                <button
                  @click="playClickSound"
                  type="submit"
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                >
                  Save
                </button>
                <button
                  type="button"
                  @click="() => { playClickSound(); $emit('update:open', false); }"
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
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

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

const emit = defineEmits(["update:open"]);

defineProps({
  open: {
    type: Boolean,
    required: true,
  },
});

const form = useForm({
  name: "",
  email: "",
  address: "",
  image: null,
  phone_numbers: [
    {
      name: "",
      phone: "",
    },
  ],
});


const handleImageUpload = (event) => {
  form.image = event.target.files[0]; // Set the file to the form object
};


const addPhoneNumber = () => {
  form.phone_numbers.push({ name: "", phone: "" });
};



const removePhoneNumber = (index) => {
  if (form.phone_numbers.length > 1) {
    form.phone_numbers.splice(index, 1);
  }
};

const validatePhoneNumber = (index) => {
  // Strip non-digits
  form.phone_numbers[index] = form.phone_numbers[index].replace(/\D/g, '');
};

const blockNonNumbers = (event) => {
  const key = event.key;
  if (!/^\d$/.test(key)) {
    event.preventDefault();
  }
};



const submit = () => {
  form.post("/suppliers", {
    onSuccess: () => {
      form.reset();
      emit("update:open", false);
    },
  });
};
</script>
