<!-- resources/js/Pages/Expenses/Index.vue -->
<template>
  <Head title="Expenses" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 px-6 md:px-36">
    <!-- Header -->
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-10">
      <!-- Top bar: count + total amount -->
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">
            {{ expenses.length }}
          </span>
          <span class="text-xl">/ Total Expenses</span>
        </p>

        <div class="flex items-center gap-3">
          <span class="text-gray-600">Total Amount:</span>
          <span class="text-xl font-semibold">{{ formatAmount(sumAmount) }}</span>
        </div>
      </div>

      <!-- Title row -->
      <div class="flex w-full items-center justify-between">
        <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
          <Link href="/">
            <img src="/images/back-arrow.png" class="w-12 h-12" alt="Back" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Expenses</p>
        </div>

        <div class="flex justify-end w-full">
          <button
            @click="openCreate"
            :disabled="!isAdmin"
            :class="isAdmin
              ? 'md:px-12 py-3 px-4 md:text-2xl text-base font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl hover:bg-blue-700 transition'
              : 'md:px-12 py-3 px-4 md:text-2xl text-base font-bold tracking-wider text-white uppercase bg-blue-400 rounded-xl cursor-not-allowed'"
            :title="isAdmin ? 'Add a new expense' : 'You do not have permission to add expenses'"
          >
            <i class="md:pr-4 ri-add-circle-fill"></i> Add Expense
          </button>
        </div>
      </div>

      <!-- Table -->
      <template v-if="expenses && expenses.length">
        <div class="overflow-x-auto">
          <table
            id="ExpenseTable"
            class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
          >
            <thead>
              <tr
                class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700 text-left"
              >
                <th class="p-4 font-semibold tracking-wide uppercase">#</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Title</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Amount</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Expense Date</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Note</th>
                <th class="p-4 font-semibold tracking-wide uppercase">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(expense, index) in expenses" :key="expense.id" class="hover:bg-gray-100">
                <td class="px-6 py-3">{{ index + 1 }}</td>
                <td class="px-6 py-3">{{ expense.title }}</td>
                <td class="px-6 py-3">{{ formatAmount(expense.amount) }}</td>
                <td class="px-6 py-3">{{ formatDate(expense.expense_date) }}</td>
                <td class="px-6 py-3">
                  <span v-if="expense.note && expense.note.length">{{ expense.note }}</span>
                  <span v-else class="text-gray-400 italic">—</span>
                </td>
                <td class="px-6 py-3">
                  <div class="flex items-center gap-2">
                    <!-- Edit -->
                    <button
                      :disabled="!canEdit"
                      :class="canEdit
                        ? 'px-3 py-1 rounded-lg bg-green-600 text-white hover:bg-green-700 transition'
                        : 'px-3 py-1 rounded-lg bg-green-300 text-white cursor-not-allowed'"
                      :title="canEdit ? 'Edit' : 'No permission to edit'"
                      @click="onEdit(expense)"
                      aria-label="Edit expense"
                    >
                      Edit
                    </button>

                    <!-- Delete -->
                    <button
                      :disabled="!canDelete"
                      :class="canDelete
                        ? 'px-3 py-1 rounded-lg bg-red-600 text-white hover:bg-red-700 transition'
                        : 'px-3 py-1 rounded-lg bg-red-300 text-white cursor-not-allowed'"
                      :title="canDelete ? 'Delete' : 'No permission to delete'"
                      @click="onDelete(expense)"
                      aria-label="Delete expense"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </template>

      <template v-else>
        <div class="col-span-4 text-center">
          <p class="text-center text-red-500 text-[17px]">No Expenses Available</p>
        </div>
      </template>
    </div>
  </div>

  <!-- Modals -->
  <ExpenseCreateModal v-model:open="isCreateModalOpen" />
  <ExpenseUpdateModal
    v-model:open="isEditModalOpen"
    :selected-expense="selectedExpense"
  />
  <ExpenseDeleteModal
    v-model:open="isDeleteModalOpen"
    :selected-expense="selectedExpense"
  />

  <Footer />
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import Header from '@/Components/custom/Header.vue';
import Footer from '@/Components/custom/Footer.vue';
import Banner from '@/Components/Banner.vue';
import ExpenseCreateModal from '@/Components/custom/ExpenseCreateModal.vue';
import ExpenseUpdateModal from '@/Components/custom/ExpenseUpdateModal.vue';
import ExpenseDeleteModal from '@/Components/custom/ExpenseDeleteModal.vue';
import { HasRole } from '@/Utils/Permissions';

const props = defineProps({
  expenses: { type: Array, default: () => [] },
  totalExpenses: { type: Number, default: 0 },
  sumAmount: { type: Number, default: 0 },
});

/** Roles */
const isAdmin = computed(() => HasRole(['Admin']));
const canEdit = computed(() => HasRole(['Admin']));               // Edit: Admin only
const canDelete = computed(() => HasRole(['Admin', 'Manager']));  // Delete: Admin or Manager

/** Modals/state */
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedExpense = ref(null);

/** Handlers */
const openCreate = () => {
  if (isAdmin.value) isCreateModalOpen.value = true;
};

const openEditModal = (expense) => {
  selectedExpense.value = expense;
  isEditModalOpen.value = true;
};

const openDeleteModal = (expense) => {
  selectedExpense.value = expense;
  isDeleteModalOpen.value = true;
};

// Guarded click handlers (prevents bypass even if someone removes disabled in DOM)
const onEdit = (expense) => {
  if (!canEdit.value) return;
  openEditModal(expense);
};

const onDelete = (expense) => {
  if (!canDelete.value) return;
  openDeleteModal(expense);
};

/** Formatters */
const formatDate = (d) => {
  if (!d) return '—';
  const date = new Date(d);
  if (isNaN(date)) return d;
  return date.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: '2-digit' });
};

const formatAmount = (a) => {
  if (a === null || a === undefined || a === '') return '0.00';
  const n = Number(a);
  if (isNaN(n)) return a;
  return n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
};

/** DataTables */
let dt = null;
onMounted(() => {
  if (typeof window !== 'undefined' && window.$ && typeof window.$.fn?.dataTable !== 'undefined') {
    dt = window.$('#ExpenseTable').DataTable({
      dom: 'Bfrtip',
      pageLength: 10,
      buttons: [],
      columnDefs: [
        { targets: [4], searchable: false },
        { targets: [5], orderable: false },
      ],
      initComplete: function () {
        const searchInput = window.$('div.dataTables_filter input');
        searchInput.attr('placeholder', 'Search ...');
      },
      language: { search: '' },
    });
  }
});

onBeforeUnmount(() => {
  if (dt) {
    dt.destroy(true);
    dt = null;
  }
});
</script>

<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* Style the filter container */
#ExpenseTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
  float: left; /* mirrors your previous layout */
}

/* Style the label and input field inside the filter */
#ExpenseTable_filter label {
  font-size: 17px;
  display: flex;
  align-items: center;
}

/* Style the input field */
#ExpenseTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.2s ease;
}

#ExpenseTable_filter input[type="search"]:focus {
  outline: none;
  border: 1px solid #4b5563;
  box-shadow: none;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>
