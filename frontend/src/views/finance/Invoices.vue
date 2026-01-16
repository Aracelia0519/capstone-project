<template>
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center gap-3 mb-2">
          <span class="p-2 bg-orange-100 text-orange-600 rounded-lg">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
          </span>
          Invoices / Billing
        </h1>
        <p class="text-sm text-gray-600">Generate and track invoices</p>
      </div>
      <div class="flex gap-3 flex-wrap">
        <button class="px-4 py-2.5 bg-orange-600 text-white rounded-lg font-medium flex items-center gap-2 hover:bg-orange-700 transition-colors duration-200" @click="generateInvoice">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Generate Invoice</span>
        </button>
        <button class="px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg font-medium flex items-center gap-2 hover:bg-gray-50 transition-colors duration-200" @click="showEmailSettings = true">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <span>Email Settings</span>
        </button>
      </div>
    </div>

    <!-- Stats Banner -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Total Invoices</h3>
          <p class="text-xl font-bold text-gray-900">{{ invoices.length }}</p>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Paid</h3>
          <p class="text-xl font-bold text-gray-900">{{ paidCount }}</p>
          <p class="text-sm text-gray-600">₱{{ paidAmount.toLocaleString() }}</p>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-yellow-100 text-yellow-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Pending</h3>
          <p class="text-xl font-bold text-gray-900">{{ pendingCount }}</p>
          <p class="text-sm text-gray-600">₱{{ pendingAmount.toLocaleString() }}</p>
        </div>
      </div>
      
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Overdue</h3>
          <p class="text-xl font-bold text-gray-900">{{ overdueCount }}</p>
          <p class="text-sm text-gray-600">₱{{ overdueAmount.toLocaleString() }}</p>
        </div>
      </div>
    </div>

    <!-- Filters & Search -->
    <div class="space-y-4 mb-6">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <input 
          v-model="filters.search" 
          type="text" 
          placeholder="Search by invoice ID, client, amount..."
          class="w-full pl-10 pr-3 py-2.5 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
        >
      </div>
      
      <div class="flex flex-wrap gap-3">
        <select v-model="filters.status" class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
          <option value="">All Status</option>
          <option value="paid">Paid</option>
          <option value="pending">Pending</option>
          <option value="overdue">Overdue</option>
        </select>
        
        <select v-model="filters.type" class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
          <option value="">All Types</option>
          <option value="client">Client</option>
          <option value="distributor">Distributor</option>
          <option value="service-provider">Service Provider</option>
        </select>
        
        <select v-model="filters.dateRange" class="px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
          <option value="all">All Time</option>
          <option value="today">Today</option>
          <option value="week">This Week</option>
          <option value="month">This Month</option>
          <option value="quarter">This Quarter</option>
        </select>
      </div>
    </div>

    <!-- Invoices Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-8">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice ID</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client / Distributor / Service Provider</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="invoice in filteredInvoices" :key="invoice.id">
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="font-mono text-sm text-gray-900 font-medium">{{ invoice.id }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900">{{ invoice.client }}</span>
                  <span class="text-xs text-gray-500">{{ invoice.type }}</span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm text-gray-700">{{ formatDate(invoice.date) }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['text-sm', isOverdue(invoice.dueDate) ? 'text-red-600 font-medium' : 'text-gray-700']">
                  {{ formatDate(invoice.dueDate) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="text-sm font-semibold text-gray-900">₱{{ invoice.amount.toLocaleString() }}</span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="['inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium', 
                  invoice.status === 'paid' ? 'bg-green-100 text-green-800' : 
                  invoice.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                  'bg-red-100 text-red-800']">
                  {{ invoice.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex gap-2">
                  <button class="p-1.5 rounded-md text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-colors duration-200" @click="viewInvoice(invoice)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </button>
                  <button class="p-1.5 rounded-md text-gray-600 hover:text-green-600 hover:bg-green-50 transition-colors duration-200" @click="downloadPDF(invoice)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </button>
                  <button class="p-1.5 rounded-md text-gray-600 hover:text-orange-600 hover:bg-orange-50 transition-colors duration-200" @click="sendEmail(invoice)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Empty State -->
      <div v-if="filteredInvoices.length === 0" class="py-12 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No invoices found</h3>
        <p class="text-gray-500">Try adjusting your filters or generate a new invoice</p>
      </div>

      <!-- Pagination -->
      <div v-if="filteredInvoices.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
        <button 
          @click="prevPage" 
          :disabled="currentPage === 1"
          class="p-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        
        <span class="text-sm text-gray-700">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        
        <button 
          @click="nextPage" 
          :disabled="currentPage === totalPages"
          class="p-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Invoice Preview Modal -->
    <div v-if="selectedInvoice" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click="selectedInvoice = null">
      <div class="bg-white rounded-xl shadow-xl max-w-3xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Invoice Preview: {{ selectedInvoice.id }}</h3>
          <button @click="selectedInvoice = null" class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-6 space-y-6">
          <!-- Invoice Header -->
          <div class="flex flex-col md:flex-row justify-between gap-6 pb-6 border-b border-gray-200">
            <div class="space-y-1">
              <h2 class="text-2xl font-bold text-gray-900">CaviteGo Paint</h2>
              <p class="text-sm text-gray-600">123 Paint Street, Cavite City</p>
              <p class="text-sm text-gray-600">contact@cavitegopaint.com | (046) 123-4567</p>
            </div>
            <div class="space-y-3">
              <h3 class="text-xl font-bold text-gray-900 text-right">INVOICE</h3>
              <div class="space-y-2">
                <div class="flex justify-between gap-4">
                  <span class="text-sm text-gray-600">Invoice #:</span>
                  <span class="text-sm font-medium text-gray-900">{{ selectedInvoice.id }}</span>
                </div>
                <div class="flex justify-between gap-4">
                  <span class="text-sm text-gray-600">Date:</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatDate(selectedInvoice.date) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                  <span class="text-sm text-gray-600">Due Date:</span>
                  <span class="text-sm font-medium text-gray-900">{{ formatDate(selectedInvoice.dueDate) }}</span>
                </div>
                <div class="flex justify-between gap-4">
                  <span class="text-sm text-gray-600">Status:</span>
                  <span :class="['text-sm font-medium capitalize', 
                    selectedInvoice.status === 'paid' ? 'text-green-600' : 
                    selectedInvoice.status === 'pending' ? 'text-yellow-600' : 
                    'text-red-600']">
                    {{ selectedInvoice.status }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Bill To -->
          <div class="space-y-2">
            <h4 class="text-lg font-semibold text-gray-900">Bill To</h4>
            <div class="space-y-1">
              <p class="font-medium text-gray-900">{{ selectedInvoice.client }}</p>
              <p class="text-sm text-gray-600">{{ selectedInvoice.type }}</p>
              <p class="text-sm text-gray-600">Sample Address, Cavite</p>
              <p class="text-sm text-gray-600">client@email.com</p>
            </div>
          </div>
          
          <!-- Items Table -->
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr>
                  <td class="px-4 py-3 text-sm text-gray-700">Paint Supplies - Premium Quality</td>
                  <td class="px-4 py-3 text-sm text-gray-700">10</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱{{ (selectedInvoice.amount / 10).toLocaleString() }}</td>
                  <td class="px-4 py-3 text-sm text-gray-700">₱{{ selectedInvoice.amount.toLocaleString() }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Totals -->
          <div class="space-y-2 ml-auto max-w-xs">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Subtotal:</span>
              <span class="text-sm font-medium text-gray-900">₱{{ selectedInvoice.amount.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Tax (12%):</span>
              <span class="text-sm font-medium text-gray-900">₱{{ (selectedInvoice.amount * 0.12).toLocaleString() }}</span>
            </div>
            <div class="flex justify-between items-center pt-2 border-t border-gray-200">
              <span class="text-base font-semibold text-gray-900">Total Amount:</span>
              <span class="text-lg font-bold text-gray-900">₱{{ (selectedInvoice.amount * 1.12).toLocaleString() }}</span>
            </div>
          </div>
          
          <!-- Payment Instructions -->
          <div class="p-4 bg-gray-50 rounded-lg space-y-2">
            <h4 class="text-lg font-semibold text-gray-900">Payment Instructions</h4>
            <p class="text-sm text-gray-700">Please make payment to:</p>
            <p class="text-sm text-gray-700"><strong>Bank:</strong> CaviteGo Paint Bank</p>
            <p class="text-sm text-gray-700"><strong>Account #:</strong> 1234-5678-9012</p>
            <p class="text-sm text-gray-700"><strong>Due Date:</strong> {{ formatDate(selectedInvoice.dueDate) }}</p>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
          <button @click="selectedInvoice = null" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Close
          </button>
          <button class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700" @click="downloadPDF(selectedInvoice)">
            Download PDF
          </button>
          <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700" @click="sendEmail(selectedInvoice)">
            Send Email
          </button>
        </div>
      </div>
    </div>

    <!-- Email Settings Modal -->
    <div v-if="showEmailSettings" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50" @click="showEmailSettings = false">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto" @click.stop>
        <div class="flex items-center justify-between p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Email Notification Settings</h3>
          <button @click="showEmailSettings = false" class="p-1 rounded-md text-gray-400 hover:text-gray-600 hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        
        <div class="p-6">
          <div class="space-y-6">
            <div class="space-y-4">
              <h4 class="text-lg font-medium text-gray-900">Invoice Notifications</h4>
              <div class="flex items-center gap-3">
                <input type="checkbox" id="sendOnCreate" checked class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                <label for="sendOnCreate" class="text-sm text-gray-700">Send email when invoice is created</label>
              </div>
              <div class="flex items-center gap-3">
                <input type="checkbox" id="sendReminders" checked class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                <label for="sendReminders" class="text-sm text-gray-700">Send payment reminders</label>
              </div>
              <div class="flex items-center gap-3">
                <input type="checkbox" id="sendOnPayment" checked class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                <label for="sendOnPayment" class="text-sm text-gray-700">Send confirmation on payment</label>
              </div>
            </div>
            
            <div class="space-y-4">
              <h4 class="text-lg font-medium text-gray-900">Reminder Schedule</h4>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Days before due date</label>
                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500" value="7" min="1" max="30">
              </div>
              <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Follow-up frequency (days)</label>
                <input type="number" class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500" value="3" min="1" max="7">
              </div>
            </div>
            
            <div class="space-y-4">
              <h4 class="text-lg font-medium text-gray-900">Email Template</h4>
              <textarea class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 min-h-[100px]">
Dear [Client Name],

Your invoice #[Invoice Number] for ₱[Amount] is due on [Due Date].
Please make payment at your earliest convenience.

Thank you,
CaviteGo Paint Team
              </textarea>
            </div>
          </div>
        </div>
        
        <div class="flex justify-end gap-3 p-6 border-t border-gray-200">
          <button @click="showEmailSettings = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
            Cancel
          </button>
          <button class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700" @click="saveEmailSettings">
            Save Settings
          </button>
        </div>
      </div>
    </div>

    <!-- Defense Explanation -->
    
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

// State
const showEmailSettings = ref(false)
const selectedInvoice = ref(null)
const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  search: '',
  status: '',
  type: '',
  dateRange: 'all'
})

// Mock data
const invoices = ref([
  { id: 'INV-2024-001', client: 'John Smith', type: 'Client', date: '2024-01-15', dueDate: '2024-02-15', amount: 5000, status: 'paid' },
  { id: 'INV-2024-002', client: 'ABC Distributors', type: 'Distributor', date: '2024-01-14', dueDate: '2024-02-14', amount: 12500, status: 'pending' },
  { id: 'INV-2024-003', client: 'Maria Santos', type: 'Service Provider', date: '2024-01-13', dueDate: '2024-01-27', amount: 3000, status: 'overdue' },
  { id: 'INV-2024-004', client: 'Paint Supplies Co.', type: 'Supplier', date: '2024-01-12', dueDate: '2024-02-12', amount: 8500, status: 'paid' },
  { id: 'INV-2024-005', client: 'Mark Johnson', type: 'Client', date: '2024-01-11', dueDate: '2024-02-11', amount: 6200, status: 'pending' },
  { id: 'INV-2024-006', client: 'Service Pro Inc.', type: 'Service Provider', date: '2024-01-10', dueDate: '2024-01-24', amount: 4300, status: 'overdue' },
  { id: 'INV-2024-007', client: 'Jane Doe', type: 'Client', date: '2024-01-09', dueDate: '2024-02-09', amount: 7800, status: 'paid' },
  { id: 'INV-2024-008', client: 'XYZ Supplies', type: 'Supplier', date: '2024-01-08', dueDate: '2024-02-08', amount: 9200, status: 'pending' },
  { id: 'INV-2024-009', client: 'Robert Brown', type: 'Distributor', date: '2024-01-07', dueDate: '2024-02-07', amount: 15500, status: 'paid' },
  { id: 'INV-2024-010', client: 'Sarah Wilson', type: 'Client', date: '2024-01-06', dueDate: '2024-01-20', amount: 3100, status: 'overdue' },
])

// Computed properties
const filteredInvoices = computed(() => {
  return invoices.value.filter(invoice => {
    if (filters.value.status && invoice.status !== filters.value.status) {
      return false
    }
    
    if (filters.value.type && invoice.type.toLowerCase().replace(/\s/g, '-') !== filters.value.type) {
      return false
    }
    
    if (filters.value.search) {
      const searchTerm = filters.value.search.toLowerCase()
      return (
        invoice.id.toLowerCase().includes(searchTerm) ||
        invoice.client.toLowerCase().includes(searchTerm) ||
        invoice.amount.toString().includes(searchTerm)
      )
    }
    
    return true
  })
})

const paginatedInvoices = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredInvoices.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredInvoices.value.length / itemsPerPage)
})

// Stats
const paidCount = computed(() => {
  return invoices.value.filter(i => i.status === 'paid').length
})

const paidAmount = computed(() => {
  return invoices.value.filter(i => i.status === 'paid').reduce((sum, i) => sum + i.amount, 0)
})

const pendingCount = computed(() => {
  return invoices.value.filter(i => i.status === 'pending').length
})

const pendingAmount = computed(() => {
  return invoices.value.filter(i => i.status === 'pending').reduce((sum, i) => sum + i.amount, 0)
})

const overdueCount = computed(() => {
  return invoices.value.filter(i => i.status === 'overdue').length
})

const overdueAmount = computed(() => {
  return invoices.value.filter(i => i.status === 'overdue').reduce((sum, i) => sum + i.amount, 0)
})

// Methods
const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const isOverdue = (dueDate) => {
  const today = new Date()
  const due = new Date(dueDate)
  return due < today
}

const viewInvoice = (invoice) => {
  selectedInvoice.value = invoice
}

const generateInvoice = () => {
  const newId = `INV-2024-${(invoices.value.length + 101).toString().padStart(3, '0')}`
  const newInvoice = {
    id: newId,
    client: 'New Client',
    type: 'Client',
    date: new Date().toISOString().split('T')[0],
    dueDate: new Date(Date.now() + 30 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    amount: 0,
    status: 'pending'
  }
  
  invoices.value.unshift(newInvoice)
  selectedInvoice.value = newInvoice
}

const downloadPDF = (invoice) => {
  alert(`Downloading PDF for ${invoice.id}`)
}

const sendEmail = (invoice) => {
  alert(`Sending email for ${invoice.id}`)
}

const saveEmailSettings = () => {
  alert('Email settings saved!')
  showEmailSettings.value = false
}

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

// Lifecycle
onMounted(() => {
  console.log('Invoices component mounted')
})
</script>

<style scoped>
/* Modal animations */
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.3s ease;
}

.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}

/* Invoice status colors */
.status-paid {
  background-color: #d1fae5;
  color: #065f46;
}

.status-pending {
  background-color: #fef3c7;
  color: #92400e;
}

.status-overdue {
  background-color: #fee2e2;
  color: #991b1b;
}

/* Responsive adjustments */
@media (max-width: 640px) {
  .invoice-preview .modal-content {
    margin: 0;
    border-radius: 0;
    max-height: 100vh;
  }
  
  .invoice-actions {
    flex-direction: column;
  }
  
  .invoice-actions button {
    width: 100%;
  }
}
</style>