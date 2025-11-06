<style lang="css">
   .pagination-disabled {
   color: rgb(37 99 235);
   transition: all 0.5s ease;
   background: rgb(229 231 235 / var(--tw-bg-opacity));
   }
   .pagination {
   display: flex;
   justify-content: center;
   align-items: center;
   margin-top: 20px;
   font-size: 14px;
   float: right;
   }
   .pagination a:first-child,
   .pagination a:last-child {
   padding: 8px 16px;
   }
</style>
<template>
   <Head title="Products" />
      <Banner />
      <div
         class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16"
         >
         <!-- Include the Header -->
         <Header />
         <div class="w-full md:w-5/6 py-12 space-y-16">
            <div class="flex items-center justify-between">
               <div class="flex items-center justify-center space-x-4"></div>
               <p class="text-3xl italic font-bold text-black">
                  <span class="px-4 py-1 mr-3 text-white bg-black rounded-xl">{{
                  totalProducts
                  }}</span>
                  <span class="text-xl">/ Total Products</span>
               </p>
            </div>
            <div class="flex items-center justify-between">
               <div class="flex items-center justify-center space-x-4">
                  <Link href="/">
                  <img src="/images/back-arrow.png" class="w-14 h-14" />
                  </Link>
                  <p class="text-4xl font-bold tracking-wide text-black uppercase">
                     Products
                  </p>
               </div>
               <!-- <div class="relative inline-block">
                  <button
                    @click="handleBulkBarcode"
                    class="md:px-12 py-4 px-4 md:text-lg font-bold tracking-wider text-white uppercase rounded-xl    bg-purple-600   hover:bg-purple-700"
                  >
                    Generate Bulk Barcode
                  </button>
                    </div> -->
               <div class="relative inline-block">
                  <button
                     @click="openExpiryModal"
                     :class="[
                     'md:px-12 py-4 px-4 md:text-lg font-bold tracking-wider text-white uppercase rounded-xl  ',
                     expiryAlertCount > 0
                     ? 'bg-amber-600 hover:bg-amber-700 cursor-pointer '
                     : 'bg-amber-600 hover:bg-amber-700 cursor-pointer'
                     ]"
                     >
                  Expire Products
                  </button>
                  <!-- Alert Badge -->
                  <span
                     v-if="expiryAlertCount > 0"
                     class="absolute -top-2 -right-2 bg-yellow-500 text-black text-lg font-bold px-3 py-1 rounded-full border-2 border-white shadow-lg animate-bounce"
                     >
                  {{ expiryAlertCount }}
                  </span>
               </div>
               <div class="relative inline-block">
                  <button
                     @click="openPreOrderModal"
                     :class="[
                     'md:px-12 py-4 px-4 md:text-lg font-bold tracking-wider text-white uppercase rounded-xl  ',
                     preOrderAlertCount > 0
                     ? 'bg-red-600 hover:bg-red-700 cursor-pointer '
                     : 'bg-green-600 hover:bg-green-700 cursor-pointer'
                     ]"
                     >
                  Pre Order Level
                  </button>
                  <!-- Alert Badge -->
                  <span
                     v-if="preOrderAlertCount > 0"
                     class="absolute -top-2 -right-2 bg-yellow-500 text-black text-lg font-bold px-3 py-1 rounded-full border-2 border-white shadow-lg animate-bounce"
                     >
                  {{ preOrderAlertCount }}
                  </span>
               </div>
               <p
                  @click="
                  () => {
                  if (HasRole(['Admin'])) {
                  isCreateModalOpen = true;
                  }
                  }
                  "
                  :class="
                  HasRole(['Admin'])
                  ? 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl'
                  : 'md:px-12 py-4 px-4 md:text-2xl font-bold tracking-wider text-white uppercase bg-blue-600 cursor-not-allowed rounded-xl'
                  "
                  :title="
                  HasRole(['Admin'])
                  ? ''
                  : 'You do not have permission to add more Products'
                  "
                  >
                  <i class="md:pr-4 ri-add-circle-fill"></i> Add More Product
               </p>
            </div>
            <div class="flex items-center space-x-4">
               <!-- Search Input on the Left -->
               <div class="md:w-1/4 w-full">
                  <input
                     v-model="search"
                     @input="performSearch"
                     type="text"
                     placeholder="Search ..."
                     class="w-full custom-input"
                     />
               </div>
            </div>
            <div class="flex items-center space-x-4">
               <!-- Search Input on the Left -->
               <!-- <div class="w-1/3">
                  <input
                    v-model="search"
                    @input="performSearch"
                    type="text"
                    placeholder="Search ..."
                    class="w-full custom-input"
                  />
                  </div> -->
               <!-- Filter Dropdowns on the Right -->
               <div class="flex justify-end w-full space-x-2">
                  <select
                     v-model="selectedCategory"
                     @change="applyFilters"
                     class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                     >
                     <option value="">Filter by Category</option>
                     <option
                        v-for="category in props.allcategories"
                        :key="category.id"
                        :value="category.id"
                        >
                        {{
                        category.hierarchy_string
                        ? category.hierarchy_string + " ----> " + category.name
                        : category.name
                        }}
                     </option>
                  </select>
                  <!-- Stocks Filter -->
                  <select
                     v-model="stockStatus"
                     @change="applyFilters"
                     class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                     >
                     <option value="">Filter by Stock</option>
                     <option value="in">In Stock</option>
                     <option value="out">Out of Stock</option>
                  </select>
                  <!-- Price Filter -->
                  <select
                     v-model="sort"
                     @change="applyFilters"
                     class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg cursor-pointer custom-select"
                     >
                     <option value="">Filter by Price</option>
                     <option value="asc">Ascending</option>
                     <option value="desc">Descending</option>
                  </select>
                  <!-- Color Filter -->
                  <select
                     v-model="color"
                     @change="applyFilters"
                     class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg custom-select"
                     >
                     <option value="">Select Color</option>
                     <option
                        v-for="colorOption in props.colors"
                        :key="colorOption.id"
                        :value="colorOption.name"
                        >
                        {{ colorOption.name }}
                     </option>
                  </select>
                  <!-- Size Filter -->
                  <select
                     v-model="size"
                     @change="applyFilters"
                     class="px-6 py-3 text-xl font-normal tracking-wider text-blue-600 bg-white rounded-lg custom-select"
                     >
                     <option value="">Select Size</option>
                     <option
                        v-for="sizeOption in props.sizes"
                        :key="sizeOption.id"
                        :value="sizeOption.name"
                        >
                        {{ sizeOption.name }}
                     </option>
                  </select>
                  <Link
                     href="/products"
                     class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select"
                     >
                  Reset
                  </Link>
               </div>
            </div>
            <!-- {{ products }} -->
            <div class="grid md:grid-cols-4 grid-cols-1 gap-8">
               <template v-if="products.data.length > 0">
                  <div
                     v-for="product in products.data"
                     :key="product.id"
                     class="space-y-4 text-white transition-transform duration-300 transform bg-black border-4 border-black shadow-lg hover:-translate-y-4"
                     >
                     <div
                        @click="
                        () => {
                        openViewModal(product);
                        }
                        "
                        class="cursor-pointer"
                        >
                        <!-- <img
                           :src="`/${product.image}`"
                           alt="Product Image"
                           class="object-cover w-full h-64"
                           /> -->
                        <img
                           :src="
                           product.image
                           ? `/${product.image}`
                           : '/images/placeholder.jpg'
                           "
                           alt="Product Image"
                           class="object-cover w-full h-64"
                           />
                     </div>



                     <div class="px-2 py-4 space-y-4">
                        <div class="w-full text-[11px] font-bold tracking-wide space-y-2  p-3 rounded-lg  ">
                           <!-- Product Name Centered -->
                           <p class="text-center text-white text-[13px] font-semibold">
                              {{ product.name || "N/A" }}
                           </p>
                           <!-- Prices Row: Whole & Retail -->
                           <div class="flex justify-center items-center text-white text-[12px] gap-2">
                              <!-- Wholesale Price -->
                              <div class="bg-blue-600 px-3 py-1 rounded-full">
                                 Retail: {{ product.selling_price || "N/A" }}
                              </div>
                              <!-- Retail Price (optional) -->
                              <!--
                                 <div class="bg-green-700 px-3 py-1 rounded-full">
                                   Retail: {{ product.selling_price || "N/A" }}
                                 </div>
                                 -->
                           </div>
                        </div>
                        <div class="flex justify-center space-x-2 items-start w-full">
                           <div class="flex space-x-1 text-gray-400">
                              <p class="font-bold">Color:</p>
                              <p>{{ product.color?.name || "N/A" }}</p>
                           </div>
                           <div class="flex space-x-1 text-gray-400">
                              <p class="font-bold">Size:</p>
                              <p>
                                 {{ product.size?.name || "N/A" }}
                              </p>
                           </div>
                        </div>
                        <div class="flex items-center justify-center w-full space-x-4">
                           <p
                              class="flex items-center space-x-2 text-justify text-gray-400"
                              >
                              Supplier :
                              <b> &nbsp; {{ product.supplier?.name || "N/A" }} </b>
                           </p>
                        </div>
                        <div class="flex items-center justify-between">
                           <p
                              v-if="product.stock_quantity > 0"
                              class="text-xl font-bold tracking-wider text-green-500"
                              >
                              <i class="ri-checkbox-blank-circle-fill"></i> In Stock ({{ product.stock_quantity }})
                           </p>
                           <p v-else class="text-xl font-bold tracking-wider text-red-500">
                              <i class="ri-checkbox-blank-circle-fill"></i> Out of Stock
                           </p>
                           <div class="flex space-x-4">
                              <button
                                 :disabled="!HasRole(['Admin'])"
                                 @click="
                                 () => {
                                 if (HasRole(['Admin'])) {
                                 openDuplicateModal(product);
                                 }
                                 }
                                 "
                                 :class="{
                                 'cursor-not-allowed opacity-50': !HasRole(['Admin']),
                                 'cursor-pointer hover:bg-green-600 hover:text-white':
                                 HasRole(['Admin']),
                                 }"
                                 class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full"
                                 >
                              <i class="ri-file-copy-2-line"></i>
                              </button>
                              <button
                                 :disabled="!HasRole(['Admin'])"
                                 @click="
                                 () => {
                                 if (HasRole(['Admin'])) {
                                 openEditModal(product);
                                 }
                                 }
                                 "
                                 :class="{
                                 'cursor-not-allowed opacity-50': !HasRole(['Admin']),
                                 'cursor-pointer hover:bg-green-600 hover:text-white':
                                 HasRole(['Admin']),
                                 }"
                                 class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full"
                                 >
                              <i class="ri-pencil-line"></i>
                              </button>
                              <button
                                 :disabled="!HasRole(['Admin'])"
                                 @click="
                                 () => {
                                 if (HasRole(['Admin'])) {
                                 openDeleteModal(product);
                                 }
                                 }
                                 "
                                 :class="{
                                 'cursor-not-allowed opacity-50': !HasRole(['Admin']),
                                 'cursor-pointer hover:bg-green-600 hover:text-white':
                                 HasRole(['Admin']),
                                 }"
                                 class="flex items-center justify-center w-10 h-10 text-gray-800 transition duration-200 bg-gray-100 rounded-full"
                                 >
                              <i class="ri-delete-bin-line"></i>
                              </button>
                           </div>
                        </div>
                     </div>




                     
                  </div>
               </template>
               <template v-else>
                  <div class="col-span-4 text-center text-gray-500">
                     <p class="text-center text-red-500 text-[17px]">
                        No Products Available
                     </p>
                  </div>
               </template>
            </div>
            <div class="flex space-x-2 pagination">
               <!-- Prev Button -->
               <span
                  v-if="products.links[0]"
                  @click.prevent="navigateTo(products.links[0].url)"
                  :class="[
                  'pagination-btn',
                  { 'pagination-disabled': !products.links[0].url },
                  ]"
                  >
               Previous
               </span>
               <!-- Pagination Links -->
               <span
                  v-for="(link) in products.links.slice(
                  1,
                  products.links.length - 1
                  )"
                  :key="link.label"
                  @click.prevent="navigateTo(link.url)"
                  :class="['pagination-btn', { 'pagination-active': link.active }]"
                  v-html="link.label"
                  ></span>
               <!-- Next Button -->
               <span
                  v-if="products.links[products.links.length - 1]"
                  @click.prevent="
                  navigateTo(products.links[products.links.length - 1].url)
                  "
                  :class="[
                  'pagination-btn',
                  {
                  'pagination-disabled':
                  !products.links[products.links.length - 1].url,
                  },
                  ]"
                  >
               Next
               </span>
            </div>
         </div>
      </div>
      <ProductCreateModel
         :categories="allcategories"
         :colors="colors"
         :sizes="sizes"
         :suppliers="suppliers"
         :units="props.units || []"
         v-model:open="isCreateModalOpen"
         />
      <ProductUpdateModel
         :categories="allcategories"
         :colors="colors"
         :suppliers="suppliers"
         :sizes="sizes"
         v-model:open="isEditModalOpen"
         :selected-product="selectedProduct"
         :units="props.units || []"
         :current-page="products.current_page"
         />
      <ProductDuplicateModel
         :categories="allcategories"
         :colors="colors"
         :suppliers="suppliers"
         :sizes="sizes"
         v-model:open="isDuplicateModalOpen"
         :selected-product="selectedProduct"
         />
      <ProductViewModel
         :categories="allcategories"
         :colors="colors"
         :sizes="sizes"
         v-model:open="isViewModalOpen"
         :selected-product="selectedProduct"
         />
      <ProductDeleteModel
         v-model:open="isDeleteModalOpen"
         :selected-product="selectedProduct"
         @delete="deleteProduct"
         />
      <PreOrderProductModal
         :preOrderProducts="preOrderProducts"
         v-model:open="isPreOrderModalOpen"
         />
      <ExpireProductModal
         :expiryProducts="expiryProducts"
         v-model:open="isExpiryModalOpen"
         />
      <BulkBarcodeModal   v-model="isBulkBarcodeModalOpen"
         :products="props.rawProducts" />
      <Footer />
</template>
<script setup>
   import { ref, computed,  watch } from 'vue';
   import { Head } from "@inertiajs/vue3";
   import { Link, useForm, router } from "@inertiajs/vue3";
   import Header from "@/Components/custom/Header.vue";
   import Footer from "@/Components/custom/Footer.vue";
   import Banner from "@/Components/Banner.vue";
   import { defineProps, onMounted } from "vue";
   import ProductCreateModel from "@/Components/custom/ProductCreateModel.vue";
   import PreOrderProductModal from '@/Components/custom/PreOrderProductModal.vue'
   import ExpireProductModal from '@/Components/custom/ExpireProductModal.vue'
   import ProductDuplicateModel from "@/Components/custom/ProductDuplicateModel.vue";
   import ProductUpdateModel from "@/Components/custom/ProductUpdateModel.vue";
   import ProductViewModel from "@/Components/custom/ProductViewModel.vue";
   import ProductDeleteModel from "@/Components/custom/ProductDeleteModel.vue";
   import BulkBarcodeModal from '@/Components/custom/BulkBarcodeModal.vue';
   import { debounce } from "lodash";
   import { HasRole } from "@/Utils/Permissions";

   const isCreateModalOpen = ref(false);
   const isEditModalOpen = ref(false);
   const isDuplicateModalOpen = ref(false);
   const isViewModalOpen = ref(false);
   const selectedProduct = ref(null);
   const isDeleteModalOpen = ref(false);
   const isPreOrderModalOpen = ref(false);
   const isExpiryModalOpen = ref(false);
   const isBulkBarcodeModalOpen = ref(false);


   const emit = defineEmits(["update:open"]);
   const preOrderAlertCount = computed(() => {
     if (props.preOrderProducts && props.preOrderProducts.data) {
       return props.preOrderProducts.data.filter(product => {
         const preorderLevel = product.preorder_level_qty || 0;
         const currentStock = product.total_quantity || 0;
         return preorderLevel > 0 && currentStock <= preorderLevel;
       }).length;
     } else if (props.preOrderProducts && Array.isArray(props.preOrderProducts)) {
       return props.preOrderProducts.filter(product => {
         const preorderLevel = product.preorder_level_qty || 0;
         const currentStock = product.total_quantity || 0;
         return preorderLevel > 0 && currentStock <= preorderLevel;
       }).length;
     }
     return props.preOrderAlertCount || 0;
   });




   const expiryProducts = ref([]);
   const preOrderProducts = ref([]);



   const openEditModal = (product) => {
     selectedProduct.value = product; // Set the selected product
     isEditModalOpen.value = true; // Open the edit modal
   };

   const openDuplicateModal = (product) => {
     selectedProduct.value = product; // Set the selected product
     isDuplicateModalOpen.value = true; // Open the edit modal
   };

   const openViewModal = (product) => {
     selectedProduct.value = product; // Set the selected product
     isViewModalOpen.value = true; // Open the view modal
   };

   const openDeleteModal = (product) => {
     selectedProduct.value = product;
     isDeleteModalOpen.value = true;
   };

   const openPreOrderModal = () => {
     // Check if preOrderProducts exists and has data
     if (props.preOrderProducts && props.preOrderProducts.data) {
       // Filter products that are at or below pre-order level
       const filtered = props.preOrderProducts.data.filter(product => {
         const preorderLevel = product.preorder_level_qty || 0;
         const currentStock = product.total_quantity || 0;
         return preorderLevel > 0 && currentStock <= preorderLevel;
       });

       preOrderProducts.value = filtered;
       isPreOrderModalOpen.value = true;
     } else if (props.preOrderProducts && Array.isArray(props.preOrderProducts)) {
       // Handle case where preOrderProducts is directly an array
       const filtered = props.preOrderProducts.filter(product => {
         const preorderLevel = product.preorder_level_qty || 0;
         const currentStock = product.total_quantity || 0;
         return preorderLevel > 0 && currentStock <= preorderLevel;
       });

       preOrderProducts.value = filtered;
       isPreOrderModalOpen.value = true;
     } else {
       // No pre-order products data available
       preOrderProducts.value = [];
       isPreOrderModalOpen.value = true;
     }
   };

   const openExpiryModal = () => {
     if (props.expiryProducts && props.expiryProducts.length > 0) {
       expiryProducts.value = props.expiryProducts;
     } else {
       expiryProducts.value = [];
     }
     isExpiryModalOpen.value = true;
   };





   const props = defineProps({
     products: Object,
       rawProducts: Array,
     categories: Array,
     suppliers: Array,
     colors: Array,
     sizes: Array,
     allcategories: Array,
   preOrderProducts: [Array, Object],
   expiryProducts: [Array, Object],
     totalProducts: Number,
     search: String,
     sort: String,
     color: String,
     size: String,
     stockStatus: String,
     selectedCategory: String,
     preOrderAlertCount: Number,
     expiryAlertCount: Number,
         units: Array,

   });

   const search = ref(props.search || "");
   const sort = ref(props.sort || "");
   const color = ref(props.color || "");
   const size = ref(props.size || "");
   const suppliers = ref(props.suppliers || "");
   const stockStatus = ref(props.stockStatus || "");
   const selectedCategory = ref(props.selectedCategory || "");

   const performSearch = debounce(() => {
     applyFilters();
   }, 500);

   const applyFilters = (page) => {
     router.get(
       route("products.index"),
       {
         search: search.value,
         sort: sort.value,
         color: color.value,
         size: size.value,
         stockStatus: stockStatus.value,
         selectedCategory: selectedCategory.value,
       },
       { preserveState: true }
     );
   };

   onMounted(() => {
     // console.log("Products:", props.products);
     // console.table(props.products);
   });
   const showModal = ref(false);
   const form = useForm({});

      // expose units from props for child component
      const units = ref(props.units || []);

   const openModal = (id) => {
     productToDelete.value = id;
     showModal.value = true;
   };
   const deleteProduct = (id) => {
     const form = useForm({});
     form.delete(`/products/${id}`, {
       onSuccess: () => {
         isDeleteModalOpen.value = false; // Close the modal on success
       },
       onError: (errors) => {
         console.error("Delete failed:", errors);
       },
     });
   };

   const handleBulkBarcode = () => {
    isBulkBarcodeModalOpen.value = true;

   };



   const navigateTo = (url) => {
     if (!url) return; // Avoid null or undefined URLs

     // Extract the `page` parameter from the URL
     const urlParams = new URLSearchParams(
       new URL(url, window.location.origin).search
     );
     const page = urlParams.get("page");

     // Use Inertia's router.get with current filters
     router.get(
       route("products.index"),
       {
         page, // Add the page parameter
         search: search.value,
         sort: sort.value,
         color: color.value,
         size: size.value,
         stockStatus: stockStatus.value,
         selectedCategory: selectedCategory.value,
       },
       {
         preserveState: true, // Maintain the current state
         preserveScroll: true, // Prevent scroll reset
       }
     );
   };
</script>
