<template>
  <TransitionRoot as="template" :show="open">
    <Dialog class="relative z-10" @close="$emit('update:open', false)">
      <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-200" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 flex items-center justify-center">
        <TransitionChild as="template" enter="ease-out duration-300" enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100" leave="ease-in duration-200" leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95">
          <DialogPanel
            class="bg-black border-4 border-blue-600 rounded-[20px] shadow-xl max-w-md w-full p-6 text-center">
            <DialogTitle class="text-xl font-bold text-white">Add Credit Payment</DialogTitle>

            <!-- Smart Search -->
            <div class="mt-4 relative" @click.outside="showResults = false">
              <input v-model="search" @input="showResults = search.length > 0" type="text"
                placeholder="Search by Sale ID, Order ID, or Customer"
                class="w-full px-4 py-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600" />
              <ul v-if="showResults && searchResults.length"
                class="absolute z-50 w-full bg-white shadow-lg max-h-56 overflow-y-auto rounded-md mt-1">
                <li v-for="sale in searchResults" :key="sale.id" @click="selectSale(sale); showResults = false"
                  class="px-4 py-2 hover:bg-blue-100 cursor-pointer">
                  <span class="font-semibold">Sale:</span> {{ sale.id }}
                  <span class="opacity-60">|</span>
                  <span class="font-semibold">Order:</span> {{ sale.order_id ?? '-' }}
                  <span class="opacity-60">|</span>
                  <span class="font-semibold">Customer:</span> {{ sale.customer?.name ?? '-' }}
                </li>
              </ul>
            </div>


            <form @submit.prevent="submit" class="mt-6 space-y-4 text-left">
              <!-- Customer -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Customer</label>
                <select v-model="form.customer_id" class="w-full mt-2 px-4 py-2 rounded-md text-black">
                  <option value="" disabled>Select customer</option>
                  <option v-for="c in customersOptions" :key="c.id" :value="String(c.id)">
                    {{ c.name }}
                  </option>
                </select>
              </div>

              <!-- Order -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Order ID</label>
                <select v-model="form.order_id" class="w-full mt-2 px-4 py-2 rounded-md text-black">
                  <option value="" disabled>Select order</option>
                  <option v-for="o in ordersOptions" :key="String(o)" :value="String(o)">
                    {{ o }}
                  </option>
                </select>
              </div>

              <!-- Sale -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Sale ID</label>
                <select v-model="form.sale_id" class="w-full mt-2 px-4 py-2 rounded-md text-black">
                  <option value="" disabled>Select sale</option>
                  <option v-for="s in salesOptions" :key="String(s)" :value="String(s)">
                    {{ s }}
                  </option>
                </select>
              </div>

              <!-- Pending Payment -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Pending Payment:</label>
                <input type="number" v-model="form.pending_payment" readonly
                  class="w-full px-4 py-2 mt-2 text-black bg-gray-200 rounded-md" />
              </div>

              <!-- Part Payment -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Part Payment:</label>
                <input type="number" v-model="form.part_payment"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600" />
              </div>

              <!-- Description -->
              <div>
                <label class="block text-sm font-medium text-gray-300">Description</label>
                <textarea v-model="form.description" rows="3"
                  class="w-full px-4 py-2 mt-2 text-black rounded-md focus:outline-none focus:ring focus:ring-blue-600"></textarea>
              </div>

              <div class="mt-6 space-x-4 text-center">
                <button type="submit" class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                  Make Payment
                </button>
                <button @click="() => emit('update:open', false)" type="button"
                  class="px-4 py-2 text-gray-700 bg-gray-300 rounded hover:bg-gray-400">
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
import { ref, computed, watch } from 'vue';
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue';
import { useForm } from '@inertiajs/vue3';

const emit = defineEmits(['update:open']);

const props = defineProps({
  open: { type: Boolean, required: true },
  sales: { type: Array, default: () => [] },
  creditPayments: { type: Array, default: () => [] },
});

const form = useForm({
  customer_id: '',
  order_id: '',
  sale_id: '',
  pending_payment: 0,
  part_payment: 0,
  description: '',
});

const search = ref('');
const showResults = ref(false);

const asStr = (v) => (v == null ? '' : String(v));
const eq = (a, b) => String(a) === String(b);
const safeName = (c) => (c && c.name ? c.name : '');

const creditSales = computed(() => props.sales.filter(s => Number(s.credit_bill) === 1));

const searchResults = computed(() => {
  if (!search.value) return [];
  const term = search.value.toLowerCase();
  return creditSales.value.filter(sale =>
    asStr(sale.id).toLowerCase().includes(term) ||
    asStr(sale.order_id).toLowerCase().includes(term) ||
    safeName(sale.customer).toLowerCase().includes(term)
  ).slice(0, 30);
});

// Dropdown options
const customersOptions = computed(() => {
  const seen = new Set();
  const out = [];
  creditSales.value.forEach(s => {
    if (s.customer && !seen.has(s.customer.id)) {
      seen.add(s.customer.id);
      out.push({ id: s.customer.id, name: s.customer.name });
    }
  });
  return out.sort((a, b) => a.name.localeCompare(b.name));
});

const ordersOptions = computed(() => {
  const pool = form.customer_id ? creditSales.value.filter(s => eq(s.customer_id, form.customer_id)) : creditSales.value;
  return Array.from(new Set(pool.map(s => s.order_id).filter(Boolean))).sort();
});

const salesOptions = computed(() => {
  let pool = creditSales.value;
  if (form.customer_id) pool = pool.filter(s => eq(s.customer_id, form.customer_id));
  if (form.order_id) pool = pool.filter(s => eq(s.order_id, form.order_id));
  return Array.from(new Set(pool.map(s => String(s.id)))).sort();
});

// Apply selected sale to auto-fill customer, order, pending_payment
const applySale = (sale) => {
  form.customer_id = asStr(sale.customer_id);
  form.order_id = asStr(sale.order_id);
  form.sale_id = asStr(sale.id);

  const existing = props.creditPayments.find(c => eq(c.sale_id, sale.id));
  form.pending_payment = existing ? Number(existing.pending_payment) : Number(sale.total_amount || 0);
};

// Smart search select
const selectSale = (sale) => applySale(sale);

// Watchers to auto-fill related fields
watch(() => form.sale_id, (val) => {
  if (!val) return;
  const s = creditSales.value.find(x => eq(x.id, val));
  if (s) applySale(s);
});

watch(() => form.order_id, (val) => {
  if (!val) return;
  const s = creditSales.value.find(x => eq(x.order_id, val) && (!form.customer_id || eq(x.customer_id, form.customer_id)));
  const chosen = s ?? creditSales.value.find(x => eq(x.order_id, val));
  if (chosen) applySale(chosen);
});

watch(() => form.customer_id, (val) => {
  if (!val) return;
  const belongs = creditSales.value.find(x => eq(x.customer_id, val) && eq(x.order_id, form.order_id) && eq(x.id, form.sale_id));
  if (!belongs) {
    const first = creditSales.value.find(x => eq(x.customer_id, val));
    if (first) applySale(first);
    else {
      form.order_id = '';
      form.sale_id = '';
      form.pending_payment = 0;
    }
  }
});

const submit = () => {
  form.post('/credit_payment', {
    onSuccess: () => {
      form.reset();
      emit('update:open', false);
    },
  });
};
</script>
