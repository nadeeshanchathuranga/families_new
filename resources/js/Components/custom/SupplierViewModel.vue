<template :key="modalKey">
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-50" @close="$emit('update:open', false)">
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
        <div class="fixed inset-0 bg-black bg-opacity-60 transition-opacity" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6">
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
            class="bg-[#111827] border border-blue-600 rounded-2xl shadow-2xl max-w-2xl w-full p-8 text-white space-y-8"
          >
            <!-- Title -->
            <DialogTitle class="text-2xl font-semibold text-center text-blue-500">
              Supplier Payment Summary
            </DialogTitle>

            <!-- Supplier Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
              <div>
                <label class="text-white-400">Supplier Name:</label>
                <p class="text-lg font-bold text-white">{{ supplier?.name || 'N/A' }}</p>
              </div>
              <div>
                <label class="text-gray-400">Final Due:</label>
                <p class="text-green-400 text-lg font-bold">
                  LKR {{ (totalCost - parseFloat(supplier?.pay || 0)).toFixed(2) }}
                </p>
              </div>
            </div>

            <!-- Product List -->
            <div>
              <label class="text-gray-400">Products:</label>
             <div class="max-h-64 overflow-y-auto">
  <ul class="list-disc pl-5 mt-2 space-y-1 text-sm text-white">
    <li v-for="product in supplier?.products || []" :key="product.id">
      {{ product.name }} – LKR {{ parseFloat(product.cost_price || 0).toFixed(2) }} × {{ product.total_quantity }}
    </li>
    <li v-if="!supplier?.products?.length" class="italic text-gray-500">
      No products available
    </li>
  </ul>
</div>

              <!-- Total Cost -->
              <div v-if="supplier?.products?.length" class="mt-3 text-right text-base font-semibold text-green-400">
                Total: LKR {{ totalCost.toFixed(2) }}
              </div>
            </div>

            <!-- Payment Form -->
            <form @submit.prevent="submit" class="space-y-6">
              <!-- Pay Input -->
              <div>
                <label for="pay" class="block text-sm text-gray-400 mb-1">Pay:</label>
                <input
  v-model="form.pay"
  type="number"
  min="1"
  step="0.01"
  id="pay"
  required
  placeholder="Enter payment amount"
  class="w-full rounded-lg border border-gray-600 bg-white text-black px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600"
/>
                <p v-if="form.errors.pay" class="text-sm text-red-500 mt-1">
                  {{ form.errors.pay }}
                </p>
              </div>

              <!-- Balance -->
              <div>
                <label class="block text-sm text-gray-400 mb-1">Balance (Due):</label>
                <p :class="parseFloat(currentDue) < 0 ? 'text-red-400' : 'text-yellow-300'" class="text-lg font-semibold">
                  LKR {{ currentDue.toFixed(2) }}
                </p>
              </div>

              <!-- Buttons -->
              <div class="flex justify-between pt-4">
                <button
                  type="submit"
                  :disabled="parseFloat(currentDue) <= -1"
                  class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg text-sm font-medium shadow-md transition disabled:opacity-50 disabled:cursor-not-allowed"
                >
                  Submit Payment
                </button>

                <button
                  type="button"
                  @click="() => { playClickSound(); $emit('update:open', false); }"
                  class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-2 rounded-lg text-sm font-medium transition"
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
import { ref, reactive, computed, watch } from "vue";
import axios from "axios";

// Props and Emit
const emit = defineEmits(["update:open"]);
const props = defineProps({
  supplier: Object,
  open: Boolean,
});

// Modal reset key
const modalKey = ref(Date.now());

// Form state
const form = reactive({
  pay: '',
  errors: {}
});

// Watch modal open to reset
watch(() => props.open, (val) => {
  if (!val) {
    modalKey.value = Date.now();
    form.pay = '';
    form.errors = {};
  }
});

// Total product cost = sum of cost_price * total_quantity
const totalCost = computed(() => {
  if (!props.supplier?.products?.length) return 0;
  return props.supplier.products.reduce((sum, product) => {
    const cost = parseFloat(product.cost_price || 0);
    const qty = parseFloat(product.total_quantity || 0);
    return sum + cost * qty;
  }, 0);
});

// Balance = Total - (Pay + Existing Paid)
const currentDue = computed(() => {
  const paidAlready = parseFloat(props.supplier?.pay || 0);
  const payNow = parseFloat(form.pay || 0);
  return totalCost.value - (paidAlready + payNow);
});

// Submit form
const submit = async () => {
  if (parseFloat(currentDue.value) <= -1) {
    alert("No due balance. Payment not required.");
    return;
  }

  try {
    await axios.post('/supplier-payment', {
      supplier_id: props.supplier.id,
      pay: form.pay,
      total: totalCost.value,
      balance: currentDue.value
    });

    alert("Payment submitted successfully!");
    form.pay = '';
    form.errors = {};
    window.location.reload();
  } catch (error) {
    if (error.response?.data?.errors) {
      form.errors = error.response.data.errors;
    } else {
      alert("Something went wrong!");
    }
  }
};

// Optional sound
const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};
</script>
