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
    <Head title="Bulk Upload"/>
    <Banner />
    <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
        <!-- Include the Header -->
        <Header />
        <div class="w-full md:w-5/6 py-12 space-y-24">
        <div class="flex items-center justify-between">
            <div class="flex items-center justify-center space-x-4"></div>

        </div>
        <div class="flex w-full">
            <div class="flex items-center w-full h-16 space-x-4 rounded-2xl">
                <Link href="/">
                <img src="/images/back-arrow.png" class="w-14 h-14" />
            </Link>
            <p class="text-4xl font-bold tracking-wide text-black uppercase">
                Bulk Upload
            </p>
            </div>
            <div class="flex justify-end w-full">

            </div>
        </div>

        <div class="bg-white rounded-xl shadow p-8 space-y-6 ">
            <div class="text-center">
                <h2 class="text-4xl font-semibold text-gray-700">Bulk Upload CSV</h2>
                <p class="text-xl text-gray-500">Upload your file using the format provided.</p>
            </div>

            <form @submit.prevent="submitCsv" enctype="multipart/form-data" class="space-y-4">
                <div class="text-center">
                    <label for="csv_file" class="block mb-2 text-xl font-medium text-gray-700">Select CSV File</label>
                    <input type="file" id="csv_file" name="csv_file" @change="handleFileChange"
                        accept=".csv"
                        class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" required />
                </div>

                <div class="text-center">
                    <button type="submit"
                            class="px-6 py-2 bg-green-600 text-white rounded-xl hover:bg-green-700 transition">
                        Upload CSV
                    </button>
                </div>

                <div class="text-center">
                    <a href="#" @click.prevent="downloadCsvTemplate"
                        class="text-blue-500 text-xl hover:underline">
                        Download CSV Format
                    </a>

                </div>

            </form>
        </div>



        </div>
    </div>
    <Footer />
</template>

<script setup>
import { ref } from "vue";
import { useForm } from "@inertiajs/vue3";
import { Link } from "@inertiajs/vue3";
import { Head } from '@inertiajs/vue3';
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

const form = useForm({
    csv_file: null,
});

const handleFileChange = (event) => {
    form.csv_file = event.target.files[0];
};

const submitCsv = () => {
    form.post(route('csv.upload'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('CSV uploaded successfully');
            form.reset();
        },
    });
};




$(document).ready(function () {
  let table = $("#ColorTable").DataTable({
    dom: "Bfrtip",
    pageLength: 10,
    buttons: [],
    columnDefs: [

      {
        targets: [2],
        searchable: false,

      },
    ],
    initComplete: function () {
      let searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.off("keyup");
      searchInput.on("keypress", function (e) {});
    },
    language: {
      search: "",
    },
  });
});

const downloadCsvTemplate = () => {
  const headers = ["Name", "Code", "Cost Price", "Selling Price","Discount", "Discounted Price", "Quantity", "Purchase Date", "Expired Date", "Barcode", "Batch No", "Preorder level qty", "Expiry date margin", "Whole price", "Wholesale discount", "Final whole price"];
  const rows = [["", "", ""]]; 

  let csvContent = headers.join(",") + "\n" + rows.map(e => e.join(",")).join("\n");
  let blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
  let link = document.createElement("a");
  link.href = URL.createObjectURL(blob);
  link.setAttribute("download", "template.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
};



</script>

