<template>
  <div class="ecommerce-payments p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Payment Management</h1>
          <p class="text-gray-300">Manage customer payments securely and efficiently</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <Button class="bg-gradient-to-r from-emerald-500 to-teal-500 text-white border-0 hover:opacity-90">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Report
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-emerald-500/20 to-teal-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">₱{{ totalRevenue.toLocaleString() }}</div>
          <div class="text-sm text-gray-300">Total Revenue</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ completedPayments }}</div>
          <div class="text-sm text-gray-300">Completed</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ pendingPayments }}</div>
          <div class="text-sm text-gray-300">Pending</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-red-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ refundedPayments }}</div>
          <div class="text-sm text-gray-300">Refunded</div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <Card class="lg:col-span-2 bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold mb-6">Payment Method Distribution</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div v-for="method in paymentMethods" :key="method.name" 
                 class="bg-gray-800/50 rounded-xl p-4 text-center hover:bg-gray-800 transition-colors">
              <div :class="[
                'w-12 h-12 rounded-full mx-auto mb-3 flex items-center justify-center',
                method.color
              ]">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="method.name === 'GCash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                  <path v-else-if="method.name === 'PayMaya'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  <path v-else-if="method.name === 'COD'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                </svg>
              </div>
              <div class="text-white font-medium mb-1">{{ method.name }}</div>
              <div class="text-sm text-gray-400">{{ method.count }} payments</div>
              <div class="text-sm text-emerald-400 mt-1">₱{{ method.amount.toLocaleString() }}</div>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold mb-6">Payment Status Overview</h3>
          <div class="space-y-4">
            <div v-for="status in paymentStatus" :key="status.name" class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="w-3 h-3 rounded-full mr-3" :class="status.color"></div>
                <span class="text-gray-300">{{ status.name }}</span>
              </div>
              <div class="flex items-center">
                <span class="text-white font-medium mr-2">{{ status.count }}</span>
                <span class="text-gray-400 text-sm">{{ status.percentage }}%</span>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="space-y-2">
            <Label class="text-gray-300">Order ID</Label>
            <Input type="text" v-model="searchOrderId" placeholder="Search order ID..." 
                   class="bg-gray-800 border-gray-700 text-white placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Payment Method</Label>
            <Select v-model="selectedMethod">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Methods" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_methods_placeholder">All Methods</SelectItem>
                <SelectItem value="GCash">GCash</SelectItem>
                <SelectItem value="PayMaya">PayMaya</SelectItem>
                <SelectItem value="Bank Transfer">Bank Transfer</SelectItem>
                <SelectItem value="COD">COD</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_status_placeholder">All Status</SelectItem>
                <SelectItem value="Completed">Completed</SelectItem>
                <SelectItem value="Pending">Pending</SelectItem>
                <SelectItem value="Failed">Failed</SelectItem>
                <SelectItem value="Refunded">Refunded</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Date Range</Label>
            <Select v-model="dateRange">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Time" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_time_placeholder">All Time</SelectItem>
                <SelectItem value="today">Today</SelectItem>
                <SelectItem value="week">This Week</SelectItem>
                <SelectItem value="month">This Month</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-end">
            <Button @click="resetFilters" variant="outline" class="w-full bg-gray-800 border-gray-700 text-white hover:bg-gray-700">
              Reset
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-900/80">
            <TableRow class="hover:bg-transparent border-gray-800">
              <TableHead class="text-gray-300">Payment ID</TableHead>
              <TableHead class="text-gray-300">Order ID</TableHead>
              <TableHead class="text-gray-300">Client</TableHead>
              <TableHead class="text-gray-300">Payment Method</TableHead>
              <TableHead class="text-gray-300">Amount</TableHead>
              <TableHead class="text-gray-300">Status</TableHead>
              <TableHead class="text-gray-300">Date</TableHead>
              <TableHead class="text-gray-300">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="payment in filteredPayments" :key="payment.id" 
                class="border-gray-800 hover:bg-white/5">
              <TableCell>
                <div class="font-mono text-white font-medium">{{ payment.paymentId }}</div>
              </TableCell>
              <TableCell>
                <div class="font-mono text-gray-300">{{ payment.orderId }}</div>
              </TableCell>
              <TableCell>
                <div class="flex items-center">
                  <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-xs mr-3">
                    {{ payment.client.charAt(0) }}
                  </div>
                  <span class="text-gray-300">{{ payment.client }}</span>
                </div>
              </TableCell>
              <TableCell>
                <div class="flex items-center">
                  <div :class="[
                    'w-8 h-8 rounded-lg mr-3 flex items-center justify-center',
                    methodColors[payment.method]
                  ]">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path v-if="payment.method === 'GCash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                      <path v-else-if="payment.method === 'PayMaya'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      <path v-else-if="payment.method === 'COD'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                      <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                    </svg>
                  </div>
                  <span class="text-gray-300">{{ payment.method }}</span>
                </div>
              </TableCell>
              <TableCell>
                <div class="text-white font-medium">₱{{ payment.amount.toLocaleString() }}</div>
              </TableCell>
              <TableCell>
                <Badge :class="[
                  'rounded-full border-0 font-medium',
                  statusClasses[payment.status]
                ]">
                  {{ payment.status }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="text-gray-300">{{ payment.date }}</div>
                <div class="text-sm text-gray-400">{{ payment.time }}</div>
              </TableCell>
              <TableCell>
                <div class="flex space-x-2">
                  <Button size="icon" variant="ghost" @click="viewPayment(payment)" 
                         class="text-blue-400 hover:text-blue-300 hover:bg-blue-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                  </Button>
                  <Button size="icon" variant="ghost" v-if="payment.status === 'Pending'" @click="confirmPayment(payment)" 
                         class="text-green-400 hover:text-green-300 hover:bg-green-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                  </Button>
                  <Button size="icon" variant="ghost" v-if="payment.status === 'Completed'" @click="processRefund(payment)" 
                         class="text-red-400 hover:text-red-300 hover:bg-red-500/20">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
      
      <div class="p-4 border-t border-gray-800 flex items-center justify-between">
        <div class="text-sm text-gray-400">
          Showing {{ filteredPayments.length }} of {{ payments.length }} payments
        </div>
        <div class="flex space-x-2">
          <Button size="sm" variant="outline" class="bg-gray-800 text-gray-300 border-0 hover:bg-gray-700">
            Previous
          </Button>
          <Button size="sm" class="bg-gradient-to-r from-indigo-500 to-purple-500 text-white border-0">
            1
          </Button>
          <Button size="sm" variant="outline" class="bg-gray-800 text-gray-300 border-0 hover:bg-gray-700">
            2
          </Button>
          <Button size="sm" variant="outline" class="bg-gray-800 text-gray-300 border-0 hover:bg-gray-700">
            Next
          </Button>
        </div>
      </div>
    </Card>

    <Dialog :open="!!selectedPayment" @update:open="(val) => !val && (selectedPayment = null)">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-2xl">
        <DialogHeader class="border-b border-gray-800 pb-4">
          <DialogTitle>Payment Details</DialogTitle>
          <p class="text-gray-400" v-if="selectedPayment">{{ selectedPayment.paymentId }}</p>
        </DialogHeader>
        
        <div class="pt-4" v-if="selectedPayment">
          <div class="space-y-6">
            <Card class="bg-gray-800/50 border-0 text-white">
              <CardContent class="p-4">
                <h4 class="text-sm text-gray-300 mb-3">Payment Information</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <div class="text-xs text-gray-400 mb-1">Order ID</div>
                    <div class="text-white font-medium">{{ selectedPayment.orderId }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400 mb-1">Client</div>
                    <div class="text-white">{{ selectedPayment.client }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400 mb-1">Payment Method</div>
                    <div class="flex items-center">
                      <div :class="[
                        'w-6 h-6 rounded mr-2 flex items-center justify-center',
                        methodColors[selectedPayment.method]
                      ]">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path v-if="selectedPayment.method === 'GCash'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                          <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                        </svg>
                      </div>
                      <span class="text-white">{{ selectedPayment.method }}</span>
                    </div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400 mb-1">Status</div>
                    <Badge :class="[
                      'rounded-full border-0 font-medium',
                      statusClasses[selectedPayment.status]
                    ]">
                      {{ selectedPayment.status }}
                    </Badge>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400 mb-1">Date & Time</div>
                    <div class="text-white">{{ selectedPayment.date }} {{ selectedPayment.time }}</div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-400 mb-1">Amount</div>
                    <div class="text-xl font-bold text-white">₱{{ selectedPayment.amount.toLocaleString() }}</div>
                  </div>
                </div>
              </CardContent>
            </Card>
            
            <Card class="bg-gray-800/50 border-0 text-white">
              <CardContent class="p-4">
                <h4 class="text-sm text-gray-300 mb-3">Receipt Information</h4>
                <div v-if="selectedPayment.receipt" class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-400">Reference Number:</span>
                    <span class="text-white font-mono">{{ selectedPayment.referenceNumber }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-400">Transaction ID:</span>
                    <span class="text-white font-mono">{{ selectedPayment.transactionId }}</span>
                  </div>
                  <div class="border border-dashed border-gray-700 rounded-lg p-4 text-center hover:border-gray-600 transition-colors cursor-pointer">
                    <svg class="w-12 h-12 text-gray-500 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-gray-400">Payment Receipt Available</p>
                    <Button variant="link" class="mt-2 text-sm text-indigo-400 hover:text-indigo-300 p-0 h-auto">
                      Download Receipt
                    </Button>
                  </div>
                </div>
                <div v-else class="text-center py-6">
                  <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                  <p class="text-gray-400">No receipt uploaded</p>
                </div>
              </CardContent>
            </Card>
            
            <Card v-if="selectedPayment.status === 'Refunded'" class="bg-red-500/10 border-red-500/20 text-white">
              <CardContent class="p-4">
                <h4 class="text-sm text-red-300 mb-3">Refund Information</h4>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-400">Refund Date:</span>
                    <span class="text-white">{{ selectedPayment.refundDate }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-400">Refund Amount:</span>
                    <span class="text-white">₱{{ selectedPayment.amount.toLocaleString() }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-400">Refund Method:</span>
                    <span class="text-white">{{ selectedPayment.refundMethod }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-400">Reason:</span>
                    <span class="text-white">{{ selectedPayment.refundReason }}</span>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
          
          <div class="flex justify-end space-x-3 mt-6 pt-6 border-t border-gray-800">
            <Button variant="outline" @click="selectedPayment = null" 
                   class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
              Close
            </Button>
            <Button v-if="selectedPayment.status === 'Pending'" @click="confirmPayment(selectedPayment)" 
                   class="bg-gradient-to-r from-green-500 to-emerald-500 text-white border-0 hover:opacity-90">
              Confirm Payment
            </Button>
            <Button v-if="selectedPayment.status === 'Completed'" @click="processRefund(selectedPayment)" 
                   class="bg-gradient-to-r from-red-500 to-pink-500 text-white border-0 hover:opacity-90">
              Process Refund
            </Button>
          </div>
        </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'

const searchOrderId = ref('')
const selectedMethod = ref('')
const selectedStatus = ref('')
const dateRange = ref('')
const selectedPayment = ref(null)

const statusClasses = {
  'Completed': 'bg-green-500/20 text-green-300',
  'Pending': 'bg-amber-500/20 text-amber-300',
  'Failed': 'bg-red-500/20 text-red-300',
  'Refunded': 'bg-red-500/20 text-red-300'
}

const methodColors = {
  'GCash': 'bg-gradient-to-r from-emerald-500 to-teal-500',
  'PayMaya': 'bg-gradient-to-r from-blue-500 to-cyan-500',
  'Bank Transfer': 'bg-gradient-to-r from-indigo-500 to-purple-500',
  'COD': 'bg-gradient-to-r from-gray-600 to-gray-700'
}

const payments = ref([
  { 
    id: 1, 
    paymentId: 'PAY-2024-001', 
    orderId: 'ORD-2024-001', 
    client: 'Juan Dela Cruz',
    method: 'GCash', 
    amount: 12450, 
    status: 'Completed',
    date: '2024-03-15', 
    time: '14:35',
    referenceNumber: 'GCASH123456789',
    transactionId: 'TX1234567890',
    receipt: true
  },
  { 
    id: 2, 
    paymentId: 'PAY-2024-002', 
    orderId: 'ORD-2024-002', 
    client: 'Maria Santos',
    method: 'PayMaya', 
    amount: 8900, 
    status: 'Completed',
    date: '2024-03-14', 
    time: '10:20',
    referenceNumber: 'PMYA987654321',
    transactionId: 'TX9876543210',
    receipt: true
  },
  { 
    id: 3, 
    paymentId: 'PAY-2024-003', 
    orderId: 'ORD-2024-003', 
    client: 'Pedro Reyes',
    method: 'Bank Transfer', 
    amount: 15600, 
    status: 'Pending',
    date: '2024-03-13', 
    time: '16:50',
    referenceNumber: '',
    transactionId: '',
    receipt: false
  },
  { 
    id: 4, 
    paymentId: 'PAY-2024-004', 
    orderId: 'ORD-2024-004', 
    client: 'Ana Lopez',
    method: 'COD', 
    amount: 6750, 
    status: 'Pending',
    date: '2024-03-12', 
    time: '09:25',
    referenceNumber: '',
    transactionId: '',
    receipt: false
  },
  { 
    id: 5, 
    paymentId: 'PAY-2024-005', 
    orderId: 'ORD-2024-005', 
    client: 'Luis Garcia',
    method: 'GCash', 
    amount: 11200, 
    status: 'Refunded',
    date: '2024-03-11', 
    time: '11:15',
    referenceNumber: 'GCASH555666777',
    transactionId: 'TX5556667770',
    receipt: true,
    refundDate: '2024-03-12',
    refundMethod: 'GCash',
    refundReason: 'Product damaged upon delivery'
  },
  { 
    id: 6, 
    paymentId: 'PAY-2024-006', 
    orderId: 'ORD-2024-006', 
    client: 'Carla Lim',
    method: 'PayMaya', 
    amount: 8900, 
    status: 'Failed',
    date: '2024-03-10', 
    time: '13:40',
    referenceNumber: '',
    transactionId: '',
    receipt: false
  }
])

const paymentMethods = computed(() => {
  const methods = {}
  payments.value.forEach(payment => {
    if (!methods[payment.method]) {
      methods[payment.method] = { name: payment.method, count: 0, amount: 0 }
    }
    methods[payment.method].count++
    methods[payment.method].amount += payment.amount
  })
  
  return Object.values(methods).map(method => ({
    ...method,
    color: methodColors[method.name] || 'bg-gradient-to-r from-gray-500 to-gray-600'
  }))
})

const paymentStatus = computed(() => {
  const statuses = {}
  payments.value.forEach(payment => {
    if (!statuses[payment.status]) {
      statuses[payment.status] = { name: payment.status, count: 0 }
    }
    statuses[payment.status].count++
  })
  
  const total = payments.value.length
  return Object.values(statuses).map(status => ({
    ...status,
    percentage: Math.round((status.count / total) * 100),
    color: status.name === 'Completed' ? 'bg-green-500' :
           status.name === 'Pending' ? 'bg-amber-500' :
           status.name === 'Refunded' ? 'bg-red-500' : 'bg-gray-500'
  }))
})

const totalRevenue = computed(() => {
  return payments.value
    .filter(p => p.status === 'Completed')
    .reduce((sum, p) => sum + p.amount, 0)
})

const completedPayments = computed(() => {
  return payments.value.filter(p => p.status === 'Completed').length
})

const pendingPayments = computed(() => {
  return payments.value.filter(p => p.status === 'Pending').length
})

const refundedPayments = computed(() => {
  return payments.value.filter(p => p.status === 'Refunded').length
})

const filteredPayments = computed(() => {
  return payments.value.filter(payment => {
    const matchesOrderId = searchOrderId.value === '' || 
      payment.orderId.toLowerCase().includes(searchOrderId.value.toLowerCase()) ||
      payment.paymentId.toLowerCase().includes(searchOrderId.value.toLowerCase())
    
    // Check against placeholder values from Shadcn Select
    const matchesMethod = selectedMethod.value === '' || selectedMethod.value === 'all_methods_placeholder' ||
      payment.method === selectedMethod.value
    
    const matchesStatus = selectedStatus.value === '' || selectedStatus.value === 'all_status_placeholder' ||
      payment.status === selectedStatus.value
    
    return matchesOrderId && matchesMethod && matchesStatus
  })
})

const resetFilters = () => {
  searchOrderId.value = ''
  selectedMethod.value = ''
  selectedStatus.value = ''
  dateRange.value = ''
}

const viewPayment = (payment) => {
  selectedPayment.value = payment
}

const confirmPayment = (payment) => {
  if (confirm(`Confirm payment of ₱${payment.amount.toLocaleString()} from ${payment.client}?`)) {
    payment.status = 'Completed'
    if (selectedPayment.value) {
      selectedPayment.value.status = 'Completed'
    }
  }
}

const processRefund = (payment) => {
  const reason = prompt('Enter refund reason:')
  if (reason) {
    payment.status = 'Refunded'
    payment.refundDate = new Date().toISOString().split('T')[0]
    payment.refundMethod = payment.method
    payment.refundReason = reason
    if (selectedPayment.value) {
      selectedPayment.value.status = 'Refunded'
      selectedPayment.value.refundDate = payment.refundDate
      selectedPayment.value.refundMethod = payment.refundMethod
      selectedPayment.value.refundReason = payment.refundReason
    }
  }
}
</script>

<style scoped>
.ecommerce-payments {
  min-height: 100vh;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-payments {
    padding: 1rem;
  }
}
</style>