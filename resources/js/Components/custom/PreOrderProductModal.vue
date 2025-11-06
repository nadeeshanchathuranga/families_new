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
         <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
            <TransitionChild
               as="template"
               enter="ease-out duration-300"
               enter-from="opacity-0 scale-95"
               enter-to="opacity-100 scale-100"
               leave="ease-in duration-200"
               leave-from="opacity-100 scale-100"
               leave-to="opacity-0 scale-95"
               >
               <DialogPanel class="bg-white text-black border-4 border-red-600 rounded-[20px] shadow-xl w-full max-w-6xl max-h-[90vh] overflow-hidden">
                  <!-- Modal Header -->
                  <div class="bg-red-600 text-white px-6 py-4 flex items-center justify-between">
                     <div class="flex items-center space-x-3">
                        <DialogTitle class="text-2xl font-bold">
                           Pre-Order Alert - Low Stock Productsddd
                        </DialogTitle>
                     </div>
                     <button
                        @click="$emit('update:open', false)"
                        class="text-white hover:text-gray-200 text-2xl font-bold"
                     >
                        ×
                     </button>
                  </div>

                  <!-- Modal Body -->
                  <div class="p-6 overflow-y-auto max-h-[calc(90vh-120px)]">
                     <div v-if="preOrderProducts && preOrderProducts.length > 0" class="space-y-4">
                        <!-- Alert Summary -->
                        <div class="bg-yellow-100 border-l-4 border-yellow-500 p-4 mb-6">
                           <div class="flex items-center">
                              <i class="ri-information-line text-yellow-500 text-xl mr-2"></i>
                              <p class="text-yellow-700 font-semibold">
                                 {{ preOrderProducts.length }} product(s) have reached or fallen below their pre-order level.
                              </p>
                           </div>
                        </div>

                        <!-- Products Table -->
                        <div class="overflow-x-auto">
                           <table class="min-w-full bg-white border border-gray-200 rounded-lg shadow">
                              <thead class="bg-gray-50">
                                 <tr>
                                    <th class="px-4 py-3 text-left text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Product Name
                                    </th>
                                    <th class="px-4 py-3 text-left text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Category
                                    </th>
                                    <th class="px-4 py-3 text-left text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Supplier
                                    </th>
                                    <th class="px-4 py-3 text-left text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Code
                                    </th>
                                    <th class="px-4 py-3 text-left text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Batch No
                                    </th>


                                    <th class="px-4 py-3 text-center text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Current Stock
                                    </th>
                                    <th class="px-4 py-3 text-center text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Pre-Order Level
                                    </th>
                                    <th class="px-4 py-3 text-center text-md font-bold text-gray-500 uppercase tracking-wider border-b">
                                       Status
                                    </th>
                                 </tr>
                              </thead>
                              <tbody class="divide-y divide-gray-200">
                                 <tr
                                    v-for="product in preOrderProducts"
                                    :key="product.id"
                                    class="hover:bg-gray-50 transition-colors duration-200"
                                 >
                                    <td class="px-4 py-4 text-md font-medium text-gray-900">
                                       {{ product.name || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 text-md text-gray-600">
                                       {{ product.category?.name || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 text-md text-gray-600">
                                       {{ product.supplier?.name || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 text-md text-gray-600">
                                       {{ product.code || 'N/A' }}
                                    </td>
                                    <td class="px-4 py-4 text-md text-gray-600">
                                       {{ product.batch_no || 'N/A' }}
                                    </td>

                                    <td class="px-4 py-4 text-center">
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-md font-medium bg-red-100 text-red-800">
                                          {{ product.total_quantity || 0 }}
                                       </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-md font-medium bg-yellow-100 text-yellow-800">
                                          {{ product.preorder_level_qty || 0 }}
                                       </span>
                                    </td>
                                    <td class="px-4 py-4 text-center">
                                       <span
                                          class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                          :class="getStatusClass(product)"
                                       >
                                          {{ getStatusText(product) }}
                                       </span>
                                    </td>
                                 </tr>
                              </tbody>
                           </table>
                        </div>
                     </div>

                     <!-- No Products Message -->
                     <div v-else class="text-center py-12">
                        <i class="ri-checkbox-circle-line text-green-500 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">All Products are Well Stocked!</h3>
                        <p class="text-gray-600">No products have reached their pre-order level at this time.</p>
                     </div>
                  </div>

                  <!-- Modal Footer -->
                  <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3 border-t">
                     <button
                        @click="$emit('update:open', false)"
                        class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors duration-200"
                     >
                        Close
                     </button>
                     <button
                        v-if="preOrderProducts && preOrderProducts.length > 0"
                        @click="exportReport"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200"
                     >
                        <i class="ri-download-line mr-2"></i>
                        Export Report
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
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";
import { computed } from "vue";

const props = defineProps({
  open: Boolean,
  preOrderProducts: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(["update:open"]);

// Helper function to determine status class
const getStatusClass = (product) => {
  const currentStock = product.total_quantity || 0;
  const preorderLevel = product.preorder_level_qty || 0;

  if (currentStock === 0) {
    return 'bg-red-100 text-red-800';
  } else if (currentStock <= preorderLevel) {
    return 'bg-yellow-100 text-yellow-800';
  }
  return 'bg-green-100 text-green-800';
};

// Helper function to determine status text
const getStatusText = (product) => {
  const currentStock = product.total_quantity || 0;
  const preorderLevel = product.preorder_level_qty || 0;

  if (currentStock === 0) {
    return 'Out of Stock';
  } else if (currentStock <= preorderLevel) {
    return 'Low Stock';
  }
  return 'In Stock';
};

// Export functionality
const exportReport = () => {
  // Create CSV content
  const csvContent = [
    'Product Name,Category,Supplier,Code,Batch No,Color,Size,Current Stock,Pre-Order Level,Status',
    ...props.preOrderProducts.map(product => [
      product.name || 'N/A',
      product.category?.name || 'N/A',
      product.supplier?.name || 'N/A',
      product.code || 'N/A',
      product.batch_no || 'N/A',

      product.total_quantity || 0,
      product.preorder_level_qty || 0,
      getStatusText(product)
    ].join(','))
  ].join('\n');

  // Create and download file
  const blob = new Blob([csvContent], { type: 'text/csv' });
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = `pre-order-alert-report-${new Date().toISOString().split('T')[0]}.csv`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  window.URL.revokeObjectURL(url);
};
</script>
