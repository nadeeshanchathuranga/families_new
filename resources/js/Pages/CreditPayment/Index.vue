<template>

  <Head title="Credit Payments" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <!-- Flash Messages -->
    <div class="w-full md:w-5/6 space-y-2">
      <div v-if="successMessage" class="p-3 text-green-800 bg-green-200 rounded">
        {{ successMessage }}
      </div>
      <div v-if="errorMessage" class="p-3 text-red-800 bg-red-200 rounded">
        {{ errorMessage }}
      </div>
    </div>

    <div class="w-full md:w-5/6 py-12 space-y-24">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{ allPayments?.length || 0 }}</span>
          <span class="text-xl">/ Total Credit Payments</span>
        </p>
      </div>

      <!-- Add Payment Button -->
      <div class="flex w-full justify-between items-center">
        <div class="flex items-center w-full h-16 space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Credit Payments</p>
        </div>
        <button @click="openCreditPaymentModal"
          :class="HasRole(['Admin', 'Cashier'])
            ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl'
            : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'"
          :title="HasRole(['Admin', 'Cashier']) ? '' : 'No permission'" :disabled="!HasRole(['Admin', 'Cashier'])">
          <i class="md:pr-4 ri-add-circle-fill"></i> Add Credit Payment
        </button>
      </div>

      <!-- Payments Table -->
      <div v-if="allPayments && allPayments.length" class="overflow-x-auto w-full">
        <table id="CreditPaymentsTable"
          class="min-w-[900px] w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <thead>
            <tr
              class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700">
              <th class="p-3 md:p-4 font-semibold uppercase text-left">#</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Customer</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Total Amount</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Pending Amount</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Paid Amount</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Status</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Latest Payment Date</th>
              <th class="p-3 md:p-4 font-semibold uppercase text-left">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(payment, index) in allPayments" :key="payment.sale_id" class="hover:bg-gray-200">
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ index + 1 }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ payment.customer_name }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ payment.total_amount }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ payment.pending_payment }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ payment.paid_amount }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ payment.status }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">{{ payment.latest_payment_date }}</td>
              <td class="px-2 md:px-6 py-2 md:py-3 whitespace-nowrap">
                <button class="px-4 py-2 text-sm text-white bg-green-600 rounded hover:bg-green-700"
                  @click="openViewModal(payment)">
                  View
                </button>
              </td>
            </tr>
          </tbody>
        </table>

      </div>

      <p v-else class="text-center text-red-500 text-[17px]">No credit payments available</p>
    </div>
  </div>

  <!-- View Modal -->
  <div v-if="isViewModalOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-lg p-6 relative">
      <button class="absolute top-3 right-3 text-gray-500 hover:text-black" @click="isViewModalOpen = false">✕</button>

      <h2 class="text-2xl font-bold mb-4">Credit Payment Details</h2>

      <p><strong>Order ID:</strong> {{ selectedPayment.order_id }}</p>
      <p><strong>Customer:</strong> {{ selectedPayment.customer_name }}</p>
      <p><strong>Total Amount:</strong> {{ selectedPayment.total_amount }}</p>
      <p><strong>Pending Payment:</strong> {{ selectedPayment.pending_payment }}</p>
      <p><strong>Paid Amount:</strong> {{ selectedPayment.paid_amount }}</p>
      <p><strong>Status:</strong> {{ selectedPayment.status }}</p>
      <p><strong>Latest Payment Date:</strong> {{ selectedPayment.latest_payment_date }}</p>

      <div class="mt-6">
        <h3 class="text-xl font-semibold mb-2">Partial Payments</h3>
        <table class="w-full border border-gray-300 rounded">
          <thead>
            <tr class="bg-gray-200 text-gray-800">
              <th class="p-2 text-left">Amount</th>
              <th class="p-2 text-left">Date</th>
              <th class="p-2 text-left">Description</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(part, idx) in selectedPayment.part_payments" :key="idx" class="border-t">
              <td class="p-2">{{ part.part_payment }}</td>
              <td class="p-2">{{ part.date }}</td>
              <td class="p-2">{{ part.description || '-' }}</td>
            </tr>

            <tr v-if="!selectedPayment.part_payments || !selectedPayment.part_payments.length">
              <td colspan="3" class="p-2 text-center text-gray-500">No partial payments</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-4 flex justify-end">
        <button class="px-4 py-2 text-sm text-white bg-blue-600 rounded hover:bg-blue-700"
          @click="downloadPdf(selectedPayment)">
          Download PDF
        </button>
      </div>

    </div>
  </div>

  <CreditPaymentCreateModal :open="isCreateModalOpen" :customers="customers" :sales="sales"
    :credit-payments="allPayments" @update:open="isCreateModalOpen = $event" />
  <Footer />
</template>

<script setup>
import { ref, watch, onMounted } from "vue";
import { Link, Head, usePage } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import CreditPaymentCreateModal from "@/Components/custom/CreditPaymentCreateModal.vue";
import { HasRole } from "@/Utils/Permissions";
import jsPDF from "jspdf";
import autoTable from "jspdf-autotable";

// Props from backend
defineProps({
  allPayments: { type: Array, default: () => [] },
  customers: { type: Array, default: () => [] },
  sales: { type: Array, default: () => [] },
});

// Modals
const isCreateModalOpen = ref(false);
const openCreditPaymentModal = () => {
  if (HasRole(["Admin", "Cashier"])) isCreateModalOpen.value = true;
};

const isViewModalOpen = ref(false);
const selectedPayment = ref({});
const openViewModal = (payment) => {
  selectedPayment.value = payment;
  isViewModalOpen.value = true;
};

// Flash messages
const { props } = usePage();
const successMessage = ref(props.flash?.success || "");
const errorMessage = ref(props.flash?.error || "");

// Auto-clear messages
watch([successMessage, errorMessage], ([success, error]) => {
  if (success || error) {
    setTimeout(() => {
      successMessage.value = "";
      errorMessage.value = "";
    }, 5000);
  }
});

// Initialize DataTable with left search box
onMounted(() => {
  if (window.$ && $("#CreditPaymentsTable").length) {
    $("#CreditPaymentsTable").DataTable({
      dom: '<"flex justify-start mb-4"f>rtip',
      pageLength: 10,
      columnDefs: [{ targets: -1, searchable: false }],
      initComplete: function () {
        $("div.flex input").attr("placeholder", "Search payments...").addClass("px-3 py-2 border rounded");
      },
      language: { search: "" },
    });
  }
});

// Download PDF function
const downloadPdf = (payment) => {
  if (!payment) return;

  const doc = new jsPDF();

  doc.setFontSize(18);
  doc.text("Credit Payment Details", 14, 20);

  doc.setFontSize(12);
  doc.text(`Order ID: ${payment.order_id}`, 14, 30);
  doc.text(`Customer: ${payment.customer_name}`, 14, 38);
  doc.text(`Total Amount: ${payment.total_amount}`, 14, 46);
  doc.text(`Paid Amount: ${payment.paid_amount}`, 14, 54);
  doc.text(`Status: ${payment.status}`, 14, 62);
  doc.text(`Latest Payment Date: ${payment.latest_payment_date}`, 14, 70);

  if (payment.part_payments && payment.part_payments.length) {
    const tableColumn = ["Amount", "Date", "Description"];
    const tableRows = payment.part_payments.map(part => [
      part.part_payment,
      part.date,
      part.description || "-"
    ]);

    autoTable(doc, {
      head: [tableColumn],
      body: tableRows,
      startY: 80,
      theme: "grid",
      headStyles: { fillColor: [59, 130, 246] },
    });
  } else {
    doc.text("No partial payments available.", 14, 80);
  }

  doc.save(`CreditPayment_${payment.order_id}.pdf`);
};
</script>
