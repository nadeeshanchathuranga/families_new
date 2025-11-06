<template>
  <Head title="Users" />
  <Banner />
  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <div class="w-full md:w-5/6 py-12 space-y-10">
      <!-- Header row -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Users</p>
        </div>
        <p class="text-3xl italic font-bold text-black">
          <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{ totalUsers }}</span>
          <span class="text-xl">/ Total Users</span>
        </p>
      </div>

      <!-- Add user -->
      <div class="flex justify-end">
        <button
          type="button"
          @click="openCreateModal"
          :class="HasRole(['Admin'])
            ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl'
            : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-400 rounded-xl cursor-not-allowed'"
          :disabled="!HasRole(['Admin'])"
          :title="HasRole(['Admin']) ? '' : 'You do not have permission to add users'"
        >
          <i class="md:pr-4 ri-add-circle-fill"></i> Add User
        </button>
      </div>

      <!-- Table -->
      <template v-if="Array.isArray(allusers) && allusers.length > 0">
        <div class="overflow-x-auto">
          <table id="UserTable" class="w-full text-gray-700 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
            <thead>
              <tr class="bg-gradient-to-r from-blue-600 via-blue-500 to-blue-600 text-[16px] text-white border-b border-blue-700">
                <th class="p-4 font-semibold tracking-wide text-left uppercase">ID</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Name</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Email</th>
                <th class="p-4 font-semibold tracking-wide text-left uppercase">Role</th>
                <th class="p-4 font-semibold tracking-wide text-center uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="text-[13px] font-normal">
              <tr
                v-for="(user , idx) in allusers"
                :key="user.id"
                class="transition duration-200 ease-in-out hover:bg-gray-200 hover:shadow-lg"
              >
                <td class="p-4 font-bold border-t border-gray-200">{{ user.id }}</td>
                <td class="p-4 border-t border-gray-200">{{ user.name || 'N/A' }}</td>
                <td class="p-4 border-t border-gray-200">{{ user.email || 'N/A' }}</td>
                <td class="p-4 border-t border-gray-200">
                  <span
                    class="px-2 py-1 rounded text-lg font-semibold"
                    :class="{
                      'bg-red-100 text-red-700': user.role_type==='Admin',
                      'bg-green-100 text-green-700': user.role_type==='Manager',
                      'bg-blue-100 text-blue-700': user.role_type==='Cashier'
                    }"
                  >
                    {{ user.role_type }}
                  </span>
                </td>

                <!-- Actions -->
                <td class="p-4 text-center border-t border-gray-200">
                  <div class="inline-flex items-center space-x-3">
                  


                    <button
  :class="HasRole(['Admin'])
            ? 'px-4 py-2 bg-green-500 text-white rounded-lg'
            : 'px-4 py-2 bg-green-400 text-white rounded-lg cursor-not-allowed'"
  :title="HasRole(['Admin'])
            ? ''
            : 'You do not have permission to edit'"
  :disabled="!HasRole(['Admin'])"
  @click="() => { if (HasRole(['Admin'])) openEditModal(user); }"
>
  Edit
</button>


                   

<button
  v-if="user.role_type !== 'Admin'"
  :class="HasRole(['Admin'])
            ? 'px-4 py-2 bg-red-500 text-white rounded-lg'
            : 'px-4 py-2 bg-red-400 text-white rounded-lg cursor-not-allowed'"
  :title="HasRole(['Admin'])
            ? ''
            : 'You do not have permission to delete'"
  :disabled="!HasRole(['Admin'])"
  @click="() => { if (HasRole(['Admin'])) openDeleteModal(user); }"
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
        <div class="text-center text-gray-500 py-8">No users available.</div>
      </template>
    </div>
  </div>

  <!-- Modals -->
  <UserCreateModal v-model:open="isCreateModalOpen" />

 

  <UserUpdateModal
  v-model:open="isEditModalOpen"
  :user="selectedUser"
  :key="selectedUser?.id || 'edit-modal'"
/>

  <!-- ✅ FIXED: pass correct prop 'user' -->
 <UserDeleteModal
  v-model:open="isDeleteModalOpen"
  :selected-user="selectedUser"
/>

  <Footer />
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, nextTick } from "vue";
import { Link, Head, usePage } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import { HasRole } from "@/Utils/Permissions";

import UserCreateModal from "@/Components/custom/UserCreateModal.vue";
import UserUpdateModal from "@/Components/custom/UserUpdateModal.vue";
import UserDeleteModal from "@/Components/custom/UserDeleteModal.vue";

const props = defineProps({
  allusers: Array,
  totalUsers: Number,
});


const openDeleteModal = (user) => {
  selectedUser.value = user;
  isDeleteModalOpen.value = true;
};

const openEditModal = (user) => { 
  selectedUser.value = user;
  isEditModalOpen.value = true;
};


const isCreateModalOpen = ref(false);
const isEditModalOpen   = ref(false);
const isDeleteModalOpen = ref(false);
const selectedUser      = ref(null);

// optional: current user id, if you want to prevent self-delete in UI
const page = usePage();
const authUserId = page?.props?.auth?.user?.id ?? null;

const openCreateModal = () => {
  if (HasRole(["Admin"])) isCreateModalOpen.value = true;
};

let dt = null;

onMounted(async () => {
  const tableId = "#UserTable";
  await nextTick();

  if ($.fn.DataTable.isDataTable(tableId)) {
    $(tableId).DataTable().clear().destroy();
  }

  dt = $(tableId).DataTable({
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
    autoWidth: false,
    deferRender: true,
    language: { search: "" },
    initComplete: function () {
      const searchInput = $("div.dataTables_filter input");
      searchInput.attr("placeholder", "Search ...");
      searchInput.on("keypress", function (e) {
        if (e.which === 13) dt.search(this.value).draw();
      });
      dt.order([0, "desc"]).draw();
    },
  });

  // delegated handlers (DataTables mutates DOM)
  $(tableId).on("click", ".js-edit-user", function () {
    if (!HasRole(["Admin"])) return;
    const idx = Number($(this).data("idx"));
    selectedUser.value = props.allusers[idx];
    isEditModalOpen.value = true;
  });

 $(tableId).on("click", ".js-delete-user", function () {
  if (!HasRole(["Admin"])) return;
  const idx = Number($(this).data("idx"));
  selectedUser.value = props.allusers[idx];  // must have id here
  isDeleteModalOpen.value = true;
});



});

onBeforeUnmount(() => {
  const tableId = "#UserTable";
  $(tableId).off("click", ".js-edit-user");
  $(tableId).off("click", ".js-delete-user");
  if (dt) {
    dt.destroy(true);
    dt = null;
  }
});
</script>

<style>
/* DataTables pagination container */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* ✅ Correct default selector: #<tableId>_filter */
#UserTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px;
}

#UserTable_filter label {
  font-size: 17px;
  color: #000000;
  display: flex;
  align-items: center;
}

#UserTable_filter input[type="search"] {
  font-weight: 400;
  padding: 9px 15px;
  font-size: 14px;
  color: #000000cc;
  border: 1px solid rgb(209 213 219);
  border-radius: 5px;
  background: #fff;
  outline: none;
  transition: all 0.2s ease;
}

#UserTable_filter input[type="search"]:focus {
  border: 1px solid #4b5563;
  box-shadow: none;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>
