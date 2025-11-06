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
          <DialogPanel class="bg-white text-black border-4 border-blue-600 rounded-[20px] shadow-xl w-5/6 lg:w-3/6 p-6">
            <div class="flex flex-col items-start justify-start w-full h-full px-2 pt-4">
              <div class="flex justify-center w-full h-full py-4 space-x-8 items-start-center">
                <!-- Left Side: Image -->
                <div class="w-1/2">
                  <img
                    :src="selectedProduct.image ? `/${selectedProduct.image}` : '/images/placeholder.jpg'"
                    alt="Product Image"
                    class="object-cover h-full rounded-2xl"
                  />
                </div>

                <!-- Right Side: Product Details -->
                <div class="flex flex-col justify-between w-1/2 h-full">
                  <!-- Product Name & Discount Badge -->
                  <div class="flex items-center justify-between">
                    <p class="text-3xl font-bold text-black w-full break-words">
                      {{ selectedProduct.name }}
                      <span
                        v-if="selectedProduct.discount && selectedProduct.discount > 0"
                        class="inline-block px-2 py-2 text-sm font-medium text-white bg-red-600 rounded"
                      >
                        {{ selectedProduct.discount }} % OFF
                      </span>
                    </p>
                  </div>

                  <!-- Category -->
                  <p class="pb-6 mt-2 text-[#00000099] text-xl font-normal italic">
                    {{ selectedProduct.category?.name ?? "No Category" }}
                  </p>

                  <!-- Supplier -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Supplier: </span>
                    {{ selectedProduct.supplier?.name || "N/A" }}
                  </p>

                  <!-- Product Code -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Product Code: </span>
                    {{ selectedProduct?.code ?? "N/A" }}
                  </p>

                  <!-- Batch Number -->
                  <p class="pb-6 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Batch No: </span>
                    {{ selectedProduct?.batch_no ?? "N/A" }}
                  </p>

                  <!-- Color -->
                  <div class="flex items-center justify-between w-full text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-justify text-[#00000099] text-2xl flex items-center pb-6">
                        Color:
                        <span class="font-bold text-black">
                          {{ selectedProduct?.color?.name ?? "N/A" }}
                        </span>
                      </p>
                    </div>
                  </div>

                  <!-- Size -->
                  <div class="flex items-center justify-between w-full text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099] text-2xl pb-6">
                        Size:
                        <span class="px-2 py-2 font-bold text-black border-2 border-gray-800 rounded-xl">
                          {{ selectedProduct?.size?.name ?? "N/A" }}
                        </span>
                      </p>
                    </div>
                  </div>

                  <!-- Selling Price & Cost Price -->
                  <div class="flex items-center justify-between w-full pb-6 text-2xl">
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Selling Price:</p>
                      <p class="font-bold text-black">
                        {{ selectedProduct?.selling_price ?? "N/A" }} LKR
                      </p>
                    </div>
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Cost Price:</p>
                      <p class="font-bold text-black">
                        {{ selectedProduct?.cost_price ?? "N/A" }} LKR
                      </p>
                    </div>
                  </div>

                  <!-- Discount Price & Quantity -->
                  <div class="flex items-center justify-between w-full pb-6 text-2xl">
                    <div
                      class="flex flex-col w-full"
                      v-if="selectedProduct.discount && selectedProduct.discount > 0"
                    >
                      <p class="text-[#00000099]">Discount Price:</p>
                      <p class="font-bold text-black">
                        {{
                          selectedProduct.selling_price &&
                          selectedProduct.discount &&
                          selectedProduct.discount > 0
                            ? (
                                selectedProduct.selling_price -
                                (selectedProduct.selling_price * selectedProduct.discount) / 100
                              ).toFixed(2)
                            : selectedProduct.selling_price
                        }}
                        LKR
                      </p>
                    </div>
                    <div class="flex flex-col w-full">
                      <p class="text-[#00000099]">Quantity:</p>
                      <p class="font-bold text-black">
                        {{ selectedProduct?.stock_quantity ?? "N/A" }}
                      </p>
                    </div>
                  </div>

                  <!-- Created Date -->
                  <p class="pb-8 text-2xl font-bold text-black">
                    <span class="text-[#00000099] font-normal">Created On: </span>
                    {{ formattedDate }}
                  </p>

                  <!-- Barcode Printing Section -->
                  <div class="mt-2" v-if="HasRole(['Admin', 'Manager'])">
                    <label class="block mb-1 text-xl text-gray-700">Number of Barcodes:</label>
                    <input
                      type="number"
                      v-model="barcodeCount"
                      min="1"
                      class="w-full px-4 py-2 text-xl border rounded focus:outline-none focus:ring focus:ring-blue-300"
                    />
                    <button
                      class="w-full px-4 py-3 mt-4 text-2xl font-semibold tracking-widest text-white bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2"
                      @click="generateAndPrintBarcodes"
                    >
                      Print Bar Codes
                    </button>
                  </div>
                </div>
              </div>
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
import { ref, watch, computed } from "vue";
import { useForm } from "@inertiajs/vue3";
import dayjs from "dayjs";
import advancedFormat from "dayjs/plugin/advancedFormat";
import { HasRole } from "@/Utils/Permissions";

dayjs.extend(advancedFormat);

const playClickSound = () => {
  const clickSound = new Audio("/sounds/click-sound.mp3");
  clickSound.play();
};

const emit = defineEmits(["update:open"]);

const { selectedProduct } = defineProps({
  open: {
    type: Boolean,
    required: true,
  },
  categories: {
    type: Array,
    required: true,
  },
  colors: {
    type: Array,
    required: true,
  },
  sizes: {
    type: Array,
    required: true,
  },
  selectedProduct: {
    type: Object,
    default: null,
  },
});

const barcodeCount = ref(1);

const formattedDate = computed(() =>
  selectedProduct && selectedProduct.created_at
    ? dayjs(selectedProduct.created_at).format("Do MMMM YYYY")
    : ""
);

function generateAndPrintBarcodes() {
  const barcode = selectedProduct?.barcode;
  const count = parseInt(barcodeCount.value, 10);

  if (!barcode || barcode.trim() === '') {
    alert('Please enter a barcode value.');
    return;
  }
  if (!count || count < 1) {
    alert('Please enter a valid number of barcodes.');
    return;
  }

  // Sizing constants (mm/px)
  const MM_TO_PX = 3.78;
  const LABEL_W_MM = 30;
  const LABEL_H_MM = 18;
  const INNER_PADDING_MM = 0.5;
  const GUTTER_MM = 0; // Removed gap - configured in printer
  const BARCODE_H_MM = 8; // Reduced from 12
  const NAME_FZ_PX = 9;
  const PRICE_FZ_PX = 16; // Increased for better visibility

  // Build labels HTML
  const labelsHtml = Array.from({ length: count }).map((_, idx) => `
    <div class="barcode-label">
      <div class="product-name">${selectedProduct?.name || 'N/A'}</div>
      <div class="product-name">${selectedProduct?.barcode || 'N/A'}</div>
      <div class="barcode-svg"><svg id="barcode${idx + 1}"></svg></div>
      <div class="bottom-info">${(selectedProduct?.selling_price ?? 'N/A')} LKR</div>
    </div>
  `).join('');

  const htmlContent = `
    <html>
    <head>
      <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
      <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        html, body { background:white; margin:0; padding:0; padding-top: 3mm; }
        body { font-family:"Poppins", sans-serif; -webkit-print-color-adjust:exact; print-color-adjust:exact; }

        .barcode-container {
          width: 100%;
          margin: 0;
          margin-top: 2mm;
          padding: 0;
          display: flex;
          flex-wrap: wrap;
          gap: ${GUTTER_MM}mm;
          align-content: flex-start;
          justify-content: flex-start;
        }

        .barcode-label {
          width: ${LABEL_W_MM}mm;
          height: ${LABEL_H_MM}mm;
          background: white;
          padding: ${INNER_PADDING_MM}mm;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          overflow: hidden;
          margin: 0;
        }

        .product-name {
          font-size: ${NAME_FZ_PX}px;
          font-weight: 600;
          line-height: 1.1;
          width: 100%;
          text-align: center;
          white-space: nowrap;
          overflow: hidden;
          text-overflow: ellipsis;
          margin: 0;
          padding: 0;
        }

        .barcode-svg {
          width: 100%;
          height: ${BARCODE_H_MM}mm;
          display: flex;
          align-items: center;
          justify-content: center;
          overflow: hidden;
          margin: 0;
          padding: 0;
        }
        .barcode-svg svg {
          width: 100%;
          height: 100%;
          display: block;
        }

        .bottom-info {
          font-size: ${PRICE_FZ_PX}px;
          font-weight: 700;
          line-height: 1;
          white-space: nowrap;
          width: 100%;
          text-align: center;
          margin: 0;
          padding: 0;
        }

        @media print {
          @page {
            margin: 0;
            padding: 0;
          }
          html, body { 
            margin: 0; 
            padding: 0; 
          }
          .barcode-container {
            margin: 0;
            padding: 0;
          }
        }
      </style>
    </head>
    <body>
      <div class="barcode-container">
        ${labelsHtml}
      </div>
    </body>
    </html>
  `;

  const printWindow = window.open('', '_blank');
  printWindow.document.write(htmlContent);
  printWindow.document.close();

  printWindow.onload = () => {
    for (let i = 1; i <= count; i++) {
      const svg = printWindow.document.getElementById(`barcode${i}`);
      if (!svg) continue;
      JsBarcode(svg, barcode, {
        format: 'CODE128',
        lineColor: '#000',
        width: 1,
        height: Math.round(BARCODE_H_MM * MM_TO_PX),
        displayValue: false,
        margin: 0,
      });
    }

    setTimeout(() => {
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }, 400);
  };
}
</script>