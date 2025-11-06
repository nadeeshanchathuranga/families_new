<template>
  <Head title="POS" />

  <div
    class="flex flex-col items-center justify-start h-screen  space-y-3 bg-gray-100 md:px-20 px-4 overflow-auto text-[1.06rem] leading-relaxed selection:bg-yellow-200"
  >
    <!-- Logo Section - Outside the box -->
    <div class="w-full md:w-5/6 flex flex-col items-start pt-4 pb-2 gap-2">
      <Link href="/dashboard" class="cursor-pointer hover:opacity-80 transition-opacity">
        <img src="/images/jaan_logo.jpg" alt="JAAN Network" class="h-16 md:h-20 object-contain" />
      </Link>
      <Link 
        href="/dashboard" 
        class="inline-flex items-center gap-4 px-6 py-3 bg-gray-100 text-gray-900 font-bold text-2xl rounded-lg hover:bg-gray-200 transition-colors border border-gray-300"
      >
        <div class="flex items-center justify-center w-10 h-10 rounded-full border-2 border-gray-900">
          <i class="ri-arrow-left-line text-2xl"></i>
        </div>
        <span>POS</span>
      </Link>
    </div>

    <div class="w-full md:w-5/6  ">
      <div class="flex flex-col w-full gap-5 md:flex-row">
        <div class="flex w-full p-5 border-2 border-black rounded-2xl bg-white shadow-sm">
          <div class="flex flex-col items-start justify-center w-full md:px-8 px-2 gap-5">

            <!-- Top Controls -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between w-full gap-3">
              <!-- Left -->
              <div class="flex flex-col md:flex-row md:items-center gap-3 w-full md:w-auto">
                <div class="flex flex-col md:flex-row md:items-center gap-3 w-full md:w-auto">
                  <Link
                    href="/"
                    class="inline-flex items-center gap-2 px-4 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
                  >
                    <span>Home</span>
                  </Link>
                </div>

                <!-- Logout -->
                <form @submit.prevent="logout" class="flex items-center">
                  <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition font-bold"
                  >
                    <span>Logout</span>
                  </button>
                </form>

                <!-- Order ID + Refresh -->
                <div class="flex items-center md:ml-6 w-full md:w-auto">
                  <p class="text-xl font-bold tracking-wide text-gray-900 md:text-left ml-auto">
                    Order ID: #{{ orderid }}
                  </p>
                  <button
                    @click="refreshData"
                    class="ml-3 text-xl text-gray-800 hover:rotate-180 transition-transform duration-300"
                    aria-label="Refresh"
                  >
                    <i class="ri-restart-line"></i>
                  </button>
                </div>
              </div>

              <!-- Right -->
              <div class="flex flex-wrap items-center gap-3 justify-end w-full md:w-auto">
                <button
                  @click="isServiceModalOpen = true"
                  class="flex items-center px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 transition text-white text-xl font-semibold"
                  title="Quick add a service"
                >
                  <i class="ri-add-circle-fill mr-2"></i>
                  Add Service
                </button>

                <button
                  @click="openExpenseCreate"
                  class="flex items-center px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 transition text-white text-xl font-semibold"
                  title="Add a new expense"
                >
                  <i class="ri-add-circle-fill mr-2"></i>
                  Add Expense
                </button>


 

     <button
                  @click="openReturnBills"
                  class="flex items-center px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 transition text-white text-xl font-semibold"
                  title="Add a new expense"
                >
                  <i class="ri-add-circle-fill mr-2"></i>
                  Return Bills
                </button>




                <button
                  @click="isSelectModalOpen = true"
                  class="flex items-center px-4 py-2 rounded-lg bg-blue-100 hover:bg-blue-200 transition text-xl"
                >
                  <img src="/images/selectpsoduct.svg" alt="Manual Icon" class="w-5 h-5 mr-2" />
                  <span class="text-blue-700 font-semibold">User Manual</span>
                </button>

                <button
                  @click="isModalOpen = true"
                  class="flex items-center px-4 py-2 rounded-lg bg-blue-100 hover:bg-blue-200 transition text-xl"
                >
                  <img src="/images/selectpsoduct.svg" alt="Order Icon" class="w-5 h-5 mr-2" />
                  <span class="text-blue-700 font-semibold">Orders & Customer</span>
                </button>

                <ServiceSelector v-model:modelValue="isServiceSelectorOpen" @confirmed="handleServicesSelected" />
              </div>
            </div>

            <!-- Two-column POS Body -->
            <div class="w-full grid grid-cols-1 lg:grid-cols-2 gap-6">
              <!-- LEFT: Products & Services -->
              <section class="w-full bg-white border border-black rounded-xl p-5 shadow-sm">
                <div class="flex flex-col md:flex-row w-full items-center space-y-4 md:space-y-0 md:space-x-4">
                  <!-- Barcode -->
                  <div class="flex w-full md:w-3/4 items-center space-x-4">
                    <input
                      v-model="form.barcode"
                      id="search"
                      type="text"
                      placeholder="Enter BarCode Here!"
                      ref="barcodeInputRef"
                      autocomplete="off"
                      autocapitalize="off"
                      spellcheck="false"
                      @keydown.enter.prevent="submitBarcode"
                      @focus="$event.target.select()"
                      class="w-full h-16 px-4 border border-gray-300 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                    />
                    <button
                      @click="submitBarcode"
                      class="px-8 py-4 text-xl font-bold text-white uppercase bg-blue-600 hover:bg-blue-700 rounded-xl transition"
                    >
                      Enter
                    </button>
                  </div>

                  <!-- Credit Bill -->
                  <div class="flex items-center justify-end w-full md:w-1/4">
                    <label for="credit_bill" class="flex items-center space-x-3 text-lg text-black">
                      <input
                        id="credit_bill"
                        type="checkbox"
                        v-model="credit_bill"
                        class="w-6 h-6 text-blue-600 border-2 border-black rounded focus:ring-2 focus:ring-blue-500"
                      />
                      <span class="font-semibold">Credit Bill</span>
                    </label>
                  </div>
                </div>

                <div class="w-full max-h-[50vh] overflow-y-auto">
                  <!-- Barcode Scanning Animation -->
                  <div
                    v-if="products.length === 0 && services.length === 0"
                    class="flex flex-col items-center justify-center py-20"
                  >
                    <div class="relative w-48 h-48 mb-6">
                      <!-- Animated scanning line -->
                      <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-40 h-32 border-4 border-blue-500 rounded-lg relative overflow-hidden">
                          <!-- Horizontal scanning line animation -->
                          <div class="absolute w-full h-1 bg-red-500 animate-scan shadow-lg"></div>
                          <!-- Barcode lines -->
                          <div class="flex items-center justify-center h-full gap-1 px-4">
                            <div class="w-1 h-20 bg-gray-800"></div>
                            <div class="w-2 h-20 bg-gray-800"></div>
                            <div class="w-1 h-20 bg-gray-800"></div>
                            <div class="w-3 h-20 bg-gray-800"></div>
                            <div class="w-1 h-20 bg-gray-800"></div>
                            <div class="w-2 h-20 bg-gray-800"></div>
                            <div class="w-1 h-20 bg-gray-800"></div>
                            <div class="w-2 h-20 bg-gray-800"></div>
                            <div class="w-3 h-20 bg-gray-800"></div>
                            <div class="w-1 h-20 bg-gray-800"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <p class="text-xl text-gray-700 font-medium animate-pulse">Scanning Barcode...</p>
                    <p class="text-sm text-gray-500 mt-2">Please scan a product to begin</p>
                  </div>

               
                  <!-- Product List -->
<div
  class="flex items-center w-full py-3 border-b border-black"
  v-for="(item, index) in products"
  :key="item.id"
>
  <!-- Number -->
  <div class="flex w-1/12 justify-center">
    <span class="text-xl font-bold text-gray-700">{{ index + 1 }}.</span>
  </div>

  <!-- Product Image -->
  <div class="flex w-1/6">
    <img
      :src="item.image ? `/${item.image}` : '/images/placeholder.jpg'"
      alt="Product Image"
      class="object-cover w-14 h-14 border border-gray-500 rounded"
    />
  </div>

  <!-- Product Details -->
  <div class="flex flex-col justify-between w-2/3 gap-2">
    <p class="text-xl text-gray-900 font-semibold">
      {{ item.name }}
      <span v-if="item.unit" class="text-sm text-gray-500">({{ item.unit.name }})</span>
    </p>

    <div class="flex items-center justify-between w-full">
      <div class="flex items-center gap-2">
        <button
          @click="incrementQuantity(item.id)"
          class="flex items-center justify-center w-9 h-9 text-white bg-black rounded hover:bg-gray-800 text-xl"
          aria-label="Increase"
        >
          <i class="ri-add-line"></i>
        </button>
        <input
          type="number"
          v-model.number="item.quantity"
          min="0"
          class="bg-[#D9D9D9] border-2 border-black h-10 w-20 text-gray-900 rounded text-center text-xl"
        />
        <button
          @click="decrementQuantity(item.id)"
          class="flex items-center justify-center w-9 h-9 text-white bg-black rounded hover:bg-gray-800 text-xl"
          aria-label="Decrease"
        >
          <i class="ri-subtract-line"></i>
        </button>
        <!-- Discount Controls -->
        <div class="flex items-center gap-2 ml-4">
          <input
            type="number"
            min="0"
            v-model.number="item.discount_value"
            class="w-20 border border-gray-400 rounded px-2 py-1 text-xl"
            placeholder="Discount"
          />
          <select
            v-model="item.discount_type"
            class="border border-gray-400 rounded px-2 py-1 text-xl"
          >
            <option value="rs">Rs</option>
            <option value="percent">%</option>
          </select>
        </div>
      </div>

      <div class="flex items-center justify-center">
        <div class="text-right">
          <p
            v-if="(isWholesale ? item.wholesale_discount : item.discount) && (isWholesale ? item.wholesale_discount : item.discount) > 0 && item.apply_discount === false && !appliedCoupon"
            @click="applyDiscount(item.id)"
            class="cursor-pointer inline-block py-1 px-2 bg-green-600 rounded-lg font-bold text-white text-sm"
          >
            Apply {{ isWholesale ? item.wholesale_discount : item.discount }}% Off
          </p>

          <div v-if="isReturnBill" class="mt-2">
            <select
              v-model="item.returnReason"
              class="w-full border border-gray-400 px-2 py-1 rounded text-xl"
              required
            >
              <option value="" disabled selected>Select a reason</option>
              <option
                v-for="reason in props.returnReasons"
                :key="reason.id"
                :value="reason.id"
              >
                {{ reason.reason }}
              </option>
            </select>
          </div>

          <p
            v-if="(isWholesale ? item.wholesale_discount : item.discount) && (isWholesale ? item.wholesale_discount : item.discount) > 0 && item.apply_discount === true && !appliedCoupon"
            @click="removeDiscount(item.id)"
            class="cursor-pointer inline-block mt-1 py-1 px-2 bg-red-600 rounded-lg font-bold text-white text-sm"
          >
            Remove {{ isWholesale ? item.wholesale_discount : item.discount }}% Off
          </p>

          <div class="flex flex-col items-end mt-1">
            <template v-if="item.discount_value && item.discount_type && getOriginalPrice(item) > getDiscountedPrice(item)">
              <span class="text-green-700 font-bold text-xl mb-1">
                {{ getDiscountedPrice(item).toFixed(2) }} LKR
              </span>
              <span class="line-through text-gray-400 text-base">
                {{ getOriginalPrice(item).toFixed(2) }} LKR
              </span>
            </template>
            <template v-else>
              <span class="text-xl font-bold text-gray-900">
                {{ getOriginalPrice(item).toFixed(2) }} LKR
              </span>
            </template>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Remove Button -->
  <div class="flex justify-end w-1/12">
    <button
      @click="removeProduct(item.id)"
      class="text-2xl text-gray-900 border-2 border-black rounded-full w-9 h-9 flex items-center justify-center hover:bg-gray-50"
      aria-label="Remove"
    >
      <i class="ri-close-line"></i>
    </button>
  </div>
</div>

                  <!-- Service List -->
                  <div v-if="services.length > 0" class="mt-3 min-w-[300px]">
                    <h3 class="text-xl font-semibold mb-2 text-gray-900">Selected Services</h3>
                    <ul class="divide-y divide-gray-200 border rounded-lg">
                      <li
                        v-for="(service, index) in services"
                        :key="index"
                        class="flex justify-between items-center px-3 py-2 gap-2"
                      >
                        <div class="flex-grow flex items-center gap-2">
                          <template v-if="service.isEditing">
                            <input
                              v-model="service.service_name"
                              type="text"
                              class="border rounded px-2 py-1 text-xl w-44"
                              placeholder="Service Name"
                            />
                            <input
                              v-model.number="service.price"
                              type="number"
                              min="0"
                              step="0.01"
                              class="border rounded px-2 py-1 w-28 text-xl"
                              placeholder="Price"
                            />
                          </template>
                          <template v-else>
                            <span class="font-medium text-gray-900 text-xl">{{ service.service_name }}</span>
                            <span class="text-gray-600 ml-2 text-xl">LKR {{ Number(service.price||0).toFixed(2) }}</span>
                          </template>
                        </div>
                        <div class="flex gap-2">
                          <button
                            @click="toggleEditService(index)"
                            class="text-blue-700 hover:underline text-xl"
                            :aria-label="service.isEditing ? 'Save service' : 'Edit service'"
                          >
                            {{ service.isEditing ? "Save" : "Edit" }}
                          </button>
                          <button
                            @click="removeService(index)"
                            class="text-red-600 hover:underline text-xl"
                            aria-label="Remove service"
                          >
                            Delete
                          </button>
                        </div>
                      </li>
                    </ul>
                  </div>
                </div>
              </section>

              <!-- RIGHT: Summary + Payments -->
              <aside
                class="w-full flex flex-col gap-5"
                ref="asideRef"
                tabindex="0"
                @keydown.capture="onAsideKeydown"
              >
                <div class="w-full space-y-3 border border-black rounded-xl p-5 bg-white shadow-sm">
                  <div class="flex items-center justify-between">
                    <p class="text-xl font-medium text-gray-800">Sub Total</p>
                    <p class="text-xl font-semibold text-gray-900">{{ subtotal }} LKR</p>
                  </div>

                  <div class="flex items-center justify-between border-b border-gray-300 pb-2">
                    <p class="text-xl font-medium text-gray-800">Discount</p>
                    <p class="text-xl font-semibold text-red-600">({{ totalDiscount }} LKR)</p>
                  </div>

                  <div class="flex items-center justify-between border-b border-gray-300 pb-3">
                    <p class="text-xl font-medium text-gray-800">Custom Discount</p>
                    <div class="flex items-center gap-2">
                      <CurrencyInput
                        ref="customDiscountRef"
                        v-model="custom_discount"
                        @blur="validateCustomDiscount"
                        placeholder="Enter value"
                        class="rounded-md px-3 py-1 text-xl text-gray-900 focus:ring-2 focus:ring-blue-500"
                      />
                      <select
                        ref="customDiscountTypeRef"
                        v-model="custom_discount_type"
                        class="px-8 py-1 rounded-md text-xl text-gray-900 focus:ring-2 focus:ring-blue-500"
                      >
                        <option value="fixed">Rs</option>
                        <option value="percent">%</option>
                      </select>
                    </div>
                  </div>

                  <div class="flex items-center justify-between border-b border-gray-300 pb-3">
                    <p class="text-xl font-medium text-gray-800">Cash</p>
                    <div class="flex items-center">
                      <CurrencyInput
                        ref="cashInputRef"
                        v-model="cash"
                        :options="{ currency: 'LKR' }"
                        class="rounded-md px-3 py-1 text-xl text-gray-900"
                        tabindex="1"
                        :disabled="selectedPaymentMethod !== 'cash'"
                      />
                      <span class="ml-2 text-xl font-medium" tabindex="-1">LKR</span>
                    </div>
                  </div>

                  <div class="flex items-center justify-between pt-1">
                    <p class="text-xl font-extrabold text-gray-900">Total</p>
                    <p class="text-xl font-extrabold text-gray-900">{{ total }} LKR</p>
                  </div>

                  <div class="flex items-center justify-between border-t border-gray-300 pt-2">
                    <p class="text-xl font-medium text-gray-800">Balance</p>
                    <p class="text-xl font-semibold text-green-700">{{ balance }} LKR</p>
                  </div>

                  <div hidden>
                    <label for="total_to_include_guide" class="mb-1 block text-base font-semibold text-gray-700">
                      Total Not Including Guide (LKR)
                    </label>
                    <input
                      id="total_to_include_guide"
                      type="number"
                      v-model="total_to_include_guide"
                      placeholder="0.00"
                      readonly
                      class="w-full px-3 py-2 border border-gray-300 rounded-md text-xl focus:ring-2 focus:ring-blue-500"
                    />
                  </div>

                  <div class="pt-2">
                    <div class="relative flex items-center">
                      <input
                        ref="couponRef"
                        id="coupon"
                        v-model="couponForm.code"
                        type="text"
                        placeholder="Enter Coupon Code"
                        class="w-full h-11 px-4 pr-28 text-xl text-gray-800 border-2 border-gray-300 rounded-full focus:ring-2 focus:ring-blue-500"
                      />
                      <template v-if="!appliedCoupon">
                        <button
                          @click="submitCoupon"
                          class="absolute right-1 top-1 h-9 px-4 text-sm md:text-base font-semibold text-white bg-blue-600 rounded-full hover:bg-blue-700"
                        >
                          Apply Coupon
                        </button>
                      </template>
                      <template v-else>
                        <button
                          @click="removeCoupon"
                          class="absolute right-1 top-1 h-9 px-4 text-sm md:text-base font-semibold text-white bg-red-600 rounded-full hover:bg-red-700"
                        >
                          Remove Coupon
                        </button>
                      </template>
                    </div>
                  </div>
                </div>

                <div v-if="selectedPaymentMethod === 'online'" class="w-full border-2 border-black rounded-2xl p-6 bg-gray-50 shadow-md">
                  <!-- Cheque Form -->
                  <div class="mt-6">
                    <h4 class="text-xl font-semibold text-black mb-4">Cheque Information</h4>

                    <div class="flex flex-col md:flex-row gap-6 mb-4">
                      <div class="w-full">
                        <label for="cheque_number" class="mb-1 text-lg font-semibold text-gray-700">Cheque Number</label>
                        <input
                          id="cheque_number"
                          type="text"
                          v-model="cheque_number"
                          placeholder="Enter cheque number"
                          class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        />
                      </div>

                      <div class="w-full">
                        <label for="bank_name" class="mb-1 text-lg font-semibold text-gray-700">Bank Name</label>
                        <input
                          id="bank_name"
                          type="text"
                          v-model="bank_name"
                          placeholder="Enter bank name"
                          class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                    </div>

                    <div class="flex flex-col md:flex-row gap-6 mb-4">
                      <div class="w-full">
                        <label for="cheque_date" class="mb-1 text-lg font-semibold text-gray-700">Cheque Date</label>
                        <input
                          id="cheque_date"
                          type="date"
                          v-model="cheque_date"
                          class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        />
                      </div>

                      <div class="w-full">
                        <label for="cheque_amount" class="mb-1 text-lg font-semibold text-gray-700">Cheque Amount (LKR)</label>
                        <input
                          id="cheque_amount"
                          type="number"
                          v-model="cheque_amount"
                          placeholder="Enter amount"
                          class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        />
                      </div>
                    </div>

                    <div class="mb-4">
                      <label for="cheque_notes" class="mb-1 text-lg font-semibold text-gray-700">Notes (Optional)</label>
                      <textarea
                        id="cheque_notes"
                        v-model="cheque_notes"
                        rows="3"
                        placeholder="Any special notes"
                        class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                      ></textarea>
                    </div>
                  </div>
                </div>

                <!-- Payment Methods -->
                <div class="w-full bg-white p-5 rounded-xl border border-black shadow-sm">
                  <p class="text-xl font-semibold text-center mb-3 text-gray-900">Payment Method</p>
                  <div class="flex justify-center gap-4">
                    <div
                      @click="selectedPaymentMethod = 'cash'"
                      :class="[
                        'cursor-pointer w-[96px] p-2 border border-black rounded-lg flex flex-col items-center transition',
                        selectedPaymentMethod === 'cash' ? 'bg-yellow-300/70 font-bold shadow' : 'hover:bg-gray-100'
                      ]"
                    >
                      <img src="/images/money-stack.png" alt="Cash" class="w-12" />
                      <span class="mt-1 text-sm md:text-base">Cash</span>
                    </div>

                    <div
                      @click="selectedPaymentMethod = 'card'"
                      :class="[
                        'cursor-pointer w-[96px] p-2 border border-black rounded-lg flex flex-col items-center transition',
                        selectedPaymentMethod === 'card' ? 'bg-yellow-300/70 font-bold shadow' : 'hover:bg-gray-100'
                      ]"
                    >
                      <img src="/images/bank-card.png" alt="Card" class="w-12" />
                      <span class="mt-1 text-sm md:text-base">Card</span>
                    </div>

                    <div
                      @click="selectedPaymentMethod = 'online'"
                      :class="[
                        'cursor-pointer w-[96px] p-2 border border-black rounded-lg flex flex-col items-center transition',
                        selectedPaymentMethod === 'online' ? 'bg-yellow-300/70 font-bold shadow' : 'hover:bg-gray-100'
                      ]"
                    >
                      <img src="/images/bank-check.png" alt="Cheque" class="w-12" />
                      <span class="mt-1 text-sm md:text-base">Cheque</span>
                    </div>
                  </div>
                </div>

                <!-- Confirm -->
                <div class="w-full">
                  <button
  ref="confirmButtonRef"
  @click="selectedOrder?.order_id ? updateOrder() : submitOrder()"
  type="button"
  :disabled="products.length === 0 && services.length === 0"
  :class="[
    'w-full bg-black py-3 text-xl font-bold tracking-wide text-white uppercase rounded-lg transition flex items-center justify-center gap-3',
    (products.length === 0 && services.length === 0)
      ? 'opacity-50 cursor-not-allowed'
      : 'hover:bg-gray-800 focus:bg-blue-600'
  ]"
  tabindex="2"
>
  <i class="ri-add-circle-fill"></i>
  {{ selectedOrder?.order_id ? 'Update Order' : 'Confirm Order' }}
</button>

                </div>
              </aside>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modals -->
    <PosSuccessModel
      :open="isSuccessModalOpen"
      @update:open="handleModalOpenUpdate"
      :products="products.map((item, index) => ({
        ...item,
        returnReason: selectedOrder?.sale_items?.[index]?.return_reason?.reason || 'No Reason'
      }))"
      :employee="selectedEmployee"
      :cashier="loggedInUser"
      :customer="customer"
      :orderid="orderid"
      :cash="cash"
      :isWholesale="isWholesale"
      :credit_bill="credit_bill"
      :balance="balance"
      :subTotal="subtotal"
      :totalDiscount="totalDiscount"
      :total="total"
      :custom_discount_type="custom_discount_type"
      :custom_discount="custom_discount"
      :return_date="selectedOrder?.return_date || ''"
      :is_return_bill="selectedOrder?.is_return_bill || 0"
      :guide_pending="guide_pending"
      :guide_comi="guide_comi"
      :guide_cash="guide_cash"
      :guide_name="guide_name"
      :paymentMethod="selectedPaymentMethod"
    />

    <ServiceQuickModal
      v-model:open="isServiceModalOpen"
      :defaultCustomerId="null"
      :defaultEmployeeId="employee_id"
      :orderId="orderid"
      @saved="onServiceSaved"
    />

    <AlertModel v-model:open="isAlertModalOpen" :message="message" />

    <SelectProductModel
      v-model:open="isSelectModalOpen"
      :allcategories="allcategories"
      :colors="colors"
      :sizes="sizes"
      @selected-products="handleSelectedProducts"
    />

    <ExpenseCreateModal v-model:open="isCreateModalOpen" />

  <template v-if="isReturnBillsModalOpen">
     <!-- Return Bill Form -->
     <div class="fixed inset-0 z-50 flex items-start justify-center bg-black bg-opacity-30 overflow-auto">
        <div class="bg-white mt-20 p-20 rounded-lg shadow-lg w-full max-w-7xl">
                   <!-- <form @submit.prevent="submitReturnBill"> -->
                    <div error v-if="errorMessage" class="mb-4 flex items-center justify-between text-red-600 font-medium text-center text-2xl bg-red-100 rounded-lg border border-black-300 px-4 py-2">
                    {{ errorMessage }}<button type="button" class=" text-black text-align:right" @click="errorMessage=''">X</button>
                    </div>
                    <div class="space-y-6">
                        <!-- Order Code Dropdown -->
                        <div class="flex flex-col">
                            <label for="order_id" class="text-xl font-medium text-gray-700">Order Code</label>
                           <select
  id="order_id"
  v-model="ReturnbillForm.order_id"
  class="mt-2 p-4 border border-gray-300 rounded-lg text-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
  required
>
  <option value="" disabled>Select an order</option>
  <option
    v-for="sale in props.sales"
    :key="sale.id"
    :value="sale.id"
  >
    {{ sale.order_id }} — {{ sale.customer ? sale.customer.name : 'No Customer' }}
  </option>
</select>

                            <p v-if="ReturnbillForm.errors.order_id" class="text-red-500 text-sm mt-1">{{ ReturnbillForm.errors.order_id }}
                            </p>
                        </div>
                        <!-- Display Selected Order Details -->
                        <div v-if="selectedSale" class="mt-6 p-4 border rounded-lg bg-gray-50">
                            <p class="text-lg font-medium">Selected Order Details:</p>
                            <div class="mt-4 space-y-2">
                                <!-- <p><span class="font-bold"> ID:</span> {{ selectedSale.id }}</p> -->
                                <p><span class="font-bold">Order ID:</span> {{ selectedSale.order_id }}</p>
                                <p><span class="font-bold">Customer Name:</span> {{ selectedSale?.customer?.name ||
                                    'N/A' }}</p>

                                <p><span class="font-bold">Total Amount:</span> {{ selectedSale.total_amount }}</p>
                                <p><span class="font-bold">Discount:</span> {{ selectedSale.discount }}</p>
                                <p><span class="font-bold">Payment Method:</span> {{ selectedSale.payment_method }}</p>
                                <p><span class="font-bold">Sale Date:</span> {{ selectedSale.sale_date }}</p>
                            </div>
                        </div>
                        <!-- Display Sale Items -->
                        <div v-if="filteredSaleItems.length" class="mt-6 p-4 border rounded-lg bg-gray-50">
                            <p class="text-lg font-medium">Items in this Sale:</p>
                            <div class="mt-4 max-h-96 overflow-y-auto">
                            <table class="mt-4 w-full border-collapse border border-gray-200">
                                <thead>
                                    <tr>
                                        <th class="border border-gray-300 px-4 py-2">Product ID</th>
                                        <th class="border border-gray-300 px-8 py-4">Quantity</th>
                                        <th class="border border-gray-300 px-4 py-2">Unit Price</th>
                                        <th class="border border-gray-300 px-4 py-2">Total Price</th>
                                        <th class="border border-gray-300 px-4 py-2">Reason</th>
                                        <th class="border border-gray-300 px-4 py-2">Return Date</th>
                                        <th class="border border-gray-300 px-4 py-2">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in filteredSaleItems" :key="item.id">
                                        <td class="border border-gray-300 px-4 py-2">
                                            <div class="pb-2"> {{ item.product.name }}</div>
                                            <img :src="item.product.image ? `/${item.product.image}` : '/images/placeholder.jpg'"
                                                alt="Product Image" class="w-20 h-20 object-cover rounded-lg" />
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2"><div class="flex items-center space-x-2"><p @click="incrementReturnQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-add-line"></i>
                                        </p><span class="px-2">{{ item.quantity }}</span><p @click="decrementReturnQuantity(item.id)"
                                            class="flex items-center justify-center w-8 h-8 text-white bg-black rounded cursor-pointer">
                                            <i class="ri-subtract-line"></i>
                                        </p></div></td>
                                        <td class="border border-gray-300 px-4 py-2">{{ item.unit_price }}</td>
                                        <td class="border border-gray-300 px-4 py-2">{{ item.total_price }}</td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <textarea v-model="item.reason" placeholder="Enter reason for return"
                                                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <input v-model="item.return_date" type="date"
                                                class="w-full p-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                                        </td>
                                        <td class="border border-gray-300 px-4 py-2">
                                            <button @click="removeItem(index)" class="text-red-500 hover:text-red-700">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" class="w-6 h-6">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            </div>
                        </div>
                        <!-- Submit Button -->
                         
                        <div class="flex justify-center gap-between w-full space-x-8">
                            <button
                                @click="handleReturnBillSave"
                                class="px-8 py-3 text-lg font-bold tracking-wider text-white uppercase bg-blue-600 rounded-xl hover:bg-blue-700 focus:outline-none"
                            >
                                Save
                            </button>

                            <button type="button" @click="isReturnBillsModalOpen = false"
                                    class="px-8 py-3 text-lg font-bold tracking-wider text-gray-700 uppercase bg-gray-300 rounded-xl hover:bg-gray-400 focus:outline-none">
                                    Cancel
                            </button>
                        </div>
                    </div>
                <!-- </form> -->
            </div>
     </div>
    </template>


    <!-- Customer/Orders Modal -->
    <template v-if="isModalOpen">
      <div class="fixed inset-0 bg-black/70 flex justify-center items-center z-50">
        <!-- Modal Container -->
        <div
          class="bg-slate-950 rounded-2xl w-full max-w-xl relative shadow-2xl max-h-[90vh] overflow-y-auto p-8 animate-fadeIn"
          ref="customerModalRef"
          tabindex="0"
          @keydown.capture="onCustomerModalKeydown"
        >
          <!-- Close Button -->
          <button
            @click="isModalOpen = false"
            class="absolute top-3 right-4 text-gray-500 hover:text-red-500 transition text-3xl font-bold leading-none"
            aria-label="Close">
            &times;
          </button>

          <!-- Header -->
          <div class="mb-6 text-center">
            <h2 class="text-3xl font-normal text-white">Customer Details</h2>
          </div>

          <!-- Form -->
          <div class="space-y-5">
            <!-- Name -->
            <input
              ref="customerNameRef"
              v-model="customer.name"
              type="text"
              placeholder="Enter Customer Name"
              class="w-full px-4 py-3 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
            />

            <!-- Contact Number -->
            <input
              ref="customerPhoneRef"
              v-model="customer.contactNumber"
              type="text"
              placeholder="Enter Customer Contact Number"
              class="w-full px-4 py-3 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
            />

            <!-- Email -->
            <input
              ref="customerEmailRef"
              v-model="customer.email"
              type="email"
              placeholder="Enter Customer Email"
              class="w-full px-4 py-3 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
            />

            <!-- Address -->
            <input
              ref="customerAddressRef"
              v-model="customer.address"
              type="text"
              placeholder="Enter Customer Address"
              class="w-full px-4 py-3 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
            />

            <!-- Employee -->
            <select
              ref="customerEmployeeRef"
              required
              v-model="employee_id"
              id="employee_id"
              class="w-full px-4 py-3 text-gray-900 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-lg"
            >
              <option value="" disabled>Select an Employee</option>
              <option v-for="employee in allemployee" :key="employee.id" :value="employee.id">
                {{ employee.name }}
              </option>
            </select>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end gap-3 mt-8">
            <button
              @click="isModalOpen = false"
              class="px-6 py-3 text-lg font-semibold text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300 transition">
              Cancel
            </button>
            <button
              ref="customerSaveBtnRef"
              @click="addCustomerDetails"
              class="px-6 py-3 text-lg font-bold tracking-wider text-white uppercase bg-blue-600 rounded-lg hover:bg-blue-700 transition focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Save
            </button>
          </div>
        </div>
      </div>
    </template>
    <!-- /Customer/Orders Modal -->
  </div>


  
</template>




<script setup>
import { Head, Link, useForm, router } from "@inertiajs/vue3";
import { ref, onMounted, computed, watch, onUnmounted, nextTick , onBeforeUnmount } from "vue";
import axios from "axios";

import PosSuccessModel from "@/Components/custom/PosSuccessModel.vue";
import AlertModel from "@/Components/custom/AlertModel.vue";
import CurrencyInput from "@/Components/custom/CurrencyInput.vue";
import SelectProductModel from "@/Components/custom/SelectProductModel.vue";
import ExpenseCreateModal from "@/Components/custom/ExpenseCreateModal.vue";
import ServiceQuickModal from "@/Components/custom/ServiceQuickModal.vue";

import { generateOrderId } from "@/Utils/Other.js";

const props = defineProps({
  loggedInUser: Object,
  allcategories: Array,
  allemployee: Array,
  colors: Array,
  sizes: Array,
  returnReasons: Array,
  sales:Array,
    saleItems: { // Add this prop
        type: Array,
        default: () => []
    },
});

const products = ref([]);

// Helper: get original price (per item)
function getOriginalPrice(item) {
  return isWholesale.value ? parseFloat(item.whole_price) : parseFloat(item.selling_price);
}

// Helper: get discounted price (per item)
function getDiscountedPrice(item) {
  const price = getOriginalPrice(item);
  if (item.discount_value && item.discount_type) {
    if (item.discount_type === 'rs') {
      return Math.max(price - parseFloat(item.discount_value), 0);
    } else if (item.discount_type === 'percent') {
      return Math.max(price - (price * parseFloat(item.discount_value) / 100), 0);
    }
  }
  return price;
}
const services = ref([]); 
const isSuccessModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const message = ref("");
const appliedCoupon = ref(null);
const errorMessage = ref("");
const cash = ref(0);
const custom_discount = ref(0);
const custom_discount_type = ref("fixed");

const isSelectModalOpen = ref(false);
const isModalOpen = ref(false);
const isWholesale = ref(false);
const isReturnBillsModalOpen = ref(false);
const orderid = computed(() => generateOrderId());

const isReturnMode = ref(false);
const isReturnBill = ref(false);
const selectedOrder = ref(null);
const isSubmitting = ref(false);

const guide_name = ref("");
const guide_comi = ref(0);
const guide_cash = ref(0);
const guide_pending = ref(false);

const credit_bill = ref(false);

const cheque_number = ref("");
const bank_name = ref("");
const cheque_date = ref("");
const cheque_amount = ref("");
const cheque_notes = ref("");

const cashInputRef = ref(null);
const confirmButtonRef = ref(null);
const shouldFocusCashNext = ref(false);
const customDiscountRef = ref(null);
const customDiscountTypeRef = ref(null);
const couponRef = ref(null);
const barcodeInputRef = ref(null);
const asideRef = ref(null);

const isCreateModalOpen = ref(false);
const isServiceModalOpen = ref(false);

const onServiceSaved = (data) => {
  isAlertModalOpen.value = true;
  message.value = `Service saved successfully (Sale #${data?.sale_id ?? ""})`;
};

// Safe wrapper in case HasRole isn't globally available
const hasRole = (roles) => (typeof HasRole === "function" ? HasRole(roles) : false);
const isAdmin = computed(() => hasRole(["Admin"]));

const openExpenseCreate = () => {
  if (!("HasRole" in globalThis) || isAdmin.value) isCreateModalOpen.value = true;
};



const removeItem = (index) => {
    saleItemsState.value.splice(index, 1);
};



function toggleEditService(index) {
  const service = services.value[index];
  if (service.isEditing) {
    if (!service.service_name?.trim()) {
      alert("Service name cannot be empty");
      return;
    }
    if (service.price === null || service.price < 0) {
      alert("Service price must be a positive number");
      return;
    }
  }
  service.isEditing = !service.isEditing;
}
function removeService(index) {
  services.value.splice(index, 1);
}

const handleModalOpenUpdate = (newValue) => {
  isSuccessModalOpen.value = newValue;
  if (!newValue) refreshData();
};
const addCustomerDetails = () => {
  isModalOpen.value = false;
};
const selectedSale = ref(null);
const discount = ref(0);
const customer = ref({
  name: "",
  countryCode: "",
  contactNumber: "",
  email: "",
  address: "",
});

 




const employee_id = ref("");
const selectedEmployee = computed(() =>
  (props.allemployee || []).find((e) => e.id === employee_id.value) || null
);

const selectedPaymentMethod = ref("cash");

const refreshData = () => {
  router.visit(route("pos.index"), { preserveScroll: false, preserveState: false });
};
const removeProduct = (id) => {
  products.value = products.value.filter((item) => item.id !== id);
};
const removeCoupon = () => {
  appliedCoupon.value = null;
  couponForm.code = "";
};
const incrementQuantity = (id) => {
  const product = products.value.find((item) => item.id === id);
  if (product) {
    product.quantity += 1;
  }
};
const decrementQuantity = (id) => {
  const product = products.value.find((item) => item.id === id);
  if (product && product.quantity > 1) {
    product.quantity -= 1;
  }
};

const logout = async () => {
  try {
    await axios.post(route?.("logout") ?? "/logout");
    router.visit("/");
  } catch {
    router.visit("/");
  }
};

const focusBarcodeField = () => {
  nextTick(() => {
    const el = barcodeInputRef.value;
    if (el) {
      el.focus();
      el.select?.();
    }
  });
};

const submitOrder = async () => {
  if (isSubmitting.value) return;
  isSubmitting.value = true;

  if (parseFloat(balance.value) < 0) {
    isAlertModalOpen.value = true;
    message.value = "Cash is not enough";
    isSubmitting.value = false;
    return;
  }

  if (selectedPaymentMethod.value === 'online') {
    if (
      !cheque_number.value ||
      !bank_name.value ||
      !cheque_date.value ||
      !cheque_amount.value
    ) {
      isAlertModalOpen.value = true;
      message.value = "Please fill in all cheque fields (number, bank, date, amount)";
      isSubmitting.value = false;
      return;
    }
  }

  try {

     const returnItemsData = saleItemsState.value.map(item => ({
            sale_id: ReturnbillForm.order_id,
            customer_id: selectedSale.value?.customer_id || null,
            product_id: item.product_id,
            quantity: item.quantity,
            reason: item.reason,
            unit_price: item.unit_price,    // Make sure this is included
            return_date: item.return_date || new Date().toISOString().split('T')[0], // Default to today if not provided
        }));


    const payload = {
      customer: customer.value,
      products: products.value,
      services: services.value,
      employee_id: employee_id.value,
      paymentMethod: selectedPaymentMethod.value,
      userId: props.loggedInUser?.id,
      orderid: orderid.value,
      cash: cash.value,
      custom_discount: custom_discount.value,
      custom_discount_type: custom_discount_type.value,
      isWholesale: isWholesale.value ? 1 : 0,
      guide_name: guide_name.value,
      guide_comi: guide_comi.value,
      guide_cash: guide_cash.value,
      total_to_include_guide: total_to_include_guide.value,
      credit_bill: credit_bill.value,
      guide_pending: guide_pending.value ? 1 : 0,
      return_items: returnItemsData,
    };

    if (selectedPaymentMethod.value === "online") {
      payload.online = {
        cheque_number: cheque_number.value,
        bank_name: bank_name.value,
        cheque_date: cheque_date.value,
        amount: cheque_amount.value,
        notes: cheque_notes.value || "",
      };
    }

    const response = await axios.post("/pos/submit", payload);
    console.log("Order submitted successfully:", response.data);
    isSuccessModalOpen.value = true;
  } catch (error) {
    if (error.response?.status === 423) {
      isAlertModalOpen.value = true;
      message.value = error.response.data.message;
    } else {
      isAlertModalOpen.value = true;
      message.value = "An error occurred while submitting the order.";
    }
    console.error("Order submission error:", error);
  } finally {
    isSubmitting.value = false;
  }
};


const handleReturnBillSave = () => {
    const missingReason = filteredSaleItems.value.some(item => !item.reason.trim());
    if(missingReason){
     errorMessage.value = "Please provide a reason for all return items";
    return;
    }
    errorMessage.value = "";
    isReturnBillsModalOpen.value = false
   
   
};   
    


const openReturnBills = () => {
    isReturnBillsModalOpen.value = true;
};
const ReturnbillForm = useForm({
    order_id:"",
    reason:"",
    return_date:"",
    
});

const filteredSaleItems = computed(() => {
    if (!props.saleItems || !Array.isArray(props.saleItems)) {
        return [];
    }
    
    const items = props.saleItems.filter((item) => item.sale_id === ReturnbillForm.order_id);
    saleItemsState.value = items.map((item) => ({ ...item, reason: "", return_date: "" }));
    return saleItemsState.value;
});

const returnBillTotal = computed(() => {
    if (!filteredSaleItems.value.length) return 0;
    return filteredSaleItems.value.reduce((sum, item) => {
        return sum + (parseFloat(item.total_price) || 0);
    }, 0);
});


const updateOrder = async () => {
  if (!selectedOrder.value?.order_id) {
    isAlertModalOpen.value = true;
    message.value = "No order selected. Please select an order first.";
    return;
  }

  if (isReturnBill.value) {
    const returningProducts = products.value.filter((p) => p.quantity < p.pivot?.quantity);
    const missingReasons = returningProducts.filter(
      (p) => !p.returnReason && p.quantity < p.pivot?.quantity
    );
    if (missingReasons.length > 0) {
      isAlertModalOpen.value = true;
      message.value = "Please select a return reason for all returned products.";
      return;
    }
  }

  try {
    const response = await axios.put(`/pos/update/${selectedOrder.value.order_id}`, {
      customer: customer.value,
      products: products.value,
      services: services.value,
      employee_id: employee_id.value,
      paymentMethod: selectedPaymentMethod.value,
      cash: cash.value,
      custom_discount: custom_discount.value,
      custom_discount_type: custom_discount_type.value,
      appliedCoupon: appliedCoupon.value,
      returnBill: isReturnBill.value,
      isWholesale: isWholesale.value,
    });

    console.log("Order updated successfully:", response.data);
    isSuccessModalOpen.value = true;
  } catch (error) {
    console.error("Error updating order:", error);
    isAlertModalOpen.value = true;
    message.value = error.response?.data?.message || "Failed to update order. Please try again.";
  }
};

const subtotal = computed(() => {
  const productTotal = products.value.reduce((t, item) => {
    const price = getDiscountedPrice(item);
    return t + price * (Number(item.quantity) || 0);
  }, 0);

  const serviceTotal = services.value.reduce((t, s) => {
    const price = parseFloat(s.price || 0);
    const quantity = s.quantity || 1;
    return t + price * quantity;
  }, 0);

  return (productTotal + serviceTotal).toFixed(2);
});

const totalDiscount = computed(() => {
  const productDiscount = products.value.reduce((t, item) => {
    if (item.discount && item.discount > 0 && item.apply_discount === true) {
      const discountAmount =
        (parseFloat(item.selling_price) - parseFloat(item.discounted_price)) *
        (Number(item.quantity) || 0);
      return t + discountAmount;
    }
    return t;
  }, 0);

  const couponDiscount = appliedCoupon.value ? Number(appliedCoupon.value.discount) : 0;
  const orderLevelDiscount = discount.value ? parseFloat(discount.value) : 0;

  return (productDiscount + couponDiscount + orderLevelDiscount).toFixed(2);
});

const validateCustomDiscount = () => {
  if (!custom_discount.value || isNaN(custom_discount.value)) {
    custom_discount.value = 0;
  }
};

const total = computed(() => {
  const subtotalValue = parseFloat(subtotal.value) || 0;
  const discountValue = parseFloat(totalDiscount.value) || 0;
  const customDiscount = parseFloat(custom_discount.value) || 0;
  const returnAmount = parseFloat(returnBillTotal.value) || 0;

  let customValue = 0;
  if (custom_discount_type.value === "percent") {
    customValue = (subtotalValue * customDiscount) / 100;
  } else if (custom_discount_type.value === "fixed") {
    customValue = customDiscount;
  }

  return (subtotalValue - discountValue - customValue - returnAmount).toFixed(2);
});

const total_to_include_guide = computed(() => {
  const totalWithGuide = parseFloat(total.value) || 0;
  const guideCash = parseFloat(guide_cash.value) || 0;
  return (totalWithGuide - guideCash).toFixed(2);
});

watch([guide_comi, subtotal], ([newComi, newSubtotal]) => {
  const commissionRate = parseFloat(newComi) || 0;
  const sub = parseFloat(newSubtotal) || 0;
  guide_cash.value = ((commissionRate / 100) * sub).toFixed(2);
});

const balance = computed(() => {
  if (cash.value == null || Number(cash.value) === 0) return 0;
  return (parseFloat(cash.value) - parseFloat(total.value)).toFixed(2);
});

const form = useForm({
  employee_id: "",
  barcode: "",
});

const couponForm = useForm({
  code: "",
});

const submitCoupon = async () => {
  try {
    const response = await axios.post(route("pos.getCoupon"), { code: couponForm.code });
    const { coupon: fetchedCoupon, error: fetchedError } = response.data;

    if (fetchedCoupon) {
      appliedCoupon.value = fetchedCoupon;
      products.value.forEach((p) => (p.apply_discount = false));
    } else {
      isAlertModalOpen.value = true;
      message.value = fetchedError;
    }
  } catch (err) {
    if (err.response?.status === 422) {
      isAlertModalOpen.value = true;
      message.value = err.response.data.message;
    }
  }
};

const submitBarcode = async () => {
  try {
    const response = await axios.post(route("pos.getProduct"), { barcode: form.barcode });
    const { product: fetchedProduct, error: fetchedError } = response.data;

    if (fetchedProduct) {
      if (fetchedProduct.stock_quantity < 1) {
        isAlertModalOpen.value = true;
        message.value = "Product is out of stock";
        form.barcode = "";
        focusBarcodeField();
        return;
      }

      const existing = products.value.find((i) => i.id === fetchedProduct.id);
      if (existing) existing.quantity += 1;
      else
        products.value.push({
          ...fetchedProduct,
          quantity: 1,
          apply_discount: false,
        });

      form.barcode = "";
      focusBarcodeField();
    } else {
      isAlertModalOpen.value = true;
      message.value = fetchedError;
      form.barcode = "";
      focusBarcodeField();
    }
  } catch (err) {
    if (err.response?.status === 422) {
      isAlertModalOpen.value = true;
      message.value = err.response.data.message;
    } else {
      isAlertModalOpen.value = true;
      message.value = "An unexpected error occurred. Please try again.";
    }
    form.barcode = "";
    focusBarcodeField();
  }
};

// Scanner buffer via keydown (no double typing)
let lastKeyTime = 0;
let scanBuffer = "";
let scanTimer = null;

const isChar = (e) => e.key.length === 1 && !e.ctrlKey && !e.metaKey && !e.altKey;

const clearScanBuffer = () => {
  scanBuffer = "";
  if (scanTimer) {
    clearTimeout(scanTimer);
    scanTimer = null;
  }
};


// Handle input from the barcode scanner
const handleScannerInput = (event) => {
    clearTimeout(timeout); // Clear the timeout for each keypress
    if (event.key === "Enter") {
        // Barcode scanning completed
        form.barcode = barcode; // Set the scanned barcode into the form
        submitBarcode(); // Automatically submit the barcode
        barcode = ""; // Reset the barcode for the next scan
    } else {
        // Append the pressed key to the barcode
        barcode += event.key;
    }

    // Timeout to reset the barcode if scanning is interrupted
    timeout = setTimeout(() => {
        barcode = "";
    }, 100); // Adjust timeout based on scanner speed
};


onMounted(async() => {
    document.addEventListener("keypress", handleScannerInput);

    try{
        const response = await axios.get('/pos');
        sales.value = response.data.sales || [];
    }
    catch(error){
        console.error("Error fetching sales:", error);
        sales.value = [];
    }
    
});


const handleScannerKeydown = (e) => {
  const active = document.activeElement;
  const inBarcode = active === barcodeInputRef.value;

  // If Enter is pressed while cursor is inside the barcode field,
  // let the input's own @keydown.enter handler do the submit (avoid double).
  if (e.key === "Enter" && inBarcode) return;

  // Detect fast scanner bursts
  const now = Date.now();
  const isScannerBurst = now - lastKeyTime < 35;
  lastKeyTime = now;

  if (isChar(e)) {
    if (inBarcode) {
      // When focused in barcode input, let the browser type normally
      return;
    }
    if (isScannerBurst) {
      if (!barcodeInputRef.value) return;
      barcodeInputRef.value.focus();
      scanBuffer += e.key;
      e.preventDefault();
      if (scanTimer) clearTimeout(scanTimer);
      scanTimer = setTimeout(clearScanBuffer, 120);
    }
    return;
  }

  if (e.key === "Enter") {
    if (scanBuffer.trim() !== "") {
      form.barcode = scanBuffer;
      clearScanBuffer();
      submitBarcode();
      e.preventDefault();
    }
  }
};

const sales = ref([]);
const saleItemsState = ref([]);



const incrementReturnQuantity = (id) => {
    const item = saleItemsState.value.find((item) => item.id === id);
    if (item) {
        item.quantity += 1;
        item.total_price = item.unit_price * item.quantity; // Update total price
    }
};

const decrementReturnQuantity = (id) => {
    const item = saleItemsState.value.find((item) => item.id === id);
    if (item && item.quantity > 1) {
        item.quantity -= 1;
        item.total_price = item.unit_price * item.quantity; // Update total price
    }
};
 


const focusOnCashField = () => {
  nextTick(() => {
    const el = cashInputRef.value?.$el?.querySelector("input");
    if (el) el.focus();
  });
};

const focusOnCustomDiscount = () => {
  nextTick(() => {
    const el = customDiscountRef.value?.$el?.querySelector("input");
    if (el) {
      el.focus();
      el.select?.();
    }
  });
};

const applyDiscount = (id) => {
  products.value.forEach((p) => {
    if (p.id === id) p.apply_discount = true;
  });
};
const removeDiscount = (id) => {
  products.value.forEach((p) => {
    if (p.id === id) p.apply_discount = false;
  });
};

const handleSelectedProducts = (selectedProducts) => {
  selectedProducts.forEach((fetchedProduct) => {
    const existing = products.value.find((i) => i.id === fetchedProduct.id);
    if (existing) existing.quantity += 1;
    else
      products.value.push({
        ...fetchedProduct,
        quantity: 1,
        apply_discount: false,
      });
  });

  focusBarcodeField();
};

const handleKeyDown = (event) => {
  if (event.key === "F1") {
    event.preventDefault();
    isServiceModalOpen.value = true;
  }
  if (event.key === "F2") {
    event.preventDefault();
    openExpenseCreate();
  }
  if (event.key === "F3") {
    event.preventDefault();
    isSelectModalOpen.value = true;
  }
  if (event.key === "F4") {
    event.preventDefault();
    isModalOpen.value = true;
  }

   if (event.key === "F5") {
    event.preventDefault();
    isReturnBillsModalOpen.value = true;
  }



if (event.key === "Shift") {
  event.preventDefault();
  nextTick(() => {
    asideRef.value?.focus?.();
    const first = asideFocusOrder.value?.[0];
    first?.focus?.();
    first?.select?.();
  });
}






};




const getFocusable = (r) => {
  const inner = r?.$el?.querySelector?.('input,select,textarea,button,[tabindex]');
  if (inner) return inner;
  if (r?.nodeType === 1) return r;
  return r?.$el || r;
};


const asideFocusOrder = computed(() => {
  return [
    () => getFocusable(customDiscountRef.value), // Custom Discount
    () => getFocusable(cashInputRef.value),      // Cash
    () => getFocusable(confirmButtonRef.value),  // Confirm Order
  ].map((fn) => fn()).filter(Boolean);
});









function focusAsideIndex(i) {
  const list = asideFocusOrder.value;
  if (!list.length) return;
  const idx = (i + list.length) % list.length;
  const el = list[idx];
  el?.focus?.();
  el?.select?.();
}

function currentAsideIndex() {
  const list = asideFocusOrder.value;
  const active = document.activeElement;
  return list.findIndex((node) => node === active || node?.contains?.(active));
}

function onAsideKeydown(e) {
  if (e.key === "ArrowDown" || e.key === "Down") {
    e.preventDefault();
    const i = currentAsideIndex();
    focusAsideIndex((i === -1 ? 0 : i) + 1);
    return;
  }
  if (e.key === "ArrowUp" || e.key === "Up") {
    e.preventDefault();
    const i = currentAsideIndex();
    focusAsideIndex((i === -1 ? 0 : i) - 1);
    return;
  }
}

onMounted(() => {
  barcodeInputRef.value?.focus();
  window.addEventListener("keydown", handleKeyDown);
  window.addEventListener("keydown", handleScannerKeydown); // scanner hook
});

onBeforeUnmount(() => {
  window.removeEventListener("keydown", handleKeyDown);
  window.removeEventListener("keydown", handleScannerKeydown);
});

const resetToLiveBill = () => {
  selectedOrder.value = null;
  products.value = [];
  services.value = [];
  cash.value = 0;
  custom_discount.value = 0;
  custom_discount_type.value = 'fixed';
  discount.value = 0;
  isReturnBill.value = false;
};

// Fetch past order for return/update
const fetchreturn = async () => {
  isReturnMode.value = true;

  if (!form.barcode) {
    isAlertModalOpen.value = true;
    message.value = "Please enter an order ID";
    return;
  }

  try {
    const response = await axios.post(route("pos.getPastOrder"), { barcode: form.barcode });
    const { order, error } = response.data;

    if (order) {
      selectedOrder.value = order;

      cash.value = order.cash || 0;
      custom_discount.value = order.custom_discount || 0;
      discount.value = order.discount || 0;
      isReturnBill.value = order.is_return_bill == 1;

      if (Array.isArray(order.products)) {
        products.value = order.products.map((product) => ({
          ...product,
          quantity: product.pivot?.quantity || 1,
          selling_price: product.pivot?.price || product.selling_price,
          apply_discount: false,
          returnReason: product.pivot?.reason_id || null,
          pivot: product.pivot,
        }));
      } else {
        products.value = [];
      }

      if (Array.isArray(order.services)) {
        services.value = order.services.map((s) => ({
          ...s,
          quantity: s.quantity || 1,
          isEditing: false,
        }));
      }

      customer.value = {
        name: order.customer_name || "",
        contactNumber: order.customer_phone || "",
        email: order.customer_email || "",
        address: order.address || "",
      };

      employee_id.value = order.employee_id || "";

      const paymentMethod = order.payment_method || "cash";
      selectedPaymentMethod.value = paymentMethod.toLowerCase();

      if (order.cash) cash.value = parseFloat(String(order.cash).replace(/[^0-9.-]+/g, ""));
      if (order.custom_discount)
        custom_discount.value = parseFloat(String(order.custom_discount).replace(/[^0-9.-]+/g, ""));

      custom_discount_type.value = "fixed";
    } else {
      isAlertModalOpen.value = true;
      message.value = error || "Order not found";
    }
  } catch (err) {
    isAlertModalOpen.value = true;
    message.value =
      err.response?.data?.error || err.response?.data?.message || "Error loading past order";
  }
};

const hasReturnItems = computed(() => {
  if (!selectedOrder.value || !isReturnBill.value) return false;
  return products.value.some((p) => p.quantity < (p.pivot?.quantity || p.quantity));
});

const handleServicesSelected = (selected) => {
  selected.forEach((service) => {
    const existing = services.value.find((s) => s.service_name === service.service_name);
    if (existing) {
      existing.quantity = (existing.quantity || 1) + 1;
      existing.price = service.price;
    } else {
      services.value.push({ ...service, quantity: 1, isEditing: false });
    }
  });
};

// Refs for modal + fields
const customerModalRef = ref(null);
const customerNameRef = ref(null);
const customerPhoneRef = ref(null);
const customerEmailRef = ref(null);
const customerAddressRef = ref(null);
const customerEmployeeRef = ref(null);
const customerSaveBtnRef = ref(null);

// Order of focusable elements in the modal
const customerFocusOrder = computed(() => {
  return [
    customerNameRef.value,
    customerPhoneRef.value,
    customerEmailRef.value,
    customerAddressRef.value,
    customerEmployeeRef.value,
    customerSaveBtnRef.value,
  ].filter(Boolean);
});

function focusFieldByIndex(i) {
  const list = customerFocusOrder.value;
  if (!list.length) return;
  const idx = (i + list.length) % list.length;
  const el = list[idx];
  // Vue refs can be components (e.g., custom inputs); try common focus targets
  const target =
    el?.$el?.querySelector?.("input,select,textarea,button") ||
    el?.$el ||
    el;
  target?.focus?.();
  if (target?.select) target.select();
}

function currentFocusedIndex() {
  const list = customerFocusOrder.value;
  const active = document.activeElement;
  return list.findIndex((el) => {
    const node = el?.$el || el;
    return node === active || node?.contains?.(active);
  });
}

function onCustomerModalKeydown(e) {
  // Up/Down to navigate
  if (e.key === "ArrowDown" || e.key === "Down") {
    e.preventDefault();
    const i = currentFocusedIndex();
    focusFieldByIndex((i === -1 ? 0 : i) + 1);
    return;
  }
  if (e.key === "ArrowUp" || e.key === "Up") {
    e.preventDefault();
    const i = currentFocusedIndex();
    focusFieldByIndex((i === -1 ? 0 : i) - 1);
    return;
  }

  // Enter to Save (unless modifier keys)
  if (e.key === "Enter" && !e.shiftKey && !e.ctrlKey && !e.metaKey && !e.altKey) {
    e.preventDefault();
    addCustomerDetails();
  }
}

// When modal opens, focus first field
watch(

() => ReturnbillForm.order_id,
    (newValue) => {
        const sale = props.sales.find((sale) => sale.id === newValue) || null;
        if (sale) {
            ReturnbillForm.discount = sale.discount || 0;
        } else {
            ReturnbillForm.discount = 0; // Default if no sale is found
        }
        console.log(sale);
    },

  () => isModalOpen.value,
  async (open) => {
    if (open) {
      await nextTick();
      // Bring keyboard focus into the modal container first (for key handling)
      customerModalRef.value?.focus?.();
      // Then focus the very first input
      focusFieldByIndex(0);
    }
  }
);

// Refocus back to barcode when all modals are closed
watch([isSelectModalOpen, isModalOpen, isCreateModalOpen, isServiceModalOpen, isSuccessModalOpen], ([a,b,c,d,e]) => {
  if (!a && !b && !c && !d && !e) focusBarcodeField();
});

watch(isReturnBill, (newVal) => {
  if (newVal && selectedOrder.value) {
    products.value.forEach((p) => {
      if (!p.returnReason) p.returnReason = "";
    });
  }
});
</script>

<style scoped>
@keyframes scan {
  0% {
    top: 0;
  }
  50% {
    top: 100%;
  }
  100% {
    top: 0;
  }
}

.animate-scan {
  animation: scan 2s ease-in-out infinite;
}
</style>
