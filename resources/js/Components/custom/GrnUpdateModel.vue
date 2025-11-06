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
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
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
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl max-w-4xl w-full p-6 text-center"
          >
            <!-- Modal Title -->
            <DialogTitle class="text-xl font-bold text-white">
              Edit Grn
            </DialogTitle>
            <form @submit.prevent="submit">
              <!-- Modal Form -->
              <div class="mt-6 space-y-4 text-left">
                <!-- Grn Number -->
                <div>
                  <label class="block text-sm font-medium text-gray-300">Grn Number:</label>
                  <input
                    v-model="form.grn_number"
                    type="text"
                    id="grn_number"
                    required
                    class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
                  />
                  <span v-if="form.errors.grn_number" class="mt-4 text-red-500">
                    {{ form.errors.grn_number }}
                  </span>
                </div>

                <!-- Items -->
                <div
                  v-for="(item, index) in form.items"
                  :key="index"
                  class="border p-3 mb-4 rounded-md bg-gray-800"
                >
                  <div class="flex gap-4">
                    <div class="w-1/2">
                      <label class="block text-sm font-medium text-gray-300">Product Name:</label>
                      <input v-model="item.name" type="text" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                    </div>
                    <div class="w-1/2">
                      <label class="block text-sm font-medium text-gray-300">Quantity:</label>
                      <input v-model="item.stock_quantity" type="number" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                    </div>
                  </div>

                  <div class="flex gap-4 mt-4">
                    <div class="w-1/2">
                      <label class="block text-sm font-medium text-gray-300">Product Code:</label>
                      <input v-model="item.code" type="text" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                    </div>
                    <div class="w-1/2">
                      <label class="block text-sm font-medium text-gray-300">Cost Price:</label>
                      <input v-model="item.cost_price" type="number" step="0.01" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                    </div>
                  </div>

                  <div class="flex gap-4 mt-4">
                    <div class="w-1/2">
                      <label class="block text-sm font-medium text-gray-300">Barcode:</label>
                      <input v-model="item.barcode" type="text" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                    </div>
                    <div class="w-1/2">
                      <label class="block text-sm font-medium text-gray-300">Batch No:</label>
                      <input v-model="item.batch_no" type="text" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                    </div>
                  </div>

                  <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-300">Expired Date:</label>
                    <input v-model="item.expire_date" type="date" class="w-full px-4 py-2 mt-2 text-black rounded-md" />
                  </div>

                  <button
                    v-if="form.items.length > 1"
                    @click.prevent="form.items.splice(index, 1)"
                    class="mt-2 text-red-500 hover:underline"
                  >
                    Remove Item
                  </button>
                </div>

                <!-- Add another item -->
                <button
                  @click.prevent="addItem"
                  class="mb-4 px-3 py-1 bg-green-600 text-white rounded hover:bg-green-700"
                >
                  + Add Another Item
                </button>
              </div>

              <!-- Modal Buttons -->
              <div class="mt-6 space-x-4">
                <button
                  class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
                  type="submit"
                  :disabled="form.processing"
                  @click="playClickSound"
                >
                  {{ form.processing ? 'Saving...' : 'Save' }}
                </button>
                <button
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
                  type="button"
                  @click="() => { playClickSound(); emit('update:open', false); }"
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
import { ref, watch } from "vue";
import { useForm } from "@inertiajs/vue3";
import axios from "axios";

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  selectedGrn: {
    type: Object,
    default: null,
  },
});

// The main form state
const form = useForm({
  grn_number: "",
  items: [
    {
      name: "",
      stock_quantity: "",
      code: "",
      cost_price: "",
      barcode: "",
      batch_no: "",
      expire_date: "",
    },
  ],
});

// Watch for changes to selectedGrn and fill the form for editing
watch(
  () => props.selectedGrn,
  (newValue) => {
    if (newValue) {
      form.grn_number = newValue.grn_number || "";
      form.items = (newValue.products && Array.isArray(newValue.products) && newValue.products.length)
        ? newValue.products.map(item => ({
            name: item.name || "",
            stock_quantity: item.stock_quantity || "",
            code: item.code || "",
            cost_price: item.cost_price || "",
            barcode: item.barcode || "",
            batch_no: item.batch_no || "",
            expire_date: item.expire_date || "", 
          }))
        : [{
            name: "",
            stock_quantity: "",
            code: "",
            cost_price: "",
            barcode: "",
            batch_no: "",
            expire_date: "",
          }];
    }
  },
  { immediate: true }
);


// Add another item row
const addItem = () => {
  form.items.push({
    name: "",
    stock_quantity: "",
    code: "",
    cost_price: "",
    barcode: "",
    batch_no: "",
    expire_date: "",
  });
};

// Watch for code changes and fetch details
watch(
  () => form.items.map(item => item.code),
  async (newCodes, oldCodes) => {
    newCodes.forEach(async (code, index) => {
      if (code && code !== (oldCodes ? oldCodes[index] : '')) {
        try {
          const response = await axios.get("/grns/next-batch", {
            params: { code },
          });
          if (response.data) {
            if (response.data.name) {
              form.items[index].name = response.data.name;
            }
            if (response.data.next_batch_no) {
              form.items[index].batch_no = response.data.next_batch_no;
            }
          }
        } catch (error) {
          console.error("Error fetching data for code:", code, error);
        }
      }
    });
  },
  { deep: true }
);

// Submit form - Fixed to ensure proper form submission
const submit = () => {
  if (!props.selectedGrn || !props.selectedGrn.id) {
    console.error('No GRN selected for editing');
    return;
  }

  form.put(`/grns/${props.selectedGrn.id}`, {
    onSuccess: () => {
      form.reset();
      emit("update:open", false); // Close the modal
    },
    onError: (errors) => {
      console.error('Form submission errors:', errors);
    }
  });
};
</script>