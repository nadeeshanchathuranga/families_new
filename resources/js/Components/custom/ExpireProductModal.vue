<template>
  <TransitionRoot as="template" :show="open">
    <Dialog as="div" class="relative z-50" @close="$emit('update:open', false)">
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" />
      </TransitionChild>

      <!-- Modal Content -->
      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="w-full max-w-6xl max-h-[90vh] overflow-hidden bg-white border-4 border-yellow-600 rounded-xl shadow-xl">
            <!-- Header -->
            <div class="flex justify-between items-center px-6 py-4 bg-yellow-600 text-white">
              <DialogTitle class="text-2xl font-bold">
                Expiry Alert – Products Nearing Expiration
              </DialogTitle>
              <button @click="$emit('update:open', false)" class="text-3xl font-bold hover:text-gray-200">
                &times;
              </button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto max-h-[calc(90vh-130px)]">
              <div v-if="expiryProducts.length > 0">
                <!-- Alert -->
                <div class="mb-6 p-4 border-l-4 border-yellow-500 bg-yellow-100 text-yellow-700 font-semibold">
                  <i class="ri-time-line mr-2 text-lg"></i>
                  {{ expiryProducts.length }} product(s) are nearing their expiration.
                </div>

                <!-- Table -->
                <div class="overflow-x-auto">
                  <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                    <thead class="bg-gray-50">
                      <tr>
                        <th class="th">Name</th>
                        <th class="th">Category</th>
                        <th class="th">Supplier</th>
                        <th class="th">Code</th>
                        <th class="th">Batch</th>
                        <th class="th text-center">Expiry</th>
                        <th class="th text-center">Days Left</th>
                        <th class="th text-center">Stock</th>
                        <!-- <th class="th text-center">Status</th> -->
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                      <tr
                        v-for="product in expiryProducts"
                        :key="product.id"
                        :class="getRowClass(product)"
                        class="hover:bg-gray-50 transition"
                      >
                        <td class="td">{{ product.name || 'N/A' }}</td>
                        <td class="td">{{ product.category?.name || 'N/A' }}</td>
                        <td class="td">{{ product.supplier?.name || 'N/A' }}</td>
                        <td class="td">{{ product.code || 'N/A' }}</td>
                        <td class="td">{{ product.batch_no || 'N/A' }}</td>
                        <td class="td text-center">{{ formatDate(product.expiry_date) }}</td>
                        <td class="td text-center">
                          <span :class="getDaysRemainingClass(product)" class="badge">
                            {{ getDaysRemaining(product.expiry_date) }}
                          </span>
                        </td>
                        <td class="td text-center">
                          <span class="badge bg-blue-100 text-blue-800">
                            {{ product.stock_quantity || 0 }}
                          </span>
                        </td>
                        <!-- <td class="td text-center">
                          <span :class="getExpiryStatusClass(product)" class="badge">
                            {{ getExpiryStatusText(product) }}
                          </span>
                        </td> -->
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Empty State -->
              <div v-else class="text-center py-12 text-gray-700">
                <i class="ri-checkbox-circle-line text-green-500 text-5xl mb-2"></i>
                <h3 class="text-xl font-bold">All Products Are Fresh</h3>
                <p>No products are nearing expiration at this time.</p>
              </div>
            </div>

            <!-- Footer -->
            <div class="flex justify-end items-center gap-3 px-6 py-4 border-t bg-gray-50">
              <button
                @click="$emit('update:open', false)"
                class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600"
              >
                Close
              </button>
              <button
                v-if="expiryProducts.length > 0"
                @click="exportReport"
                class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                <i class="ri-download-line mr-2"></i>
                Export CSV
              </button>
            </div>
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
  TransitionRoot,
  TransitionChild
} from '@headlessui/vue';

const props = defineProps({
  open: Boolean,
  expiryProducts: {
    type: Array,
    default: () => []
  }
});
const emit = defineEmits(['update:open']);

// Format date
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return isNaN(date) ? 'N/A' : date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

// Get days remaining
const getDaysRemaining = (dateString) => {
  const today = new Date();
  const expiry = new Date(dateString);
  const days = Math.ceil((expiry - today) / (1000 * 60 * 60 * 24));
  if (isNaN(days)) return 'N/A';
  if (days < 0) return `Expired ${Math.abs(days)}d ago`;
  if (days === 0) return 'Today';
  return `${days} days`;
};

// CSS classes
const getDaysRemainingClass = (product) => {
  const days = getDaysRemaining(product.expiry_date);
  if (typeof days !== 'string') return 'bg-gray-100 text-gray-800';
  if (days.includes('Expired') || days === 'Today') return 'bg-red-100 text-red-800';
  if (parseInt(days) <= 7) return 'bg-red-100 text-red-800';
  if (parseInt(days) <= 30) return 'bg-yellow-100 text-yellow-800';
  return 'bg-green-100 text-green-800';
};

const getRowClass = (product) => {
  const days = getDaysRemaining(product.expiry_date);
  if (typeof days !== 'string') return '';
  if (days.includes('Expired') || days === 'Today') return 'bg-red-50';
  if (parseInt(days) <= 30) return 'bg-yellow-50';
  return '';
};

const getExpiryStatusClass = (product) => getDaysRemainingClass(product);
const getExpiryStatusText = (product) => {
  const days = getDaysRemaining(product.expiry_date);
  if (typeof days !== 'string') return 'No Date';
  if (days.includes('Expired')) return 'Expired';
  if (days === 'Today') return 'Today';
  if (parseInt(days) <= 7) return 'Critical';
  if (parseInt(days) <= 30) return 'Warning';
  return 'Fresh';
};

// Export CSV
const exportReport = () => {
  const csv = [
    ['Product Name', 'Category', 'Supplier', 'Code', 'Batch No', 'Expiry Date', 'Days Remaining', 'Stock', 'Status'],
    ...props.expiryProducts.map(p => [
      p.name || 'N/A',
      p.category?.name || 'N/A',
      p.supplier?.name || 'N/A',
      p.code || 'N/A',
      p.batch_no || 'N/A',
      formatDate(p.expiry_date),
      getDaysRemaining(p.expiry_date),
      p.stock_quantity || 0,
      getExpiryStatusText(p)
    ])
  ]
    .map(row => row.join(','))
    .join('\n');

  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
  const url = URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.setAttribute('download', `expiry-report-${new Date().toISOString().slice(0, 10)}.csv`);
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  URL.revokeObjectURL(url);
};
</script>

<style scoped>
.th {
  @apply px-4 py-3 text-left text-sm font-bold text-gray-600 uppercase border-b;
}
.td {
  @apply px-4 py-4 text-sm text-gray-700;
}
.badge {
  @apply inline-flex items-center px-3 py-1 rounded-full text-sm font-medium;
}
</style>
