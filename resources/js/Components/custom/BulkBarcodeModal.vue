<template>
  <TransitionRoot as="template" :show="modelValue">
    <Dialog class="relative z-10" @close="closeModal">
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
        <div class="fixed inset-0 bg-black bg-opacity-50" />
      </TransitionChild>

      <!-- Modal -->
      <div class="fixed inset-0 z-20 flex items-center justify-center p-4">
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95"
        >
          <DialogPanel class="bg-white w-full max-w-6xl rounded-xl shadow-xl p-6 max-h-[90vh] flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold text-gray-800">Select Products for Bulk Barcode</h2>
              <button
                @click="closeModal"
                class="text-red-600 hover:text-red-800 text-xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-red-100 transition-colors"
                aria-label="Close modal"
              >
                ✕
              </button>
            </div>

            <!-- Loading State -->
            <div v-if="isGenerating" class="flex items-center justify-center py-8">
              <div class="text-center">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                <p class="text-gray-600">Generating barcodes...</p>
              </div>
            </div>

            <!-- Main Content -->
            <div v-else class="flex-1 flex flex-col min-h-0">
              <!-- Selection Info -->
              <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-800">
                  Selected: {{ selectedProductIds.length }} of {{ products.length }} products
                </p>
              </div>

              <!-- Table Container -->
              <div class="flex-1 overflow-hidden border rounded-lg">
                <div class="overflow-y-auto max-h-full">
                  <table class="min-w-full text-sm text-left table-auto">
                    <thead class="bg-gray-200 sticky top-0">
                      <tr>
                        <th class="p-3 w-12">
                          <input
                            type="checkbox"
                            v-model="allChecked"
                            @change="toggleAll"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                          />
                        </th>
                        <th class="p-3 font-semibold">Product Name</th>
                        <th class="p-3 font-semibold">Product Code</th>
                        <th class="p-3 font-semibold">Wholesale Price</th>
                        <th class="p-3 font-semibold">Retail Price</th>
                        <th class="p-3 font-semibold">Stock Quantity</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr
                        v-for="product in products"
                        :key="product.id"
                        class="border-t hover:bg-gray-50 transition-colors"
                        :class="{ 'bg-blue-50': selectedProductIds.includes(product.id) }"
                      >
                        <td class="p-3">
                          <input
                            type="checkbox"
                            v-model="selectedProductIds"
                            :value="product.id"
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                          />
                        </td>
                        <td class="p-3 font-medium">{{ product.name || 'N/A' }}</td>
                        <td class="p-3 font-mono">{{ product.code || 'N/A' }}</td>
                        <td class="p-3">{{ formatPrice(product.whole_price) }}</td>
                        <td class="p-3">{{ formatPrice(product.selling_price) }}</td>
                        <td class="p-3">{{ product.stock_quantity || 0 }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Actions -->
              <div class="mt-6 flex justify-end space-x-3">
                <button
                  @click="generateBarcodes"
                  :disabled="selectedProductIds.length === 0"
                  class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:bg-gray-300 disabled:cursor-not-allowed transition-colors font-medium"
                >
                  Generate {{ selectedProductIds.length }} Barcode{{ selectedProductIds.length !== 1 ? 's' : '' }}
                </button>
                <button
                  @click="closeModal"
                  class="px-6 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400 transition-colors font-medium"
                >
                  Cancel
                </button>
              </div>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, watch, computed } from 'vue';
import {
  Dialog,
  DialogPanel,
  TransitionRoot,
  TransitionChild,
} from '@headlessui/vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  products: {
    type: Array,
    default: () => []
  },
});

const emit = defineEmits(['update:modelValue', 'barcode-generated']);

const selectedProductIds = ref([]);
const allChecked = ref(false);
const isGenerating = ref(false);

const closeModal = () => {
  emit('update:modelValue', false);
  // Reset selections when closing
  selectedProductIds.value = [];
  allChecked.value = false;
};

const toggleAll = () => {
  if (allChecked.value) {
    selectedProductIds.value = props.products.map(p => p.id);
  } else {
    selectedProductIds.value = [];
  }
};

// Watch for selection changes
watch(
  () => selectedProductIds.value,
  (val) => {
    allChecked.value = val.length === props.products.length && props.products.length > 0;
  },
  { deep: true }
);

// Reset selections when modal is opened
watch(
  () => props.modelValue,
  (newVal) => {
    if (newVal) {
      selectedProductIds.value = [];
      allChecked.value = false;
    }
  }
);

const formatPrice = (price) => {
  if (price === null || price === undefined || price === '') return '0.00';
  return parseFloat(price).toFixed(2);
};

const validateProductCode = (code) => {
  if (!code || code.toString().trim() === '') {
    return false;
  }
  const cleanCode = code.toString().trim();
  return cleanCode.length >= 4 && cleanCode.length <= 20;
};

const generateBarcodes = async () => {
  if (selectedProductIds.value.length === 0) {
    alert("Please select at least one product.");
    return;
  }

  isGenerating.value = true;

  try {
    const selectedProducts = props.products.filter(p =>
      selectedProductIds.value.includes(p.id)
    );

    // Validate products have valid codes
    const invalidProducts = selectedProducts.filter(p => !validateProductCode(p.barcode));
    if (invalidProducts.length > 0) {
      alert(`The following products have invalid codes and will be skipped: ${invalidProducts.map(p => p.name).join(', ')}`);
    }

    const validProducts = selectedProducts.filter(p => validateProductCode(p.barcode));

    if (validProducts.length === 0) {
      alert("No products with valid codes selected. Please ensure products have valid barcode numbers.");
      return;
    }

    const printWindow = window.open('', '_blank');

    if (!printWindow) {
      alert("Pop-up blocked. Please allow pop-ups for this site to generate barcodes.");
      return;
    }

    const htmlContent = `
<!DOCTYPE html>
<html>
  <head>
    <title>Print Barcodes</title>
    <meta charset="UTF-8">
      <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: Arial, sans-serif;
        background: white;
        line-height: 1;
      }

      .barcode-container {
        width: 75mm;
        margin: 0 auto;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0mm;
      }

      .barcode-row {
        display: flex;
        justify-content: space-between;
        gap: 0mm;
      }

      .barcode-label {
        width: 37.5mm;
        height: 25mm;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0.5mm;
        text-align: center;
        background: white;
        box-sizing: border-box;
        overflow: hidden;
      }

      .product-name {
        font-size: 9px;
        font-weight: bold;
        line-height: 1;


        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 100%;
      }

      .barcode-svg {
        flex: 1;
        display: flex;
        justify-content: center;
        align-items: center;
         min-height: 14mm;
        max-height: 14mm;
        width: 100%;
      }

      .barcode-svg svg {
        max-width: 100%;
        max-height: 100%;
        width: auto;
        height: auto;
      }

      .bottom-info {
          display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 7px;
        font-weight: bold;
        font-family: monospace;
        width: 100%;
        margin-top: 0.5mm;
        white-space: nowrap;
        line-height: 1;
        padding: 0 7mm;
      }

      .bottom-info span {
        max-width: 49%;
        overflow: hidden;
        text-overflow: ellipsis;
      }

      @media print {
        @page {
          margin: 0;
          size: 75mm auto;
        }

        body {
          margin: 0;
          padding: 0;
          -webkit-print-color-adjust: exact;
          print-color-adjust: exact;
        }

        .barcode-label {
          border: none;
          break-inside: avoid;
        }

        .barcode-row {
          break-inside: avoid;
        }
      }
    </style>
  </head>
  <body>
    <div class="barcode-container">
      ${(() => {
        const rows = [];
        for (let i = 0; i < validProducts.length; i += 2) {
          const first = validProducts[i];
          const second = validProducts[i + 1];
          rows.push(`
            <div class="barcode-row">
              ${[first, second].filter(Boolean).map(product => `
                <div class="barcode-label">
                  <div class="product-name">${escapeHtml(product.name || '')}</div>
                  <div class="barcode-svg">
                    <svg id="barcode-${product.id}"></svg>
                  </div>
                  <div class="bottom-info">
                    <span>${escapeHtml(product.code)}</span>
                    <span>Rs:${parseFloat(product.selling_price || 0).toFixed(2)}</span>
                  </div>
                </div>
              `).join('')}
            </div>
          `);
        }
        return rows.join('');
      })()}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"><\/script>
    <script>
      function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
      }

      window.onload = function () {
        try {
          ${validProducts.map(product => `
            try {
              JsBarcode("#barcode-${product.id}", "${product.barcode}", {
                format: "CODE128",
                lineColor: "#000",
                width: 10,
                height: 45,
                displayValue: false,
                margin: 0,
                background: "transparent"
              });
            } catch (e) {
              console.error("Barcode error for product ${product.id}:", e);
              document.getElementById("barcode-${product.id}").innerHTML = '<text x="50%" y="50%" text-anchor="middle" font-size="8">Invalid</text>';
            }
          `).join('')}

          setTimeout(() => {
            window.print();
          }, 500);
        } catch (e) {
          console.error("Printing error:", e);
          alert("Error generating barcodes. Please try again.");
        }
      };

      window.onafterprint = function () {
        setTimeout(() => {
          window.close();
        }, 100);
      };
    <\/script>
  </body>
</html>
`;

    printWindow.document.open();
    printWindow.document.write(htmlContent);
    printWindow.document.close();

    // Emit event to parent component
    emit('barcode-generated', {
      productCount: validProducts.length,
      products: validProducts
    });

    closeModal();

  } catch (error) {
    console.error('Error generating barcodes:', error);
    alert('An error occurred while generating barcodes. Please try again.');
  } finally {
    isGenerating.value = false;
  }
};

// Helper function for HTML escaping
const escapeHtml = (text) => {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
};
</script>

<style scoped>
/* Additional component-specific styles */
.table-container {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e0 #f7fafc;
}

.table-container::-webkit-scrollbar {
  width: 8px;
}

.table-container::-webkit-scrollbar-track {
  background: #f7fafc;
}

.table-container::-webkit-scrollbar-thumb {
  background: #cbd5e0;
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background: #a0aec0;
}
</style>
