<template>
  <div class="max-w-8xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
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
        <Button class="bg-orange-600 hover:bg-orange-700 text-white gap-2" @click="generateInvoice">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          <span>Generate Invoice</span>
        </Button>
        <Button variant="outline" class="gap-2 text-gray-700" @click="showEmailSettings = true">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
          <span>Email Settings</span>
        </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <Card class="flex items-center p-6 gap-4 shadow-sm border-gray-200">
        <div class="w-12 h-12 rounded-xl bg-gray-100 text-gray-600 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <div class="flex-1">
          <h3 class="text-sm font-medium text-gray-600 mb-1">Total Invoices</h3>
          <p class="text-xl font-bold text-gray-900">{{ invoices.length }}</p>
        </div>
      </Card>
      
      <Card class="flex items-center p-6 gap-4 shadow-sm border-gray-200">
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
      </Card>
      
      <Card class="flex items-center p-6 gap-4 shadow-sm border-gray-200">
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
      </Card>
      
      <Card class="flex items-center p-6 gap-4 shadow-sm border-gray-200">
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
      </Card>
    </div>

    <div class="space-y-4 mb-6">
      <div class="relative">
        <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <Input 
          v-model="filters.search" 
          type="text" 
          placeholder="Search by invoice ID, client, amount..."
          class="pl-10 focus-visible:ring-orange-500"
        />
      </div>
      
      <div class="flex flex-wrap gap-3">
        <div class="w-full sm:w-[180px]">
          <Select v-model="filters.status">
            <SelectTrigger>
              <SelectValue placeholder="All Status" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Status</SelectItem>
              <SelectItem value="paid">Paid</SelectItem>
              <SelectItem value="pending">Pending</SelectItem>
              <SelectItem value="overdue">Overdue</SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <div class="w-full sm:w-[180px]">
          <Select v-model="filters.type">
            <SelectTrigger>
              <SelectValue placeholder="All Types" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Types</SelectItem>
              <SelectItem value="client">Client</SelectItem>
              <SelectItem value="distributor">Distributor</SelectItem>
              <SelectItem value="service-provider">Service Provider</SelectItem>
            </SelectContent>
          </Select>
        </div>
        
        <div class="w-full sm:w-[180px]">
          <Select v-model="filters.dateRange">
            <SelectTrigger>
              <SelectValue placeholder="Date Range" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Time</SelectItem>
              <SelectItem value="today">Today</SelectItem>
              <SelectItem value="week">This Week</SelectItem>
              <SelectItem value="month">This Month</SelectItem>
              <SelectItem value="quarter">This Quarter</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>

    <Card class="overflow-hidden mb-8 border-gray-200">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead>Invoice ID</TableHead>
              <TableHead>Client / Distributor / Service Provider</TableHead>
              <TableHead>Date</TableHead>
              <TableHead>Due Date</TableHead>
              <TableHead>Amount</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="invoice in filteredInvoices" :key="invoice.id">
              <TableCell class="font-mono text-sm text-gray-900 font-medium">{{ invoice.id }}</TableCell>
              <TableCell>
                <div class="flex flex-col">
                  <span class="font-medium text-gray-900">{{ invoice.client }}</span>
                  <span class="text-xs text-gray-500">{{ invoice.type }}</span>
                </div>
              </TableCell>
              <TableCell class="text-sm text-gray-700">{{ formatDate(invoice.date) }}</TableCell>
              <TableCell :class="['text-sm', isOverdue(invoice.dueDate) ? 'text-red-600 font-medium' : 'text-gray-700']">
                {{ formatDate(invoice.dueDate) }}
              </TableCell>
              <TableCell class="text-sm font-semibold text-gray-900">₱{{ invoice.amount.toLocaleString() }}</TableCell>
              <TableCell>
                <Badge :class="[
                  'rounded-full font-medium shadow-none hover:bg-opacity-100', 
                  invoice.status === 'paid' ? 'bg-green-100 text-green-800 hover:bg-green-100' : 
                  invoice.status === 'pending' ? 'bg-yellow-100 text-yellow-800 hover:bg-yellow-100' : 
                  'bg-red-100 text-red-800 hover:bg-red-100']">
                  {{ invoice.status }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex gap-2">
                  <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-600 hover:text-blue-600 hover:bg-blue-50" @click="viewInvoice(invoice)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </Button>
                  <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-600 hover:text-green-600 hover:bg-green-50" @click="downloadPDF(invoice)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                  </Button>
                  <Button variant="ghost" size="icon" class="h-8 w-8 text-gray-600 hover:text-orange-600 hover:bg-orange-50" @click="sendEmail(invoice)">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>

      <div v-if="filteredInvoices.length === 0" class="py-12 text-center">
        <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No invoices found</h3>
        <p class="text-gray-500">Try adjusting your filters or generate a new invoice</p>
      </div>

      <div v-if="filteredInvoices.length > 0" class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
        <Button 
          variant="outline"
          size="icon"
          @click="prevPage" 
          :disabled="currentPage === 1"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
        </Button>
        
        <span class="text-sm text-gray-700">
          Page {{ currentPage }} of {{ totalPages }}
        </span>
        
        <Button 
          variant="outline"
          size="icon"
          @click="nextPage" 
          :disabled="currentPage === totalPages"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </Button>
      </div>
    </Card>

    <Dialog :open="!!selectedInvoice" @update:open="val => !val && (selectedInvoice = null)">
      <DialogContent class="sm:max-w-3xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Invoice Preview: {{ selectedInvoice?.id }}</DialogTitle>
        </DialogHeader>
        
        <div v-if="selectedInvoice" class="space-y-6 py-4">
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
          
          <div class="space-y-2">
            <h4 class="text-lg font-semibold text-gray-900">Bill To</h4>
            <div class="space-y-1">
              <p class="font-medium text-gray-900">{{ selectedInvoice.client }}</p>
              <p class="text-sm text-gray-600">{{ selectedInvoice.type }}</p>
              <p class="text-sm text-gray-600">Sample Address, Cavite</p>
              <p class="text-sm text-gray-600">client@email.com</p>
            </div>
          </div>
          
          <div class="overflow-x-auto">
            <Table>
              <TableHeader class="bg-gray-50">
                <TableRow>
                  <TableHead>Description</TableHead>
                  <TableHead>Quantity</TableHead>
                  <TableHead>Unit Price</TableHead>
                  <TableHead>Total</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow>
                  <TableCell>Paint Supplies - Premium Quality</TableCell>
                  <TableCell>10</TableCell>
                  <TableCell>₱{{ (selectedInvoice.amount / 10).toLocaleString() }}</TableCell>
                  <TableCell>₱{{ selectedInvoice.amount.toLocaleString() }}</TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
          
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
          
          <div class="p-4 bg-gray-50 rounded-lg space-y-2">
            <h4 class="text-lg font-semibold text-gray-900">Payment Instructions</h4>
            <p class="text-sm text-gray-700">Please make payment to:</p>
            <p class="text-sm text-gray-700"><strong>Bank:</strong> CaviteGo Paint Bank</p>
            <p class="text-sm text-gray-700"><strong>Account #:</strong> 1234-5678-9012</p>
            <p class="text-sm text-gray-700"><strong>Due Date:</strong> {{ formatDate(selectedInvoice.dueDate) }}</p>
          </div>
        </div>
        
        <DialogFooter class="flex gap-3">
          <Button variant="outline" @click="selectedInvoice = null">
            Close
          </Button>
          <Button class="bg-orange-600 hover:bg-orange-700 text-white" @click="downloadPDF(selectedInvoice)">
            Download PDF
          </Button>
          <Button class="bg-green-600 hover:bg-green-700 text-white" @click="sendEmail(selectedInvoice)">
            Send Email
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <Dialog v-model:open="showEmailSettings">
      <DialogContent class="sm:max-w-2xl max-h-[90vh] overflow-y-auto">
        <DialogHeader>
          <DialogTitle>Email Notification Settings</DialogTitle>
        </DialogHeader>
        
        <div class="space-y-6 py-4">
          <div class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900">Invoice Notifications</h4>
            <div class="flex items-center space-x-2">
              <Checkbox id="sendOnCreate" :checked="true" class="data-[state=checked]:bg-orange-600 data-[state=checked]:border-orange-600" />
              <Label for="sendOnCreate" class="text-sm text-gray-700 font-normal">Send email when invoice is created</Label>
            </div>
            <div class="flex items-center space-x-2">
              <Checkbox id="sendReminders" :checked="true" class="data-[state=checked]:bg-orange-600 data-[state=checked]:border-orange-600" />
              <Label for="sendReminders" class="text-sm text-gray-700 font-normal">Send payment reminders</Label>
            </div>
            <div class="flex items-center space-x-2">
              <Checkbox id="sendOnPayment" :checked="true" class="data-[state=checked]:bg-orange-600 data-[state=checked]:border-orange-600" />
              <Label for="sendOnPayment" class="text-sm text-gray-700 font-normal">Send confirmation on payment</Label>
            </div>
          </div>
          
          <div class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900">Reminder Schedule</h4>
            <div class="space-y-2">
              <Label class="text-sm font-medium text-gray-700">Days before due date</Label>
              <Input type="number" class="focus-visible:ring-orange-500" value="7" min="1" max="30" />
            </div>
            <div class="space-y-2">
              <Label class="text-sm font-medium text-gray-700">Follow-up frequency (days)</Label>
              <Input type="number" class="focus-visible:ring-orange-500" value="3" min="1" max="7" />
            </div>
          </div>
          
          <div class="space-y-4">
            <h4 class="text-lg font-medium text-gray-900">Email Template</h4>
            <Textarea class="min-h-[100px] focus-visible:ring-orange-500" :model-value="'Dear [Client Name],\n\nYour invoice #[Invoice Number] for ₱[Amount] is due on [Due Date].\nPlease make payment at your earliest convenience.\n\nThank you,\nCaviteGo Paint Team'" />
          </div>
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="showEmailSettings = false">
            Cancel
          </Button>
          <Button class="bg-orange-600 hover:bg-orange-700 text-white" @click="saveEmailSettings">
            Save Settings
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { Badge } from '@/components/ui/badge'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogFooter } from '@/components/ui/dialog'
import { Card } from '@/components/ui/card'
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

// State
const showEmailSettings = ref(false)
const selectedInvoice = ref(null)
const currentPage = ref(1)
const itemsPerPage = 10

const filters = ref({
  search: '',
  status: 'all',
  type: 'all',
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
    if (filters.value.status && filters.value.status !== 'all' && invoice.status !== filters.value.status) {
      return false
    }
    
    if (filters.value.type && filters.value.type !== 'all' && invoice.type.toLowerCase().replace(/\s/g, '-') !== filters.value.type) {
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
/* Responsive adjustments */
@media (max-width: 640px) {
  .invoice-actions {
    flex-direction: column;
  }
  
  .invoice-actions button {
    width: 100%;
  }
}
</style>