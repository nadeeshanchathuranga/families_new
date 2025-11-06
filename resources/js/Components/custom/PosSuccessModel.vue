<template>
  <TransitionRoot as="template" :show="open" static @after-leave="onAfterLeave">
    <Dialog class="relative z-10" static>
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
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click.stop />
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
          <DialogPanel class="bg-white border-4 border-blue-600 rounded-[20px] shadow-xl max-w-xl w-full p-6 text-center">
            <!-- Modal Title -->
            <DialogTitle class="text-5xl font-bold">Payment Successful!</DialogTitle>

            <div class="w-full h-full flex flex-col justify-center items-center space-y-8 mt-4">
              <p class="text-justify text-3xl text-black">Order Payment is Successful!</p>
              <div>
                <img src="/images/checked.png" class="h-24 object-cover w-full" />
              </div>
            </div>

            <div class="flex justify-center items-center space-x-4 pt-4 mt-4">
              <!-- Print -->
              <button
                @click="handlePrintReceipt"
                class="cursor-pointer bg-blue-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl focus:bg-blue-700"
                type="button"
              >
                Print Receipt
              </button>

              <!-- Close -->
              <button
                @click="closeAndRefresh"
                class="cursor-pointer bg-red-600 text-white font-bold uppercase tracking-wider px-4 shadow-xl py-4 rounded-xl focus:bg-red-700"
                type="button"
              >
                Close
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
import { computed, ref, watch, nextTick, onBeforeUnmount } from "vue";
import { usePage } from "@inertiajs/vue3";

const emit = defineEmits(["update:open"]);

const props = defineProps({
  open: { type: Boolean, required: true },
  products: { type: Array, required: true },
  cashier: Object,
  customer: Object,
  orderid: String,
  balance: Number,
  cash: Number,
  subTotal: String,
  totalDiscount: String,
  total: String,
  custom_discount: Number,
  custom_discount_type: String,
  paymentMethod: String,
  isWholesale: Boolean,
  credit_bill: Boolean,
  guide_comi: [Number, String],
  guide_cash: [Number, String],
  guide_name: String,
  guide_pending: [Boolean, Number],

   returnItems: {
        type: Array,
        required: false,
        default: () => [],
    },
});

const page = usePage();
const companyInfo = computed(() => page.props.companyInfo);

// Close modal then reload after leave transition
const closeAndRefresh = async () => {
  emit("update:open", false);     // close modal
  await nextTick();               // let leave animation start
};

const onAfterLeave = () => {
  // when animation completes
  window.location.reload();
};

// Global keyboard shortcuts (active only when modal is open)
const onGlobalKeyDown = (event) => {
  if (!props.open) return;

  // Enter → Print
  if (event.key === "Enter") {
    event.preventDefault();
    handlePrintReceipt();
    return;
  }

  // Delete / Esc → Close
  if (event.key === "Delete" || event.key === "Escape") {
    event.preventDefault();
    closeAndRefresh();
  }
};

// Attach / detach on open
watch(
  () => props.open,
  (isOpen) => {
    if (isOpen) window.addEventListener("keydown", onGlobalKeyDown);
    else window.removeEventListener("keydown", onGlobalKeyDown);
  },
  { immediate: true }
);

// Cleanup on unmount
onBeforeUnmount(() => {
  window.removeEventListener("keydown", onGlobalKeyDown);
});

const handlePrintReceipt = () => {
  // Calculate totals using per-product discount and custom discount
  const subTotal = props.products.reduce((sum, product) => {
    // Use discounted price if available, else fallback
    let price = parseFloat(product.selling_price);
    if (product.discount_value && product.discount_type) {
      if (product.discount_type === 'rs') {
        price = Math.max(price - parseFloat(product.discount_value), 0);
      } else if (product.discount_type === 'percent') {
        price = Math.max(price - (price * parseFloat(product.discount_value) / 100), 0);
      }
    }
    return sum + price * product.quantity;
  }, 0);

  // Calculate total discount (sum of all per-product discounts)
  const totalDiscount = props.products.reduce((total, product) => {
    let discount = 0;
    const price = parseFloat(product.selling_price);
    if (product.discount_value && product.discount_type) {
      if (product.discount_type === 'rs') {
        discount = parseFloat(product.discount_value) * product.quantity;
      } else if (product.discount_type === 'percent') {
        discount = (price * parseFloat(product.discount_value) / 100) * product.quantity;
      }
    }
    return total + discount;
  }, 0);

  // Custom discount (fixed or percent)
  let customDiscount = Number(props.custom_discount || 0);
  if (props.custom_discount_type === 'percent') {
    customDiscount = (subTotal * customDiscount) / 100;
  }

  // Final total
  const total = subTotal - customDiscount;

  // Product rows with discount display
  const productRows = props.products
    .map((product, index) => {
      let originalPrice = parseFloat(product.selling_price);
      let discountedPrice = originalPrice;
      let discountLabel = '';
      if (product.discount_value && product.discount_type) {
        if (product.discount_type === 'rs') {
          discountedPrice = Math.max(originalPrice - parseFloat(product.discount_value), 0);
          discountLabel = `${product.discount_value} Rs OFF`;
        } else if (product.discount_type === 'percent') {
          discountedPrice = Math.max(originalPrice - (originalPrice * parseFloat(product.discount_value) / 100), 0);
          discountLabel = `${product.discount_value}% OFF`;
        }
      }
      return `
        <tr>
          <td colspan="3" style="padding: 4px 0; font-weight: bold;">
            ${index + 1}. ${product.name}
          </td>
        </tr>
        <tr style="border-bottom: 1px dashed #999;">
          <td></td>
          <td style="text-align: center; padding: 2px 0;">
            <span style="${discountedPrice < originalPrice ? 'text-decoration:line-through;color:#888;' : ''}">${originalPrice.toFixed(2)}</span>
            ${discountedPrice < originalPrice ? `<br/><span style='color:green;font-weight:bold;'>${discountedPrice.toFixed(2)}</span>` : ''}
            × ${product.quantity}
            ${discountLabel ? `<div style="font-weight: bold; font-size: 9px; background:black; color:white; text-align:center; margin-top:2px; border-radius:3px; display:inline-block; padding:0 4px;">${discountLabel}</div>` : ''}
          </td>
          <td style="text-align: right; padding: 2px 0;">
            ${(discountedPrice * product.quantity).toFixed(2)}
          </td>
        </tr>
      `;
    })
    .join("");

  const receiptHTML = `
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Receipt</title>
<style>
  @media print { body { margin:0; padding:0; -webkit-print-color-adjust: exact; } }
  body { background:#fff; font-size:12px; font-family: Arial, sans-serif; margin:0; padding:10px; color:#000; font-weight:600; }
  .section { margin-bottom:16px; padding-top:8px; border-top:1px solid #000; }
  .info-row { display:flex; justify-content:space-between; font-size:12px; margin-top:8px; }
  .info-row p { margin:0; font-weight:bold; }
  .info-row small { font-weight:normal; }
  table { width:100%; font-size:12px; border-collapse:collapse; margin-top:8px; }
  table th, table td { padding:6px 8px; }
  table th { text-align:left; }
  table td { text-align:right; }
  table td:first-child { text-align:left; }
  .totals { border-top:1px solid #000; padding-top:8px; font-size:12px; }
  .totals div { display:flex; justify-content:space-between; margin-bottom:8px; }
  .totals div:last-child { font-size:14px; font-weight:bold; }
  .footer { text-align:center; font-size:10px; margin-top:16px; }
  .header-line { border-bottom:1px solid #000; padding-bottom:10px; margin-bottom:10px; }
</style>
</head>
<body>
  <div class="receipt-container">
    <!-- Header -->
    <div class="header-line">
      <div style="display:flex; justify-content:space-between; align-items:flex-start;">
        <div style="flex-shrink:0; text-align:right;">
          <img src="/images/billlogo.png" alt="Company Logo" style="width:60px; height:60px; object-fit:contain;" />
        </div>
        <div style="text-align:right; flex-grow:1; padding-left:15px; color:#000;">
          ${
            companyInfo?.value?.name
              ? `<h1 style="margin:0; font-size:16px; font-weight:bold;">${companyInfo.value.name}</h1>`
              : ""
          }
          ${
            companyInfo?.value?.address
              ? `<p style="margin:2px 0; font-size:12px;">${companyInfo.value.address}</p>`
              : ""
          }
          ${
            (companyInfo?.value?.phone || companyInfo?.value?.phone2 || companyInfo?.value?.email)
              ? `<p style="margin:2px 0; font-size:12px;">
                   ${companyInfo.value.phone || ""}
                   ${companyInfo.value.phone2 ? " | " + companyInfo.value.phone2 : ""}
                   ${companyInfo.value.email ? " | " + companyInfo.value.email : ""}
                 </p>`
              : ""
          }
          ${
            companyInfo?.value?.website
              ? `<p style="margin:2px 0; font-size:12px;">${companyInfo.value.website}</p>`
              : ""
          }
        </div>
      </div>
    </div>

    <div class="info-row">
      <div>
        <p>Date & Time:</p>
        <small>${new Date().toLocaleDateString()} ${new Date().toLocaleTimeString()}</small>
      </div>
      <div>
        <p>Order No:</p>
        <small>${props.orderid}</small>
      </div>
    </div>

    <div class="info-row">
      <div>
        <p>Customer:</p>
        <small>${props.customer?.name || ""}</small>
      </div>
      <div>
        <p>Cashier:</p>
        <small>${props.cashier?.name || ""}</small>
      </div>
    </div>

    <div class="info-row">
      <p>Billing Type: <small>${props.isWholesale ? "Wholesale" : "Retail"}</small></p>
      ${props.paymentMethod === "online" ? `<p>Payment Method: <small>Cheque</small></p>` : ""}
      <p>Credit Bill: <small>${props.credit_bill ? "Yes" : "No"}</small></p>
    </div>

    <div class="section">
      <table>
        <thead>
          <tr style="border-bottom:1px solid black;">
            <th style="text-align:left; padding:4px;">Items</th>
            <th style="text-align:center; padding:4px;">Price × Qty</th>
            <th style="text-align:right; padding:4px;">Total</th>
          </tr>
        </thead>
        <tbody>
          ${productRows}
        </tbody>
      </table>
    </div>

    <div class="totals">
      ${Number(props.subTotal || 0)
        ? `<div><span>Sub Total</span><span>${(Number(props.subTotal || 0)).toFixed(2)} LKR</span></div>`
        : ""
      }
      ${Number(props.totalDiscount || 0)
        ? `<div><span>Discount</span><span>${(Number(props.totalDiscount || 0)).toFixed(2)} LKR</span></div>`
        : ""
      }
      ${Number(props.custom_discount || 0)
        ? `<div><span>Custom Discount</span><span>${
            (Number(props.custom_discount || 0)).toFixed(2)
          } ${
            props.custom_discount_type === "percent" ? "%" :
            (props.custom_discount_type === "fixed" ? "LKR" : "")
          }</span></div>`
        : ""
      }
      ${Number(props.total || 0)
        ? `<div><span>Total</span><span>${(Number(props.total || 0)).toFixed(2)} LKR</span></div>`
        : ""
      }
      ${Number(props.cash || 0)
        ? `<div><span>Cash</span><span>${(Number(props.cash || 0)).toFixed(2)} LKR</span></div>`
        : ""
      }
      ${Number(props.balance || 0)
        ? `<div><span>Balance</span><span>${(Number(props.balance || 0)).toFixed(2)} LKR</span></div>`
        : ""
      }
    </div>

    <div class="footer">
      <p>THANK YOU COME AGAIN</p>
      <p style="font-weight:bold;">Powered by JAAN Network Ltd.</p>
    </div>
  </div>
</body>
</html>
`;

  const printWindow = window.open("", "_blank");
  if (!printWindow) {
    alert("Failed to open print window. Please check your browser settings.");
    return;
  }

  printWindow.document.open();
  printWindow.document.write(receiptHTML);
  printWindow.document.close();

  printWindow.onload = () => {
    printWindow.focus();
    printWindow.print();
    printWindow.close();
  };
};
</script>
