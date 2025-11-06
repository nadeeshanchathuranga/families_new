<!-- resources/js/Pages/Orders/OrderHistory.vue -->
<template>
  <Head title="Order History" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 px-4 md:px-36">
    <Header />

    <!-- Summary Bar -->
    <div class="w-full md:w-5/6 py-6 space-y-8">
      <div class="flex items-center justify-between">
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{ totalhistoryTransactions }}</span>
          <span class="text-xl">/ Total History Transition</span>
        </p>

        <Link href="/" class="hidden md:flex items-center gap-3 group">
          <img src="/images/back-arrow.png" class="w-10 h-10 group-hover:-translate-x-1 transition" />
          <span class="text-black text-lg font-semibold group-hover:underline">Back</span>
        </Link>
      </div>

      <div class="flex w-full md:hidden">
        <Link href="/" class="flex items-center w-full h-14 gap-3">
          <img src="/images/back-arrow.png" class="w-12 h-12" />
          <p class="text-3xl font-bold tracking-wide text-black uppercase">Order History</p>
        </Link>
      </div>

      <!-- Table -->
      <div v-if="allhistoryTransactions && allhistoryTransactions.length" class="overflow-x-auto">
        <table id="TransitionTable" class="w-full text-gray-700 bg-white border border-gray-300 rounded-2xl shadow-md table-auto overflow-hidden">
          <thead>
            <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[12px] text-white">
              <th class="p-4 font-semibold tracking-wide text-left uppercase">#</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Order ID</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Totals</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Payment</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Bill Type</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Credit Bill</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Sale Date</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Status</th>
              <th class="p-4 font-semibold tracking-wide text-left uppercase">Action</th>
            </tr>
          </thead>
          <tbody class="text-[13px] font-normal">
            <tr
              v-for="(history, index) in allhistoryTransactions"
              :key="history.id"
              class="transition duration-200 ease-in-out hover:bg-gray-100"
            >
              <td class="px-6 py-3">{{ index + 1 }}</td>

              <!-- Order ID (+ Return Bill badge) -->
              <td class="p-4 font-bold border-gray-200">
                <div class="flex items-center gap-2">
                  <span :class="Number(history.is_return_bill) === 1 ? 'text-red-600' : 'text-gray-900'">
                    {{ history.order_id || 'N/A' }}
                  </span>
                  <span
                    v-if="Number(history.is_return_bill) === 1"
                    class="px-2 py-0.5 text-xs font-semibold bg-red-100 text-red-600 rounded"
                  >
                    Return Bill
                  </span>
                </div>
              </td>

              <!-- Totals -->
              <td class="p-4 font-semibold border-gray-200 text-sm leading-5">
                Base Total: {{ money(history.total_amount) }} LKR<br>
                Custom discount:
                {{ history.custom_discount_type === 'percent'
                  ? money(history.custom_discount) + ' %'
                  : money(history.custom_discount) + ' LKR' }}
                <br />
                Final:
                <span class="inline-block px-2 py-1 text-xs font-bold text-white bg-green-600 rounded">
                  {{ money(finalTotalForHistory(history)) }} LKR
                </span>
              </td>

              <!-- Payment details -->
              <td class="p-4 font-bold border-gray-200">
                <template v-if="history.payment_method === 'Online' && history.cheque">
                  <div>Cheque #: {{ history.cheque.cheque_number }}</div>
                  <div>Bank: {{ history.cheque.bank_name }}</div>
                  <div>Date: {{ history.cheque.cheque_date }}</div>
                  <div>Amount: {{ money(history.cheque.amount) }} LKR</div>
                  <div>
                    Status:
                    <span :class="history.cheque.status === 'pending' ? 'text-yellow-600' : 'text-green-600'">
                      {{ history.cheque.status }}
                    </span>
                  </div>
                </template>
                <template v-else>
                  {{ history.payment_method || 'N/A' }}
                </template>
              </td>

              <!-- Bill type -->
              <td class="p-4 font-bold border-gray-200">
                {{ Number(history.is_whole) > 0 ? 'Wholesale' : 'Retail' }}
              </td>

              <!-- Credit bill -->
              <td class="p-4 font-bold border-gray-200">
                <span :class="Number(history.credit_bill) === 0 ? 'text-green-700' : 'text-amber-600'">
                  {{ Number(history.credit_bill) === 0 ? 'Completed' : 'Not Completed' }}
                </span>
                <button
                  v-if="Number(history.credit_bill) === 1"
                  @click="openPaymentModal(history)"
                  class="ml-2 px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700"
                >
                  Payment
                </button>
              </td>

              <!-- Sale date -->
              <td class="p-4 font-bold border-gray-200">
                 {{ formatDateOnly(history.sale_date || history.created_at) }}
              </td>

              <!-- Guide status -->
              <td class="p-4 font-bold border-gray-200">
                <span v-if="Number(history.guide_pending) === 1" class="text-yellow-600">Pending</span>
                <span v-else class="text-green-600">Completed</span>

                <button
                  v-if="Number(history.guide_pending) === 1"
                  @click="markGuideCompleted(history.id)"
                  class="ml-3 px-3 py-1 text-xs bg-gray-700 text-white rounded hover:bg-gray-800"
                >
                  Mark Completed
                </button>
              </td>

              <!-- Actions -->
              <td class="p-4 font-bold border-gray-200">
                <div class="flex flex-wrap gap-2">
                  <button
                    @click="printReceipt(history)"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg hover:bg-orange-600"
                  >
                    Print
                  </button>

                  <button
                    @click="deleteReceipt(history.order_id)"
                    class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                  >
                    Delete
                  </button>

                  <button
                    v-if="history.payment_method === 'Online' && history.cheque && history.cheque.status === 'pending'"
                    @click="markChequeAsPaid(history.cheque.id)"
                    class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
                  >
                    Mark Cheque Paid
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="w-full bg-white rounded-2xl shadow p-10 text-center">
        <p class="text-red-500 text-lg">No Stock Transition Available</p>
      </div>
    </div>
  </div>

  <Footer />

  <!-- Credit Payment Modal -->
  <TransitionRoot as="template" :show="showPaymentModal">
    <Dialog class="relative z-50" :open="showPaymentModal" @close="closePaymentModal">
      <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0" enter-to="opacity-100"
        leave="ease-in duration-150" leave-from="opacity-100" leave-to="opacity-0">
        <div class="fixed inset-0 bg-black/40" />
      </TransitionChild>

      <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <TransitionChild as="template" enter="ease-out duration-200" enter-from="opacity-0 scale-95"
          enter-to="opacity-100 scale-100" leave="ease-in duration-150" leave-from="opacity-100 scale-100"
          leave-to="opacity-0 scale-95">
          <DialogPanel class="bg-white p-6 rounded-2xl w-full max-w-md shadow-xl">
            <DialogTitle class="text-xl font-bold text-gray-900">Complete Credit Payment</DialogTitle>
            <div class="mt-4 space-y-2 text-sm text-gray-800">
              <div><b>ID:</b> {{ selectedOrder?.id }}</div>
              <div><b>Order ID:</b> {{ selectedOrder?.order_id }}</div>
              <div>
                <b>Final Total:</b> {{ money(selectedOrder ? finalTotalForHistory(selectedOrder) : 0) }} LKR
              </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
              <button @click="closePaymentModal" class="px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">
                Cancel
              </button>
              <button @click="submitPayment" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                Pay Now
              </button>
            </div>
          </DialogPanel>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick, watch } from "vue";
import { Head, Link, router, useForm } from "@inertiajs/vue3";
import axios from "axios";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import {
  Dialog,
  DialogPanel,
  DialogTitle,
  TransitionChild,
  TransitionRoot,
} from "@headlessui/vue";

// ---------- props ----------
const props = defineProps({
  allhistoryTransactions: { type: Array, default: () => [] },
  totalhistoryTransactions: { type: Number, default: 0 },
  companyInfo: { type: Array, default: () => [] }, // [ { name, address, phone, phone2, email, website } ]
});

const form = useForm({});








const formatDateOnly = (v) => {
  if (!v) return "N/A";

  const toDate = (x) => {
    if (typeof x === "string" && /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/.test(x)) {
      return new Date(x.replace(" ", "T")); // treat as local
    }
    return new Date(x);
  };

  const d = toDate(v);
  if (isNaN(d)) return "N/A";

  return new Intl.DateTimeFormat("en-GB", {
    timeZone: "Asia/Colombo",
    day: "2-digit",
    month: "short",
    year: "numeric",
  }).format(d);
};










// ---------- helpers ----------
const num = (v) => Number(v || 0);
const money = (v) => num(v).toFixed(2);
const isApplied = (v) => v === true || v === 1 || v === "1";

// compute effective unit price for a sale item
const computePrice = (item) => {
  const selling = num(item.selling_price ?? item.unit_price ?? 0);
  const pct = num(item.discount ?? item.discount_percent ?? 0);
  const discountedPrice = num(item.discounted_price);

  if (isApplied(item.apply_discount) && discountedPrice > 0) return discountedPrice;
  if (isApplied(item.apply_discount) && pct > 0) return selling * (1 - pct / 100);
  return selling;
};

// final total for a history row
const finalTotalForHistory = (h) => {
  const gross = num(h.total_amount);
  const custom = num(h.custom_discount);
  const customLkr = h.custom_discount_type === "percent" ? (gross * custom) / 100 : custom;
  return gross - customLkr;
};

// ---------- preload logo as base64 for printing ----------
const logoBase64 = ref(""); // data:image/png;base64,...
async function loadLogoAsBase64(path = "/images/billlogo.png") {
  try {
    const res = await fetch(path, { cache: "no-store" });
    if (!res.ok) throw new Error("Logo not found: " + path);
    const blob = await res.blob();
    const reader = new FileReader();
    const dataUrl = await new Promise((resolve, reject) => {
      reader.onerror = reject;
      reader.onload = () => resolve(reader.result);
      reader.readAsDataURL(blob);
    });
    logoBase64.value = String(dataUrl); // "data:image/png;base64,...."
  } catch (e) {
    console.error("Logo load failed:", e);
    logoBase64.value = ""; // fallback: no logo
  }
}

// ---------- DataTables init / destroy ----------
let dtInstance = null;
const initTable = async () => {
  await nextTick();
  const $ = window.$ || window.jQuery;
  if (!$ || !$.fn || !$.fn.DataTable) return;

  if ($.fn.DataTable.isDataTable("#TransitionTable")) {
    $("#TransitionTable").DataTable().destroy();
  }

  dtInstance = $("#TransitionTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    responsive: true,
    buttons: [],
    columnDefs: [{ targets: [2, 8], orderable: false }],
    language: { search: "" },
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search orders …");
    },
  });
};

onMounted(async () => {
  await Promise.all([initTable(), loadLogoAsBase64("/images/billlogo.png")]);
});
watch(() => props.allhistoryTransactions, initTable, { deep: true });

onBeforeUnmount(() => {
  const $ = window.$ || window.jQuery;
  if ($ && $.fn && $.fn.DataTable && $.fn.DataTable.isDataTable("#TransitionTable")) {
    $("#TransitionTable").DataTable().destroy();
  }
});

// ---------- actions ----------
const deleteReceipt = (orderId) => {
  if (!orderId) return alert("Invalid Order ID");
  if (confirm("Delete this record? This action cannot be undone.")) {
    router.post(route("transactions.delete"), { order_id: orderId }, {
      onError: (error) => {
        alert("Error: " + (error?.message || "Something went wrong."));
      },
      onSuccess: () => {
        router.reload({ only: ["allhistoryTransactions", "totalhistoryTransactions"] });
      },
    });
  }
};

const markChequeAsPaid = (chequeId) => {
  if (!chequeId) return;
  if (confirm("Mark this cheque as paid?")) {
    router.post(route("cheque.markAsPaid"), { cheque_id: chequeId }, {
      onSuccess: () => {
        alert("Cheque marked as paid.");
        router.reload({ only: ["allhistoryTransactions"] });
      },
      onError: () => {
        alert("Failed to update cheque status.");
      },
    });
  }
};

const markGuideCompleted = async (saleId) => {
  try {
    await axios.put(`/sales/${saleId}/mark-guide-completed`);
    router.reload({ only: ["allhistoryTransactions"] });
  } catch (e) {
    console.error(e);
    alert("Failed to update guide status.");
  }
};

// ---------- Receipt printing (with logo) ----------
const printReceipt = (history) => {
  const company = props.companyInfo?.[0] || {};
  const items = history.sale_items || [];

  const productRows = (items).map((it) => {
    const name = it?.product?.name || it?.name || "N/A";
    const qty = Number(it.quantity || 0);
    const unit = computePrice(it);
    const pct = Number(it.discount ?? it.discount_percent ?? 0);
    const hasDisc = (it.apply_discount === true || it.apply_discount === 1 || it.apply_discount === "1") && pct > 0;

    return `
      <tr>
        <td colspan="3" style="padding:4px 0; font-weight:600;">${name}</td>
      </tr>
      <tr style="border-bottom:1px dashed #999;">
        <td></td>
        <td style="text-align:center; padding:2px 0;">
          ${unit.toFixed(2)} × ${qty}
          ${hasDisc ? `
            <div style="font-weight:bold; font-size:9px; background:#000; color:#fff; text-align:center; margin-top:2px; border-radius:3px; display:inline-block; padding:0 4px;">
              ${pct}% OFF
            </div>` : ``}
        </td>
        <td style="text-align:right; padding:2px 0;">${(unit * qty).toFixed(2)}</td>
      </tr>
    `;
  }).join("");

  const gross = num(history.total_amount);
  const custom = num(history.custom_discount);
  const customLkr = history.custom_discount_type === "percent" ? (gross * custom) / 100 : custom;
  const finalTotal = gross - customLkr;
  const cash = num(history.cash);
  const balance = cash - finalTotal;

  const headerRight = `
    ${company.name ? `<h1 style="margin:0;font-size:16px;font-weight:bold;">${company.name}</h1>` : ""}
    ${company.address ? `<p style="margin:2px 0;font-size:12px;">${company.address}</p>` : ""}
    ${(company.phone || company.phone2 || company.email)
      ? `<p style="margin:2px 0;font-size:12px;">
          ${company.phone || ""}${company.phone2 ? " | " + company.phone2 : ""}${company.email ? " | " + company.email : ""}
        </p>` : ""}
    ${company.website ? `<p style="margin:2px 0;font-size:12px;">${company.website}</p>` : ""}
  `;

  const logoImg = logoBase64.value
    ? `<img src="${logoBase64.value}" style="width:60px;height:60px;object-fit:contain;" />`
    : ``; // if logo missing, skip img to avoid print break

  const receipt = `
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>Receipt</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <style>
    @media print { body { margin:0; padding:0; -webkit-print-color-adjust: exact; print-color-adjust: exact; } }
    body { font-family: Arial, sans-serif; font-size: 12px; color:#000; padding: 10px; font-weight:600;}
    .header { border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 10px; }
    .flex { display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
    .section { margin-top: 10px; border-top: 1px solid #000; padding-top: 8px; }
    .row { display:flex; justify-content:space-between; gap:8px; margin:6px 0; }
    table { width:100%; border-collapse:collapse; margin-top:6px; }
    th, td { padding:6px 4px; border-bottom:1px dashed #bbb; }
    th { text-align:left; border-bottom:1px solid #000; }
    .totals .row { margin:4px 0; }
    .totals .row.total { font-weight:bold; font-size: 14px; }
    .footer { text-align:center; margin-top: 12px; font-size: 10px; }
  </style>
</head>
<body>
  <div class="header">
    <div class="flex">
      <div style="flex:0 0 auto;">${logoImg}</div>
      <div style="flex:1 1 auto;text-align:right;">${headerRight}</div>
    </div>
  </div>

  <div class="row">
    <div><b>Date & Time :</b> ${new Date(history.created_at || Date.now()).toLocaleString()}</div>
    <div><b>Order No:</b> ${history.order_id || ""}</div>
  </div>
  <div class="row">
    <div><b>Customer:</b> ${history?.customer?.name || ""}</div>
    <div><b>Cashier:</b> ${history?.user?.name || ""}</div>
  </div>
  <div class="row">
    <div><b>Billing:</b> ${Number(history.is_whole) > 0 ? "Wholesale" : "Retail"}</div>
    <div><b>Credit Bill:</b> ${Number(history.credit_bill) ? "Yes" : "No"}</div>
  </div>

  <div class="section">
    <table>
      <thead>
        <tr style="border-bottom: 1px solid black;">
          <th style="text-align: left; padding: 4px;">Items</th>
          <th style="text-align: center; padding: 4px;">Price × Qty</th>
          <th style="text-align: right; padding: 4px;">Total</th>
        </tr>
      </thead>
      <tbody>
        ${productRows}
      </tbody>
    </table>
  </div>

  <div class="section totals">
    <div class="row"><span>Sub Total</span><span>${money(gross)} LKR</span></div>
    <div class="row"><span>Custom Discount</span><span>${history.custom_discount_type === "percent" ? money(custom) + " %" : money(custom) + " LKR"}</span></div>
    <div class="row total"><span>Total</span><span>${money(finalTotal)} LKR</span></div>
    <div class="row"><span>Cash</span><span>${money(cash)} LKR</span></div>
    <div class="row"><span>Balance</span><span>${money(balance)} LKR</span></div>
  </div>

  <div class="footer">
         <p>THANK YOU COME AGAIN</p>
    <p><b>Powered by JAAN Network (Pvt) Ltd.</b></p>
  </div>
</body>
</html>
  `;

  const w = window.open("", "_blank");
  if (!w) {
    alert("Popup blocked. Please allow popups to print.");
    return;
  }
  w.document.open();
  w.document.write(receipt);
  w.document.close();

  // wait for images to load before printing (important for the logo)
  const waitAndPrint = () => {
    const img = w.document.querySelector("img");
    if (!img) { w.focus(); w.print(); w.close(); return; }
    if (img.complete) { w.focus(); w.print(); w.close(); return; }
    img.onload = () => { w.focus(); w.print(); w.close(); };
    img.onerror = () => { w.focus(); w.print(); w.close(); };
  };
  // in case DOM not ready yet
  if (w.document.readyState === "complete") {
    waitAndPrint();
  } else {
    w.addEventListener("load", waitAndPrint);
  }
};

// ---------- credit payment modal ----------
const showPaymentModal = ref(false);
const selectedOrder = ref(null);

const openPaymentModal = (order) => {
  selectedOrder.value = order;
  showPaymentModal.value = true;
};
const closePaymentModal = () => {
  showPaymentModal.value = false;
  selectedOrder.value = null;
};
const submitPayment = () => {
  if (!selectedOrder.value) return;
  router.post(
    route("transactions.markAsPaid"),
    {
      id: selectedOrder.value.id,
      order_id: selectedOrder.value.order_id,
      amount: finalTotalForHistory(selectedOrder.value),
    },
    {
      onSuccess: () => {
        closePaymentModal();
        router.reload({ only: ["allhistoryTransactions"] });
      },
      onError: () => alert("Payment failed."),
    }
  );
};
</script>

<style scoped>
/* DataTables pagination centered */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 18px;
}

/* filter container aligned right */
#TransitionTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
  float: left;
}

#TransitionTable_filter label {
  font-size: 15px;
  color: #000;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* search input */
#TransitionTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 8px;
  background: #fff;
  outline: none;
  transition: all 0.25s ease;
}
#TransitionTable_filter input[type="search"]:focus {
  border: 1px solid #4b5563;
  box-shadow: none;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}

/* table tweaks */
#TransitionTable thead th {
  white-space: nowrap;
}
#TransitionTable tbody td {
  vertical-align: top;
}
</style>
