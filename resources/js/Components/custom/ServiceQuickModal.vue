<template>
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Content -->
      <div class="fixed inset-0 z-50 flex items-center justify-center">
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
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-10 text-center relative"
          >
            <!-- Close -->
            <button
              type="button"
              @click="$emit('update:open', false)"
              class="absolute top-4 right-4 text-2xl text-white hover:text-gray-300"
              aria-label="Close"
            >
              &times;
            </button>

            <!-- Title -->
            <DialogTitle class="text-xl font-bold text-white">
              Add Service
            </DialogTitle>

            <!-- Form -->
           <form
  ref="formRef"
  class="mt-6 text-left"
  @submit.prevent="submit"
  @keyup.enter="trySubmit"
>
  <div class="space-y-4">
    <!-- Service Name -->
    <div>
      <label class="block text-sm font-medium text-gray-300" for="svc_name">
        Service Name:
      </label>
      <textarea
        id="svc_name"
        v-model.trim="service_name"
        rows="3"
        :disabled="submitting"
        class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        placeholder="Describe the service..."
        @keydown.down="focusNext"
        @keydown.up="focusPrev"
      ></textarea>
      <span v-if="touched.name && !service_name" class="mt-1 text-red-500 block">
        Service name is required
      </span>
    </div>

    <!-- Total Cost -->
    <div>
      <label class="block text-sm font-medium text-gray-300" for="svc_total">
        Total Cost (LKR):
      </label>
      <input
        id="svc_total"
        v-model.number="total_cost"
        type="number"
        step="0.01"
        min="0"
        :disabled="submitting"
        class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"
        placeholder="0.00"
        @blur="normalizeTotal"
        @keydown.down="focusNext"
        @keydown.up="focusPrev"
      />
      <span v-if="touched.total && !isValidTotal" class="mt-1 text-red-500 block">
        Total cost must be a positive number
      </span>
    </div>

    <!-- General error -->
    <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
  </div>

  <!-- Actions -->
  <div class="mt-6 space-x-4 text-center">
    <button
      class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700 disabled:opacity-50"
      type="submit"
      :disabled="!formOk || submitting"
      @keydown.up="focusPrev"
    >
      {{ submitting ? 'Saving...' : 'Save' }}
    </button>
    <button
      class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400"
      type="button"
      @click="$emit('update:open', false)"
      :disabled="submitting"
      @keydown.up="focusPrev"
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
import axios from "axios";
import { ref, watch, computed, nextTick } from "vue";

const props = defineProps({
  open: { type: Boolean, required: true },

  // Optional if you want to pass these and use them server-side later
  defaultCustomerId: { type: [Number, String], default: null },
  defaultEmployeeId: { type: [Number, String], default: null },
  orderId: { type: String, default: null },
});

const emit = defineEmits(["update:open", "saved"]);

const service_name = ref("");
const total_cost = ref(null);
const submitting = ref(false);
const error = ref("");
const touched = ref({ name: false, total: false });

// Reset when opened
watch(
  () => props.open,
  async (o) => {
    if (o) {
      service_name.value = "";
      total_cost.value = null;
      error.value = "";
      touched.value = { name: false, total: false };
      await nextTick();
      document.getElementById("svc_name")?.focus();
    }
  }
);

// Validation
const isValidTotal = computed(() => {
  const n = Number(total_cost.value);
  return !Number.isNaN(n) && n >= 0;
});
const formOk = computed(() => !!service_name.value && isValidTotal.value);

const normalizeTotal = () => {
  if (!isValidTotal.value) total_cost.value = 0;
};

const trySubmit = () => {
  touched.value.name = true;
  touched.value.total = true;
  if (formOk.value && !submitting.value) submit();
};

const submit = async () => {
  error.value = "";
  touched.value.name = true;
  touched.value.total = true;
  if (!formOk.value) return;

  submitting.value = true;
  try {
    const payload = {
      service_name: service_name.value,
      total_cost: Number(total_cost.value),

      // If you want to send these too, uncomment:
      // customer_id: props.defaultCustomerId ?? null,
      // employee_id: props.defaultEmployeeId ?? null,
      // order_id: props.orderId ?? null,
    };

    // Use Ziggy route() if available; otherwise fallback
    const url =
      typeof route === "function" && route("pos.service_submit")
        ? route("pos.service_submit")
        : "/service/submit";

    const { data } = await axios.post(url, payload);

    // Emit back to parent (for toast/refresh or adding to services list)
    emit("saved", data);
    emit("update:open", false);
  } catch (e) {
    error.value = e?.response?.data?.message ?? "Failed to save service";
  } finally {
    submitting.value = false;
  }
};

// Mark as touched when user edits
watch(service_name, () => (touched.value.name = true));
watch(total_cost, () => (touched.value.total = true));





const formRef = ref(null);

const moveFocus = (e, dir) => {
  const form = formRef.value;
  if (!form) return;

  const focusable = Array.from(
    form.querySelectorAll(
      'input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])'
    )
  ).filter(el => el.offsetParent !== null); // visible only

  const i = focusable.indexOf(e.target);
  if (i === -1) return;

  let next = i + dir;
  // clamp within bounds
  if (next < 0) next = 0;
  if (next >= focusable.length) next = focusable.length - 1;

  const el = focusable[next];
  el.focus();
  // optional: select text in inputs
  if (typeof el.select === "function") el.select();
};

const focusNext = (e) => {
  e.preventDefault(); // stop caret movement (esp. in textarea)
  moveFocus(e, 1);
};

const focusPrev = (e) => {
  e.preventDefault();
  moveFocus(e, -1);
};





</script>
