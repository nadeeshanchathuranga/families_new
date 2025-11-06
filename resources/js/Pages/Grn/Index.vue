<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* Style the filter container */
#ColorTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px; /* Add spacing below the filter */
}

/* Style the label and input field inside the filter */
#ColorTable_filter label {
  font-size: 17px;
  color: #000000; /* Match text color of the table header */
  display: flex;
  align-items: center;
}

/* Style the input field */
#ColorTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.5s ease;
}
#ColorTable_filter input[type="search"]:focus {
  outline: none; /* Removes the default outline */
  border: 1px solid #4b5563;
  box-shadow: none; /* Removes any focus box-shadow */
}

#ColorTable_filter {
  float: left;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
    <Head title="Grns"/>
  <Banner />
  <div
    class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
  >
    <!-- Include the Header -->
    <Header />
    <div class="w-full md:w-5/6 py-12 space-y-24">
      <div class="flex items-center justify-between">
        <div class="flex items-center justify-center space-x-4"></div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">
            {{ allgrns.length }}
          </span>
          <span class="text-xl">/ Total Grns</span>
        </p>
      </div>
      <div class="flex w-full">
        <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
              <Link href="/">
            <img src="/images/back-arrow.png" class="w-14 h-14" />
          </Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">
            Grns
          </p>
        </div>
        <div class="flex justify-end w-full">



<p
  @click="() => { if (HasRole(['Admin'])) { isCreateModalOpen = true; } }"
  :class="HasRole(['Admin'])
            ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl'
            : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'"
  :title="HasRole(['Admin'])
            ? ''
            : 'You do not have permission to add more Color'"
>
  <i class="md:pr-4 ri-add-circle-fill"></i> Add More Grns
</p>




        </div>
      </div>
      <template v-if="allgrns && allgrns.length > 0">
        <div class="overflow-x-auto">

            <table
          id="ColorTable"
          class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
          >
          <thead>
            <tr
                class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700"
              >
              <th class="p-4 font-semibold tracking-wide   uppercase">Grn ID</th>
              <th class="p-4 font-semibold tracking-wide   uppercase">Grn Number</th>
              <th class="p-4 font-semibold tracking-wide   uppercase">Products Name</th>
              <th class="p-4 font-semibold tracking-wide   uppercase">Quantity</th>
              <th class="p-4 font-semibold tracking-wide  uppercase">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(grn, index) in allgrns"
              :key="grn.id"
              class="hover:bg-gray-200"
            >
              <td class="px-6 py-3 text- first-letter:">{{ index + 1 }}</td>
              <td class="px-6 py-3">{{ grn.grn_number }}</td>
              <td class="px-6 py-3">
                <ul>
                  <li v-for="product in grn.products" :key="product.id">
                    {{ product.name }}
                  </li>
                </ul>
              </td>

              <td class="px-6 py-3">
                <ul>
                  <li v-for="product in grn.products" :key="product.id">
                    {{ product.total_quantity }}
                  </li>
                </ul>
              </td>
              <td>







<!-- Edit Button -->
<button
  :class="HasRole(['Admin'])
            ? 'px-4 py-2 bg-green-500 text-white rounded-lg'
            : 'px-4 py-2 bg-green-400 text-white rounded-lg cursor-not-allowed'"
  :title="HasRole(['Admin'])
            ? ''
            : 'You do not have permission to edit'"
  :disabled="!HasRole(['Admin'])"
  @click="() => { if (HasRole(['Admin'])) openEditModal(grn); }"
>
  Edit
</button>

<!-- Delete Button -->
<button
  :class="HasRole(['Admin'])
            ? 'px-4 py-2 bg-red-500 text-white rounded-lg ml-2'
            : 'px-4 py-2 bg-red-400 text-white rounded-lg cursor-not-allowed ml-2'"
  :title="HasRole(['Admin'])
            ? ''
            : 'You do not have permission to delete'"
  :disabled="!HasRole(['Admin'])"
  @click="() => { if (HasRole(['Admin'])) openDeleteModal(grn); }"
>
  Delete
</button>





              </td>
            </tr>
          </tbody>
        </table>

        </div>
      </template>
      <template v-else>
        <div class="col-span-4 text-center text-blue-500">
          <p class="text-center text-red-500 text-[17px]">
            No grns available
          </p>
        </div>
      </template>
    </div>
  </div>
  <!-- Modal Components -->
  <GrnCreateModel
    :grns="allGrns"
    v-model:open="isCreateModalOpen"
  />


   <GrnUpdateModel
    :grns="allGrns"
    v-model:open="isEditModalOpen"
    :selected-grn="selectedGrn"
  />

  <GrnDeleteModel
    :grns="allGrns"
    :selected-grn="selectedGrn"
    v-model:open="isDeleteModalOpen"
  />

  <Footer />
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { Head } from '@inertiajs/vue3';
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import GrnCreateModel from "@/Components/custom/GrnCreateModel.vue";
import GrnDeleteModel from "@/Components/custom/GrnDeleteModel.vue";
import GrnUpdateModel from "@/Components/custom/GrnUpdateModel.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

defineProps({
  allgrns: Array,
  totalGrns: Number,
});



const openEditModal = (grn) => {
  console.log("Opening edit modal for Colors:", grn);
  selectedGrn.value = grn;
  isEditModalOpen.value = true;
};

const openDeleteModal = (grn) => {
    selectedGrn.value = grn;
  isDeleteModalOpen.value = true;
};

const form = useForm({});

const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const isDeleteModalOpen = ref(false);
const selectedGrn = ref(null);
 




document.addEventListener("DOMContentLoaded", function () {
  const tableId = "#ColorTable";

  if ($.fn.DataTable.isDataTable(tableId)) {
    $(tableId).DataTable().clear().destroy();
  }

  const table = $(tableId).DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    ordering: true,
    orderMulti: false,
    order: [[0, "desc"]],
    buttons: [],

    columnDefs: [
      { targets: 0, type: "num" },
      { targets: 2, searchable: false },
    ],
    // Performance & consistency
    autoWidth: false,
    deferRender: true,
    language: { search: "" },
    initComplete: function () {
      const searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.on("keypress", function (e) {
        if (e.which === 13) table.search(this.value).draw();
      });
      // Re-apply initial sort explicitly (covers some plugin edge cases)
      table.order([0, "desc"]).draw();
    },
  });
});











</script>

