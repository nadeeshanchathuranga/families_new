<style>
/* General DataTables Pagination Container Style */
.dataTables_wrapper .dataTables_paginate {
  display: flex;
  justify-content: center;
  align-items: center;
  margin-top: 20px;
}

/* Style the filter container */
#TransitionTable_filter {
  display: flex;
  justify-content: flex-end;
  align-items: center;
  margin-bottom: 16px; /* Add spacing below the filter */
}

/* Style the label and input field inside the filter */
#TransitionTable_filter label {
  font-size: 17px;
  color: #000000; /* Match text color of the table header */
  display: flex;
  align-items: center;
}

/* Style the input field */
#TransitionTable_filter input[type="search"] {
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
#TransitionTable_filter input[type="search"]:focus {
  outline: none; /* Removes the default outline */
  border: 1px solid #4b5563;
  box-shadow: none; /* Removes any focus box-shadow */
}

#TransitionTable_filter {
  float: left;
}

.dataTables_wrapper {
  margin-bottom: 10px;
}
</style>

<template>
  <Head title="Reports" />
  <Banner />

  <div class="flex flex-col items-center justify-start min-h-screen py-8 space-y-8 bg-gray-100 md:px-36 px-16">
    <Header />

    <!-- Page header + date filters -->
    <div class="w-full py-12 space-y-16">
      <div class="flex md:flex-row flex-col md:items-center items-start justify-between md:space-y-0 space-y-4">
        <div class="flex items-center justify-center space-x-4">
          <Link href="/"><img src="/images/back-arrow.png" class="w-14 h-14" /></Link>
          <p class="text-4xl font-bold tracking-wide text-black uppercase">Reports</p>
        </div>

        <div date-rangepicker class="flex items-center space-x-4">
          <div class="relative">
            <input v-model="startDate" type="date"
              class="text-xl font-normal tracking-wider text-blue-600 bg-white border border-blue-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
              placeholder="Start Date" />
          </div>
          <span class="text-xl font-bold tracking-wider text-blue-600">To</span>
          <div class="relative">
            <input v-model="endDate" type="date"
              class="text-xl font-normal tracking-wider text-blue-600 bg-white border border-blue-300 rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5"
              placeholder="End Date" />
          </div>

          <!-- IMPORTANT: DO NOT PRESERVE STATE -->
          <button @click="filterData"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select hidden sm:inline-block">
            Filter
          </button>
          <Link href="/reports"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select hidden sm:inline-block">
            Reset
          </Link>
        </div>

        <div class="w-full flex justify-center items-center space-x-4 md:hidden">
          <button @click="filterData"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select">
            Filter
          </button>
          <Link href="/reports"
            class="px-6 py-3 text-xl font-normal tracking-wider text-white text-center bg-blue-600 rounded-lg custom-select">
            Reset
          </Link>
        </div>
      </div>
    </div>

    <!-- Stat boxes -->





<!-- KPI Cards (use the same totals the table uses) -->
<div class="grid w-full md:grid-cols-5 grid-cols-3 gap-4 mb-4">
  <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC6116] w-full space-y-4 rounded-2xl bg-[#EC611666] shadow-lg hover:-translate-y-1 transition">
    <div class="flex flex-col items-center justify-center">
      <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Sales</h2>
      <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Amount</h2>
    </div>
    <p class="text-2xl font-bold text-black">     {{ salesGrossTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR </p>
  </div>

  <div class="py-6 flex flex-col justify-center items-center border-2 border-[#488D3F] w-full space-y-8 rounded-2xl bg-[#488D3F66] shadow-lg hover:-translate-y-1 transition">
    <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Net Profit</h2>
    <p class="text-2xl font-bold text-black">{{ toMoney(salesProfitTotal) }} LKR</p>
  </div>

  <div class="py-6 flex flex-col justify-center items-center border-2 border-[#16D0EC] w-full space-y-4 rounded-2xl bg-[#16D0EC66] shadow-lg hover:-translate-y-1 transition">
    <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Discount</h2>
    <p class="text-2xl font-bold text-black">{{ toMoney(salesDiscountTotal) }} LKR</p>
  </div>



  <div class="py-6 flex flex-col justify-center items-center border-2 border-[#9E16EC] w-full space-y-4 rounded-2xl bg-[#9E16EC66] shadow-lg hover:-translate-y-1 transition">
    <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Number of Transactions</h2>
    <p class="text-2xl font-bold text-black">{{ totalTransactions }}</p>
  </div>

  <div class="py-6 flex flex-col justify-center items-center border-2 border-[#EC16D7] w-full space-y-4 rounded-2xl bg-[#EC16D766] shadow-lg hover:-translate-y-1 transition">
    <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Number of Customers</h2>
    <p class="text-2xl font-bold text-black">{{ totalCustomer }}</p>
  </div>
</div>











    <!-- Inventory totals -->
    <div class="grid w-full md:grid-cols-3 grid-cols-2 gap-8">
      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#ffb224] w-full space-y-4 rounded-2xl bg-[#ffb224] shadow-lg">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Quantity In Stock:</h2>
        <p class="text-2xl font-bold text-black">{{ totalQty }} QTY</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#41ec16] w-full space-y-4 rounded-2xl bg-[#41ec16] shadow-lg">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Selling Price In Stock:</h2>
        <p class="text-2xl font-bold text-black">{{ totalSellingPrice.toFixed(2) }} LKR</p>
      </div>

      <div class="py-6 flex flex-col justify-center items-center border-2 border-[#3e41ff] w-full space-y-4 rounded-2xl bg-[#3e41ff] shadow-lg">
        <h2 class="text-xl font-extrabold tracking-wide text-black uppercase">Total Cost Price In Stock:</h2>
        <p class="text-2xl font-bold text-black">{{ totalRetailValue.toFixed(2) }} LKR</p>
      </div>
    </div>

    <!-- Charts -->
    <div class="flex md:flex-row flex-col items-center justify-center w-full h-full md:space-x-4 md:space-y-0 space-y-4">
      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center pb-8">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Top Employee Sales</h2>
            <button @click="downloadPDF" class="w-full mt-6 px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <div class="w-full h-full flex justify-center items-center">
            <Doughnut :data="chartData4" :options="chartOptions4" />
          </div>
        </div>
      </div>

      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-12">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Product</h2>
            <button @click="downloadPDF2" class="w-full mt-6 px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <Pie :data="chartData" :options="chartOptions" />
        </div>
      </div>

      <div class="flex flex-col justify-between items-center md:w-1/3 w-full bg-white border-4 border-black rounded-xl h-[450px]">
        <div class="chart-container w-full p-4">
          <div class="w-full flex justify-between items-center md:pt-12">
            <h2 class="text-2xl font-medium tracking-wide text-slate-700 text-left">Top Sales By Payment Method</h2>
            <button @click="downloadPDF3" class="w-full mt-6 px-4 py-2 text-md font-normal tracking-wider text-white bg-orange-600 rounded-lg custom-select hover:bg-orange-700 hover:shadow-lg">Download PDF</button>
          </div>
          <Doughnut :data="chartData1" :options="chartOptions1" />
        </div>
      </div>
    </div>

    <!-- Sales Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Sales Table</h2>

      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <button @click="downloadSalesTablePDF"
                  class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Qty:
              <span class="text-base font-bold">{{ salesTotalQty.toLocaleString() }}</span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-blue-600 rounded-xl bg-blue-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Final Selling Price :
              <span class="text-base font-bold">
                {{ salesGrossTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-yellow-600 rounded-xl bg-yellow-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Discounts:
              <span class="text-base font-bold">
                {{ salesDiscountTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
          <!-- <div class="py-2 px-4 border-2 border-purple-600 rounded-xl bg-purple-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Net:
              <span class="text-base font-bold">
                {{ salesNetTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div> -->
          <div class="py-2 px-4 border-2 border-red-600 rounded-xl bg-red-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Profit:
              <span class="text-base font-bold">
                {{ salesProfitTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[480px] border rounded-xl mt-2">
        <table id="salesTbl" class="sales-table w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md">
          <colgroup>
            <col style="width:48px" />
            <col style="width:110px" />
            <col style="width:170px" />
            <col style="width:200px" />
            <col style="width:70px" />
            <col style="width:140px" />
            <col style="width:140px" />
            <col style="width:140px" />
            <col style="width:140px" />
          </colgroup>

          <thead class="sticky top-0 z-10">
            <tr class="bg-gradient-to-r from-slate-700 via-slate-600 to-slate-700 text-white text-[14px] border-b border-slate-800">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Date</th>
              <th class="p-3 text-left font-semibold">Order Number</th>
              <th class="p-3 text-left font-semibold">Customer</th>
              <th class="p-3 text-center font-semibold">Qty</th>
              <th class="p-3 num font-semibold">Final Selling Price(LKR)</th>
              <th class="p-3 num font-semibold">Discounts</th>
              <th class="p-3 num font-semibold">Cost (LKR)</th>
              <th class="p-3 num font-semibold">Profit (LKR)

              </th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <tr v-for="(s, i) in sales" :key="s.id ?? i" class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 whitespace-nowrap text-center">{{ formatDate(s.sale_date) }}</td>
              <td class="p-3 text-center">
                {{ s.order_id ? s.order_id : 'Service -' }} {{ s.service_name }}
              </td>
              <td class="p-3">{{ s.customer?.name ?? 'N/A' }}</td>
              <td class="p-3 text-center">{{ saleQty(s) }}</td>
          <td class="p-3 num text-center">
  {{
    toMoney(
      s.total_amount -
      (
        s.custom_discount_type === 'percent'
          ? (Number(s.total_amount || 0) * Number(s.custom_discount || 0) / 100)
          : Number(s.custom_discount || 0)
      )
    )
  }}
</td>



              <!-- UPDATED: combined discount display with %→LKR conversion and row total -->
              <td class="p-3 num text-center">
                {{ discountDisplay(s) }}
              </td>

              <td class="p-3 num text-center">{{ toMoney(s.total_cost || 0) }}</td>
              <td class="p-3 num text-center">
                <!-- {{ toMoney(profitAmount(s)) }} -->
 <ul>
<!-- <ul>
  <li v-for="(it, idx) in (s.sale_items || [])" :key="idx">
    {{ it.product?.name ?? 'N/A' }}
    <span class="text-xs text-slate-500">
      — {{ it.quantity }} × {{ toMoney(it.product?.selling_price || 0) }}
      = {{ toMoney(itemGrossTotal(it)) }}
      <br />
      (Cost: {{ toMoney(itemCostTotal(it)) }})
    </span>
  </li>


  <li class="text-red-500 text-sm">
    − Discount: {{ toMoney(s.custom_discount || 0) }}
  </li>

  <li class="font-semibold">
    Net Total: {{ toMoney(saleNetTotal(s)) }}
  </li> -->
  <li class="font-semibold text-green-600">
   <span v-if="s.is_service">
    {{ toMoney(s.total_amount || 0) }}
  </span>
  <span v-else>
    {{ toMoney(saleProfit(s)) }}
  </span>

  </li>
</ul>




              </td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 text-[12px] font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="4">Totals:</td>
              <td class="p-3 text-center">{{ salesTotalQty.toLocaleString() }}</td>
              <td class="p-3 num text-center">
                {{ salesGrossTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td class="p-3 num text-center">
                {{ salesDiscountTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td class="p-3 num text-center">
                {{ salesCostTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
              </td>
              <td class="p-3 num text-center">
  {{ salesProfitTotal.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }}
</td>

            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- Top Products Stock Table -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Top Products Stock Table</h2>

      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <button @click="downloadStockTablePDF" class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-green-600 rounded-xl bg-green-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Sales Qty:
              <span class="text-base font-bold">{{ totalSalesQty.toLocaleString() }}</span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-blue-600 rounded-xl bg-blue-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Profit:
              <span class="text-base font-bold">
                {{ grandTotalProfit.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto max-h-[420px] border rounded-xl mt-2">
        <table id="stockQtyTbl" class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
          <thead>
            <tr class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white text-[14px] border-b border-blue-800">
              <th class="p-3 text-left font-semibold">#</th>
              <th class="p-3 text-left font-semibold">Product</th>
              <th class="p-3 text-center font-semibold">Sales Qty</th>
              <th class="p-3 text-center font-semibold">Total Sales Value (LKR)</th>
              <th class="p-3 text-center font-semibold">Price (LKR)</th>
              <th class="p-3 text-center font-semibold">Discount</th>
              <th class="p-3 text-center font-semibold">Price After Discount</th>
              <th class="p-3 text-center font-semibold">Profit</th>
            </tr>
          </thead>

          <tbody class="text-[12px] font-medium">
            <tr v-for="(p, i) in products" :key="p.id ?? i" class="border-b transition duration-200 hover:bg-gray-100">
              <td class="p-3 text-center">{{ i + 1 }}</td>
              <td class="p-3 font-bold">{{ p.name || 'N/A' }}</td>
              <td class="p-3 text-center">{{ Number(p.sales_qty || 0) }}</td>
              <td class="p-3 text-center">
                {{ (Number(p.sales_qty || 0) * Number(p.selling_price || 0)).toFixed(2) }}
              </td>
              <td class="p-3 text-center">{{ Number(p.selling_price || 0).toFixed(2) }}</td>
              <td class="p-3 text-center">
                <span v-if="Number(p.discount || 0) <= 100">{{ Number(p.discount || 0) }}%</span>
                <span v-else>Rs. {{ Number(p.discount).toFixed(2) }}</span>
              </td>
              <td class="p-3 text-center">{{ priceAfterDiscount(p).toFixed(2) }}</td>
              <td class="p-3 text-center">{{ totalProfit(p).toFixed(2) }} LKR</td>
            </tr>
          </tbody>

          <tfoot class="bg-gray-50 text-[12px]  font-semibold">
            <tr>
              <td class="p-3 text-right" colspan="2">Totals:</td>
              <td class="p-3 text-right">{{ totalSalesQty.toLocaleString() }}</td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right"></td>
              <td class="p-3 text-right">
                {{ grandTotalProfit.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>

    <!-- EXPENSES SECTION -->
    <div class="w-full bg-white border-4 border-black rounded-xl p-6 mt-8">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">Expenses</h2>

      <div class="flex justify-between items-center pb-4">
        <div class="flex gap-4">
          <button @click="downloadExpensesPDF"
                  class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md">
            Download PDF
          </button>
        </div>

        <div class="flex items-center gap-3">
          <div class="py-2 px-4 border-2 border-red-600 rounded-xl bg-red-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Total Expenses:
              <span class="text-base font-bold">
                {{ totalExpenseAmount.toLocaleString(undefined,{minimumFractionDigits:2,maximumFractionDigits:2}) }} LKR
              </span>
            </p>
          </div>
          <div class="py-2 px-4 border-2 border-slate-600 rounded-xl bg-slate-100 shadow-sm text-center">
            <p class="text-sm font-extrabold text-black uppercase">
              Records:
              <span class="text-base font-bold">{{ totalExpenseCount }}</span>
            </p>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto overflow-y-auto max-h-[420px] border rounded-xl mt-2">
       <table id="expenseTbl"
       class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto">
  <colgroup>
    <col style="width:60px" />
    <col style="width:220px" />
    <col style="width:160px" />
    <col style="width:140px" />
  </colgroup>

  <thead class="sticky top-0 z-10">
    <tr class="bg-gradient-to-r from-rose-700 via-rose-600 to-rose-700 text-white text-[14px] border-b border-rose-800">
      <th class="p-3 text-center font-semibold">#</th>
      <th class="p-3 text-left font-semibold">Title</th>
      <th class="p-3 text-right font-semibold">Amount (LKR)</th>
      <th class="p-3 text-center font-semibold">Expense Date</th>
    </tr>
  </thead>

  <tbody class="text-[12px] font-medium">
    <tr v-for="(e, i) in expenses" :key="e.id ?? i"
        class="border-b transition duration-200 hover:bg-gray-100">
      <td class="p-3 text-center">{{ i + 1 }}</td>
      <td class="p-3 text-left">{{ e.title || '—' }}</td>
      <td class="p-3 text-left">{{ toMoney(e.amount) }}</td>
      <td class="p-3 text-left">{{ formatDate(e.expense_date) }}</td>
    </tr>
  </tbody>

  <tfoot class="bg-gray-50 text-[12px] font-semibold">
    <tr>
      <td class="p-3 text-right" colspan="2">Total:</td>
      <td class="p-3 text-right">
        {{ totalExpenseAmount.toLocaleString(undefined,{minimumFractionDigits:2}) }}
      </td>
      <td class="p-3"></td>
    </tr>
  </tfoot>
</table>

      </div>
    </div> <!-- /EXPENSES SECTION -->




<div class="flex md:flex-row flex-col items-center justify-center w-full h-full md:space-x-4 md:space-y-0 space-y-4 ">
    <div class="w-full bg-white border-4 border-black rounded-xl p-6">
      <h2 class="text-2xl font-semibold text-slate-700 text-center pb-4">
        Return Items Table
      </h2>

  <!-- Buttons and Total Price in a Single Row -->
  <div class="flex justify-between items-center pb-4">
    <!-- Left: Buttons -->
    <div class="flex gap-4">
      <!-- <button
        @click="downloadTable"
        class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md"
      >
        Download Excel
      </button> -->

      <button
        @click="downloadPDFTableReturn"
        class="px-4 py-2 text-md font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-700 shadow-md"
      >
        Download PDF
      </button>
    </div>

    <!-- Right: Total Price -->
    <!-- <div
      class="py-3 px-6 border-2 border-orange-600 rounded-xl bg-orange-400 shadow-lg text-center"
    >
      <h2 class="text-lg font-extrabold text-black uppercase">Total Price:
      <span class="text-xl font-bold text-black">
        {{ totalPrice.toLocaleString() }} LKR
      </span> </h2>
    </div> -->
  </div>
  <div class="overflow-x-auto max-h-[400px] border rounded-xl mt-4">
    <table
      id="StockTransactionTbl"
      class="w-full text-gray-800 bg-white border border-gray-300 rounded-lg shadow-md table-auto"
    >
      <thead>
        <tr class="bg-gradient-to-r from-blue-700 via-blue-600 to-blue-700 text-white text-[14px] border-b border-blue-800">
          <th class="p-3 text-left font-semibold">#</th>
          <th class="p-3 text-center font-semibold">Product Name</th>
          <th class="p-3 text-center font-semibold">Transaction Date</th>
          <th class="p-3 text-center font-semibold">Quantity</th>
          <th class="p-3 text-center font-semibold">Selling Price</th>
          <th class="p-3 text-center font-semibold">Total Selling Price</th> 
        </tr>
      </thead>

      <tbody class="text-[12px] font-medium">
        <tr
          v-for="(stock, index) in stockTransactionsReturn"
          :key="index"
          class="border-b transition duration-200 hover:bg-gray-100"
        >
          <td class="p-3 text-center">{{ index + 1 }}</td>
          <td class="p-3 text-center">{{ stock.product?.name }}</td>
          <td class="p-3 text-center">{{ stock.transaction_date }}</td>
          <td class="p-3 text-center">{{ stock.quantity}}</td>
          <td class="p-3 text-center">{{ stock.product?.selling_price}}</td>
          <td class="p-3 text-center">
            {{ (stock.quantity * stock.product?.selling_price).toLocaleString() }}
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
</div>









  </div>

  <Footer />
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Doughnut, Pie } from "vue-chartjs";
import { Link, router, Head } from "@inertiajs/vue3";
import Header from "@/Components/custom/Header.vue";
import Footer from "@/Components/custom/Footer.vue";
import Banner from "@/Components/Banner.vue";
import jsPDF from "jspdf";
import "jspdf-autotable";
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  ArcElement,
  CategoryScale,
  LinearScale,
  BarElement,
} from "chart.js";

ChartJS.register(Title, Tooltip, Legend, ArcElement, CategoryScale, LinearScale, BarElement);

// Props expected from controller (updated names)
const props = defineProps({
  products: { type: Array, required: true },
  sales: { type: Array, required: true },

  totalSaleAmount: { type: Number, required: true },
  averageTransactionValue: { type: Number, required: true },
  netProfit: { type: Number, required: true },
  totalTransactions: { type: Number, required: true },

  // New clear totals (in LKR)
  totalDiscountLkr: { type: Number, required: true },        // product-level discount total
  totalCustomDiscountLkr: { type: Number, required: true },  // custom discount total after type conversion

  totalCustomer: { type: Number, required: true },
  startDate: { type: String, default: "" },
  endDate: { type: String, default: "" },
  categorySales: { type: Object, required: true },
  employeeSalesSummary: { type: Object, required: true },

  expenses: { type: Array, required: true },
  totalExpenseAmount: { type: Number, required: true },
  totalExpenseCount: { type: Number, required: true },
    stockTransactionsReturn: { type: Array, default: () => [] },
});

// State
const startDate = ref(props.startDate);
const endDate   = ref(props.endDate);
const products  = ref(props.products);
const stockTransactionsReturn = ref(props.stockTransactionsReturn);
const sales     = ref(props.sales);

// NEW — expenses state
const expenses            = ref(props.expenses);
const totalExpenseAmount  = ref(props.totalExpenseAmount || 0);
const totalExpenseCount   = ref(props.totalExpenseCount || 0);

// Formatting helpers
const toMoney   = (n) => (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
const formatDate= (d) => (d ? new Date(d).toLocaleDateString() : "");

// Totals (inventory cards)
const totalQty = computed(() => products.value.reduce((sum, p) => sum + (p.stock_quantity || 0), 0));
const totalSellingPrice = computed(() =>
  products.value.reduce((sum, p) => sum + (p.stock_quantity || 0) * (parseFloat(p.selling_price) || 0), 0)
);
const totalRetailValue = computed(() =>
  products.value.reduce((sum, p) => sum + (p.stock_quantity || 0) * (parseFloat(p.cost_price) || 0), 0)
);

// Price helpers for product table (product-level discount for product catalog display)
const priceAfterDiscount = (product) => {
  const price = Number(product.selling_price || 0);
  const discount = Number(product.discount || 0);
  return discount <= 100 ? price * (1 - discount / 100) : price - discount;
};
const profitPerUnit = (product) => priceAfterDiscount(product) - Number(product.cost_price || 0);
const totalProfit   = (product) => profitPerUnit(product) * Number(product.sales_qty || 0);

// Product table totals
const totalSalesQty    = computed(() => products.value.reduce((s, p) => s + Number(p.sales_qty || 0), 0));
const grandTotalProfit = computed(() => products.value.reduce((s, p) => s + totalProfit(p), 0));

// Date filter (IMPORTANT: preserveState false)
const filterData = () => {
  if (startDate.value && endDate.value && new Date(startDate.value) > new Date(endDate.value)) {
    alert("Start date cannot be greater than end date.");
    return;
  }
  router.get(
    route("reports.index"),
    { start_date: startDate.value, end_date: endDate.value },
    { preserveScroll: true, preserveState: false } // <<< key fix
  );
};

// ===== Charts data =====
const sortDescending = (data) =>
  Object.entries(data).sort((a, b) => b[1] - a[1]).reduce((acc, [k, v]) => ((acc[k] = v), acc), {});

const productQuantities = computed(() => {
  const quantities = {};
  props.sales.forEach((sale) => {
    sale.sale_items.forEach((item) => {
      const name = item.product && item.product.name ? item.product.name : "N/A";
      quantities[name] = (quantities[name] || 0) + item.quantity;
    });
  });
  return sortDescending(quantities);
});

const chartData = computed(() => ({
  labels: Object.keys(productQuantities.value),
  datasets: [{ data: Object.values(productQuantities.value),
    backgroundColor: ["#FF6384","#36A2EB","#FFCE56","#4BC0C0","#9966FF","#28a745","#ffc107","#17a2b8","#e83e8c","#fd7e14","#6610f2","#6f42c1","#dc3545","#adb5bd","#20c997","#ffc93c","#6a0572","#8ac926","#ff595e","#198754"] }],
}));
const chartOptions = { responsive: true, plugins: { legend: { display: true, position: "bottom" } } };

const paymentMethodTotals = computed(() => {
  const totals = {};
  props.sales.forEach((s) => {
    const m = s.payment_method;
    totals[m] = (totals[m] || 0) + parseFloat(s.total_amount);
  });
  return sortDescending(totals);
});
const chartData1 = computed(() => ({
  labels: Object.keys(paymentMethodTotals.value),
  datasets: [{ data: Object.values(paymentMethodTotals.value),
    backgroundColor: ["#FF6384","#36A2EB","#FFCE56","#4BC0C0","#9966FF","#28a745","#ffc107","#17a2b8","#e83e8c","#fd7e14","#6610f2","#6f42c1","#dc3545","#adb5bd","#20c997","#ffc93c","#6a0572","#8ac926","#ff595e","#198754"] }],
}));
const chartOptions1 = {
  responsive: true,
  plugins: { legend: { display: true, position: "bottom" },
    tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw || 0).toLocaleString()}` } } },
};

const sortedEmployeeSales = computed(() =>
  Object.fromEntries(Object.entries(props.employeeSalesSummary).sort(([, a], [, b]) => b["Total Sales Amount"] - a["Total Sales Amount"]))
);
const chartData4 = computed(() => ({
  labels: Object.keys(sortedEmployeeSales.value),
  datasets: [{ data: Object.values(sortedEmployeeSales.value).map((e) => e["Total Sales Amount"]),
    backgroundColor: ["#6610f2","#36A2EB","#8ac926","#ff595e","#198754","#6f42c1","#dc3545","#adb5bd","#20c997","#28a745","#ffc107","#17a2b8","#e83e8c","#fd7e14","#FF6384","#FFCE56","#4BC0C0","#9966FF","#ffc93c"] }],
}));
const chartOptions4 = { responsive: true, plugins: { legend: { display: true, position: "bottom" },
  tooltip: { callbacks: { label: (c) => `LKR ${(+c.raw).toLocaleString()}` } } } };

// ===== Sales Table helpers & totals (respect custom_discount_type) =====
const itemsCount = (s) => (Array.isArray(s.sale_items) ? s.sale_items.length : 0);
const saleQty    = (s) => (Array.isArray(s.sale_items) ? s.sale_items.reduce((n, it) => n + Number(it.quantity || 0), 0) : 0);

// Convert custom discount to LKR based on type for a row
const customDiscountLkr = (s) => {
  const gross = Number(s.total_amount || 0);
  const val   = Number(s.custom_discount || 0);
  const type  = s.custom_discount_type || 'fixed';
  return type === 'percent' ? (gross * val / 100) : val;
};

// === NEW: combined discount helpers ===
// Total discount (LKR) in a row = product-level discount (LKR) + custom discount (converted if %)
const discountLkr = (s) =>  customDiscountLkr(s);

// Pretty label for the "discounts" cell
const discountDisplay = (s) => {
  const parts = [];
  const gross = Number(s.total_amount || 0);

  const hasProdDisc = Number(s.discount || 0) > 0;
  const customVal   = Number(s.custom_discount || 0);
  const customType  = s.custom_discount_type || "fixed";

  if (customVal > 0) {
    if (customType === "percent") {
      const lkr = (gross * customVal) / 100;
      parts.push(`${customVal}% (${toMoney(lkr)} LKR)`);
    } else {
      parts.push(`${toMoney(customVal)} LKR`);
    }
  }


  const total = discountLkr(s);
//   parts.push(`Total: ${toMoney(total)} LKR`);

  return parts.join("  |  ");
};

// Net = gross - product discount (LKR) - custom discount (LKR)
const netAmount    = (s) => Number(s.total_amount || 0) - Number(s.discount || 0) - customDiscountLkr(s);
const profitAmount = (s) => netAmount(s) - Number(s.total_cost || 0);

// Totals
const salesTotalQty    = computed(() => sales.value.reduce((a, s) => a + saleQty(s), 0));
const salesGrossTotal = computed(() =>
  sales.value.reduce((a, s) => {
    const total = Number(s.total_amount || 0);

    // Work out discount in LKR
    let customDiscountLkr = 0;
    if (s.custom_discount_type === "percent") {
      customDiscountLkr = (total * Number(s.custom_discount || 0)) / 100;
    } else {
      customDiscountLkr = Number(s.custom_discount || 0);
    }

    return a + (total - customDiscountLkr);
  }, 0)
);



// UPDATED: proper reducer for combined discounts
const salesDiscountTotal = computed(() =>
  sales.value.reduce((a, s) => a + discountLkr(s), 0)
);

const salesNetTotal    = computed(() => sales.value.reduce((a, s) => a + netAmount(s), 0));
const salesCostTotal   = computed(() => sales.value.reduce((a, s) => a + Number(s.total_cost || 0), 0));



// inside <script setup> or computed block
const salesProfitTotal = computed(() =>
  sales.value.reduce((sum, s) => sum + saleProfit(s), 0)
);

// Date label for PDFs
const dateRangeLabel = computed(() => {
  const s = startDate.value ? new Date(startDate.value).toLocaleDateString() : "All";
  const e = endDate.value ? new Date(endDate.value).toLocaleDateString() : "All";
  return `${s} — ${e}`;
});









 // per item gross (qty × selling price)
const itemGrossTotal = (it) => {
  return (it.quantity || 0) * (it.product?.selling_price || 0);
};

// per item cost (qty × cost price)
const itemCostTotal = (it) => {
  return (it.quantity || 0) * (it.product?.cost_price || 0);
};

// gross total of sale
const saleGrossTotal = (s) => {
  if (!Array.isArray(s.sale_items)) return 0;
  return s.sale_items.reduce((sum, it) => sum + itemGrossTotal(it), 0);
};

// total cost of sale
const saleCostTotal = (s) => {
  if (!Array.isArray(s.sale_items)) return 0;
  return s.sale_items.reduce((sum, it) => sum + itemCostTotal(it), 0);
};

// net total after discount
const saleNetTotal = (s) => {
  return saleGrossTotal(s) - (s.custom_discount || 0);
};

// Profit calc (mirror UI: uses sale-level totals so services are included)
const saleProfit = (s) => {
  const gross = Number(s.total_amount || 0);
  const discount =
    (s.custom_discount_type || "fixed") === "percent"
      ? (gross * Number(s.custom_discount || 0)) / 100
      : Number(s.custom_discount || 0);

  const net = gross - discount;            // Final Selling Price shown in table
  const cost = Number(s.total_cost || 0);  // works for products & services
  return net - cost;                        // Profit (LKR)
};


// ===== Chart PDFs =====
const downloadPDF = () => {
  const doc = new jsPDF();
  doc.text("Top Employee Sales", 14, 10);
  const rows = Object.entries(sortedEmployeeSales.value).map(([employee, entry]) => [employee, entry["Total Sales Amount"]]);
  doc.autoTable({ head: [["Employee", "Total Sales Amount"]], body: rows, startY: 20 });
  doc.save("EmployeeSales.pdf");
};
const downloadPDF2 = () => {
  const doc = new jsPDF();
  doc.text("Product Quantities", 14, 10);
  const rows = Object.entries(productQuantities.value).map(([product, qty]) => [product, qty]);
  doc.autoTable({ head: [["Product Name", "Quantity"]], body: rows, startY: 20 });
  doc.save("ProductQuantities.pdf");
};
const downloadPDF3 = () => {
  const doc = new jsPDF();
  doc.text("Payment Method Totals", 14, 10);
  const rows = Object.entries(paymentMethodTotals.value).map(([m, t]) => [m, `LKR ${t.toLocaleString()}`]);
  doc.autoTable({ head: [["Payment Method", "Total Amount"]], body: rows, startY: 20 });
  doc.save("PaymentMethodTotals.pdf");
};

 // ===== Sales table PDF (matches rendered table exactly) =====
const downloadSalesTablePDF = () => {
  const doc = new jsPDF("l", "mm", "a4");
  const now = new Date();

  // Helpers (mirror the UI)
  const toMoney = (n) =>
    (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const formatDate = (d) => (d ? new Date(d).toLocaleDateString() : "");

  const saleQty = (s) => {
    if (!Array.isArray(s.sale_items)) return 0;
    return s.sale_items.reduce((total, item) => total + Number(item.quantity || 0), 0);
  };

  // Convert custom discount to LKR (percent or fixed)
  const customDiscountLkr = (s) => {
    const gross = Number(s.total_amount || 0);
    const val = Number(s.custom_discount || 0);
    const type = s.custom_discount_type || "fixed";
    return type === "percent" ? (gross * val) / 100 : val;
  };

  // Same display logic as UI
  const discountDisplay = (s) => {
    const parts = [];
    const gross = Number(s.total_amount || 0);
    const customVal = Number(s.custom_discount || 0);
    const customType = s.custom_discount_type || "fixed";

    if (customVal > 0) {
      if (customType === "percent") {
        const lkr = (gross * customVal) / 100;
        parts.push(`${customVal}% (${toMoney(lkr)} LKR)`);
      } else {
        parts.push(`${toMoney(customVal)} LKR`);
      }
    }
    return parts.join("  |  ");
  };

  // ✅ FIXED: Profit calc (sale-level; includes services)
  const saleProfit = (s) => {
    const gross = Number(s.total_amount || 0);
    const discount =
      (s.custom_discount_type || "fixed") === "percent"
        ? (gross * Number(s.custom_discount || 0)) / 100
        : Number(s.custom_discount || 0);
    const net = gross - discount;
    const cost = Number(s.total_cost || 0);
    return net - cost;
  };

  // Body rows — match UI cells
  const rowData = sales.value.map((s, i) => {
    const date        = formatDate(s.sale_date);
    const orderNumber = s.order_id ? s.order_id : `Service - ${s.service_name || ""}`;
    const customer    = s.customer?.name ?? "N/A";
    const qty         = saleQty(s);

    // UI shows Final Selling Price = total_amount - custom_discount (percent handled)
    const grossNet    = Number(s.total_amount || 0) - customDiscountLkr(s);

    const discounts   = discountDisplay(s);
    const cost        = Number(s.total_cost || 0);
    const profit      = saleProfit(s);

    return [
      i + 1,
      date,
      orderNumber,
      customer,
      qty.toString(),
      toMoney(grossNet),
      discounts,
      toMoney(cost),
      toMoney(profit),
    ];
  });

  // Footer totals — mirror UI footer
  const totalQty       = sales.value.reduce((a, s) => a + saleQty(s), 0);
  const totalGrossNet  = sales.value.reduce((a, s) => a + (Number(s.total_amount || 0) - customDiscountLkr(s)), 0);
  const totalDiscounts = sales.value.reduce((a, s) => a + customDiscountLkr(s), 0);
  const totalCost      = sales.value.reduce((a, s) => a + Number(s.total_cost || 0), 0);
  const totalProfit    = sales.value.reduce((a, s) => a + saleProfit(s), 0);

  // PDF Header
  doc.setFontSize(16);
  doc.text("Sales Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${now.toLocaleString()}`, 14, 18);

  // Table
  const head = [[
    "#",
    "Date",
    "Order Number",
    "Customer",
    "Qty",
    "Final Selling Price(LKR)",
    "Discounts",
    "Cost (LKR)",
    "Profit (LKR)",
  ]];

  const foot = [[
    { content: "Totals:", colSpan: 4, styles: { halign: "right", fontStyle: "bold" } },
    { content: totalQty.toLocaleString(), styles: { halign: "center", fontStyle: "bold" } },
    toMoney(totalGrossNet),
    toMoney(totalDiscounts),
    toMoney(totalCost),
    toMoney(totalProfit),
  ]];

  doc.autoTable({
    head,
    body: rowData,
    foot,
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9, cellPadding: 2 },
    headStyles: { fillColor: [51, 65, 85], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 10 },
      1: { cellWidth: 22 },
      2: { cellWidth: 36 },
      3: { cellWidth: 36 },
      4: { cellWidth: 14, halign: "center" },
      5: { cellWidth: 28, halign: "right" }, // Final Selling Price
      6: { cellWidth: 28, halign: "right" }, // Discounts
      7: { cellWidth: 28, halign: "right" }, // Cost
      8: { cellWidth: 28, halign: "right" }, // Profit
    },
    margin: { top: 18, left: 8, right: 8 },
    didParseCell: (data) => {
      if (data.section === "foot") {
        if (data.column.index === 4) data.cell.styles.halign = "center"; // Qty
        if (data.column.index >= 5)  data.cell.styles.halign = "right";  // Money columns
        if (data.column.index <= 3)  data.cell.styles.halign = "right";  // "Totals:"
      }
    },
  });

  // Save
  const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`Sales_Report_${safe(dateRangeLabel.value)}.pdf`);
};


// ===== Expenses PDF =====
const downloadExpensesPDF = () => {
  const doc = new jsPDF("p", "mm", "a4");
  const now = new Date();

  const toMoney = (n) =>
    (Number(n || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  const fmtDate = (d) => (d ? new Date(d).toLocaleDateString() : "—");

  // Build body rows
  const rows = expenses.value.map((e, i) => [
    i + 1,
    e.title || "—",
    toMoney(e.amount),
    fmtDate(e.expense_date),
  ]);

  // Grand total (LKR)
  const total = expenses.value.reduce((sum, e) => sum + Number(e.amount || 0), 0);

  // Header
  doc.setFontSize(16);
  doc.text("Expenses Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${now.toLocaleString()}`, 14, 18);

  // One table with a footer so alignment is perfect
  doc.autoTable({
    head: [["#", "Title", "Amount (LKR)", "Expense Date"]],
    body: rows,
    foot: [[
      "",                                  // col # (blank)
      { content: "Total:", styles: { halign: "right", fontStyle: "bold" } }, // under Title, right-aligned
      { content: `${toMoney(total)} LKR`, styles: { halign: "center", fontStyle: "bold" } }, // under Amount, right
      "",                                  // under Date
    ]],
    startY: 24,
    theme: "striped",
    styles: { fontSize: 9, cellPadding: 2 },
    headStyles: { fillColor: [190, 18, 60], textColor: 255 },
    // Keep widths + alignment consistent across body/head/foot
    columnStyles: {
      0: { cellWidth: 12, halign: "center" }, // #
      1: { cellWidth: 106 },                  // Title
      2: { cellWidth: 40, halign: "center" },  // Amount
      3: { cellWidth: 40, halign: "center" }, // Date
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  const safe = (s) => String(s).replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`Expenses_${safe(dateRangeLabel.value)}.pdf`);
};

const downloadPDFTableReturnTable = () => {
  // Map the products data with calculations
  const stockTransactionData = stockTransactionsReturn.value.map((stock, index) => ({
    "#": index + 1,
    "Name":  stock.product?.name|| "N/A",
    "Transaction Date": stock.transaction_date || "N/A",
    "Quantity":stock.quantity||0,
    "Selling Price":stock.product?.selling_price|| 0,
  }));

  // Calculate the sum of total prices
  const totalSum = stockTransactionData.reduce((sum, product) => sum + product["Selling Price"], 0);

  // Add a total row
  const dataWithTotal = [
    ...stockTransactionData,
    {
      "#": "",
      "Name": "Total",
      "Transaction Date": "",
      "Quantity":"",
      "Selling Price":"",
    
    }
  ];

  // Create worksheet with the data including total
  const ws = XLSX.utils.json_to_sheet(dataWithTotal);

  // Add some styling to the total row (make the text bold)
  const lastRow = dataWithTotal.length;
  const range = XLSX.utils.decode_range(ws['!ref']);

  // Set column widths
  const colWidths = [
    { wch: 5 },  // #
    { wch: 30 }, // Name
    { wch: 20 }, // Transaction Date
    { wch: 10 }, // Quantity
    { wch: 25 }, // Cost Price
   
   
  ];
  ws['!cols'] = colWidths;

  // Create a new workbook and append the worksheet
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, "Stock Data");

  // Generate Excel file and trigger download
  const excelBuffer = XLSX.write(wb, { bookType: "xlsx", type: "array" });
  const blob = new Blob([excelBuffer], {
    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
  });

  saveAs(blob, "Return table.xlsx");
};

const downloadPDFTableReturn = () => {
  const doc = new jsPDF("p", "mm", "a4"); // Portrait, A4 size

  // Title for the PDF
  doc.setFontSize(18);
  doc.text("Return Items Table", 14, 15);

  // Prepare table headers
  const tableColumn = [
    "#",
    "Product Name",
    "Transaction Date",
    "Quantity",
    "Selling Price",
    
  ];

  // Prepare table data
  const tableRows = stockTransactionsReturn.value.map((stock, index) => [
    index + 1,
    stock.product?.name||"N/A",
    stock.transaction_date || "N/A",
    stock.quantity||0,
    Number(stock.product?.selling_price)|| 0,
    
  ]);

  // Calculate total sum of "Total Price"
  const totalSum = tableRows.reduce((sum, row) => sum + row[4], 0);

  // Add a total row at the end
  tableRows.push(["", "Total", "", "", totalSum.toFixed(2)]);

  // Adjust column widths
  doc.autoTable({
    head: [tableColumn],
    body: tableRows,
    startY: 25, // Adjust start position to prevent overlap with title
    theme: "striped",
    styles: { fontSize: 10 },
    headStyles: { fillColor: [44, 62, 80] },
    columnStyles: {
      0: { cellWidth: 20 },  // #
      1: { cellWidth: 30 },  // Name (Increased for better visibility)
      4: { cellWidth: 25 },  // Cost Price
      5: { cellWidth: 25},  // Selling Price
      6: { cellWidth: 20 },  // Profit
     
    },
    margin: { left: 5, right: 10, top: 20 },
  });

  // Save the PDF
  doc.save("Return Items.pdf");
};

// ===== Stock table PDF =====
const downloadStockTablePDF = () => {
  const tableEl = document.getElementById("stockQtyTbl");
  if (!tableEl) return;

  const toNumber = (txt) => Number(String(txt).replace(/[^\d.\-]/g, "")) || 0;
  const rows = [];
  const $ = window.$;

  if ($ && $.fn && $.fn.dataTable && $.fn.dataTable.isDataTable("#stockQtyTbl")) {
    const dt = $("#stockQtyTbl").DataTable();
    dt.rows({ search: "applied" }).every(function () {
      const tr = this.node();
      const cells = Array.from(tr.querySelectorAll("td")).map(td => (td.textContent || "").trim());
      rows.push([cells[0], cells[1], cells[2], cells[3], cells[4], cells[5], cells[6], cells[7]]);
    });
  } else {
    tableEl.querySelectorAll("tbody tr").forEach((tr) => {
      const cells = Array.from(tr.querySelectorAll("td")).map(td => (td.textContent || "").trim());
      rows.push([cells[0], cells[1], cells[2], cells[3], cells[4], cells[5], cells[6], cells[7]]);
    });
  }

  const filteredTotals = rows.reduce(
    (acc, r) => {
      acc.qty += Number(r[2]) || 0;
      acc.sales += toNumber(r[3]);
      acc.profit += toNumber(r[7]);
      return acc;
    },
    { qty: 0, sales: 0, profit: 0 }
  );

  const doc = new jsPDF("l", "mm", "a4");
  doc.setFontSize(16);
  doc.text("Top Products Stock Report", 14, 12);
  doc.setFontSize(10);
  doc.text(`Date range: ${dateRangeLabel.value} • Generated: ${new Date().toLocaleString()}`, 14, 18);

  const head = [[ "#","Product","Sales Qty","Total Sales Value (LKR)","Price (LKR)","Discount","Price After Discount","Profit" ]];

  doc.autoTable({
    head, body: rows, startY: 24, theme: "striped",
    styles: { fontSize: 9 }, headStyles: { fillColor: [33, 102, 197], textColor: 255 },
    columnStyles: {
      0: { cellWidth: 10 }, 1: { cellWidth: 60 }, 2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34, halign: "right" }, 4: { cellWidth: 28, halign: "right" },
      5: { cellWidth: 28, halign: "center" }, 6: { cellWidth: 34, halign: "right" }, 7: { cellWidth: 34, halign: "right" },
    },
    margin: { top: 18, left: 8, right: 8 },
  });

  const totalsRow = [
    "", "Totals:", filteredTotals.qty.toLocaleString(),
    filteredTotals.sales.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " LKR",
    "—", "—", "—",
    filteredTotals.profit.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + " LKR",
  ];

  doc.autoTable({
    body: [totalsRow], startY: doc.lastAutoTable ? doc.lastAutoTable.finalY + 2 : 24,
    theme: "plain", styles: { fontSize: 10, fontStyle: "bold" },
    columnStyles: {
      0: { cellWidth: 10 }, 1: { cellWidth: 60, halign: "right" }, 2: { cellWidth: 22, halign: "right" },
      3: { cellWidth: 34, halign: "right" }, 4: { cellWidth: 28 }, 5: { cellWidth: 28 }, 6: { cellWidth: 34 },
      7: { cellWidth: 34, halign: "right" },
    },
    margin: { left: 8, right: 8 },
  });

  const safe = (s) => s.replace(/[^\dA-Za-z-]/g, "_");
  doc.save(`Top_Products_Stock_${safe(dateRangeLabel.value)}.pdf`);
};

// ===== DataTables init =====
onMounted(() => {
  const jq = window.$;



  // Stock table
  const $stock = jq && jq("#stockQtyTbl");
  if ($stock && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($stock)) $stock.DataTable().destroy();
    const dt = $stock.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search stock...");
        $input.on("keypress", function (e) {
          if (e.which == 13) dt.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }

  // Expenses table
  const $exp = jq && jq("#expenseTbl");
  if ($exp && jq.fn.dataTable) {
    if (jq.fn.dataTable.isDataTable($exp)) $exp.DataTable().destroy();
    const de = $exp.DataTable({
      dom: "Bfrtip",
      paging: false,
      buttons: [],
      columnDefs: [{ targets: 0, searchable: false, orderable: false }],
      initComplete: function () {
        const $input = jq("div.dataTables_filter input");
        $input.attr("placeholder", "Search expenses...");
        $input.on("keypress", function (e) {
          if (e.which == 13) de.search(this.value).draw();
        });
      },
      language: { search: "" },
    });
  }
});
</script>

<style>
/* DataTables cosmetics */
.dataTables_wrapper .dataTables_paginate {
  display: flex; justify-content: center; align-items: center; margin-top: 20px;
}
#salesTbl_filter, #stockQtyTbl_filter, #expenseTbl_filter {
  display: flex; justify-content: flex-end; align-items: center; margin-bottom: 16px; float: left;
}
#salesTbl_filter label, #stockQtyTbl_filter label, #expenseTbl_filter label { font-size: 17px; color: #000000; display: flex; align-items: center; }
#salesTbl_filter input[type="search"], #stockQtyTbl_filter input[type="search"], #expenseTbl_filter input[type="search"] {
  font-weight: 400; padding: 9px 15px; font-size: 14px; color: #000000cc;
  border: 1px solid rgb(209 213 219); border-radius: 5px; background: #fff;
  outline: none; transition: all 0.5s ease;
}
#salesTbl_filter input[type="search"]:focus, #stockQtyTbl_filter input[type="search"]:focus, #expenseTbl_filter input[type="search"]:focus {
  border: 1px solid #4b5563; box-shadow: none;
}
.dataTables_wrapper { margin-bottom: 10px; }
</style>

<style scoped>
.chart-container {
  display: flex; flex-direction: column; align-items: center; justify-content: center;
  width: 100%; height: calc(100% - 50px); position: relative;
}
thead { position: sticky; top: 0; z-index: 10; }
.max-h-64 { max-height: 16rem; }
</style>
