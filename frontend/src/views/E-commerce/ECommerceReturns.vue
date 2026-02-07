<template>
  <div class="ecommerce-returns p-4 md:p-6">
    <div class="mb-6 md:mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Returns & Refunds</h1>
          <p class="text-gray-300">Manage product returns and refund requests</p>
        </div>
        <div class="mt-4 md:mt-0 flex space-x-3">
          <Button class="bg-gradient-to-r from-red-500 to-pink-500 text-white border-0 hover:opacity-90">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2 -2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Report
          </Button>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
      <Card class="bg-gradient-to-br from-red-500/20 to-pink-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ returnRequests.length }}</div>
          <div class="text-sm text-gray-300">Total Returns</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-amber-500/20 to-yellow-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ pendingReturns }}</div>
          <div class="text-sm text-gray-300">Pending Review</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-green-500/20 to-emerald-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">{{ approvedReturns }}</div>
          <div class="text-sm text-gray-300">Approved</div>
        </CardContent>
      </Card>
      <Card class="bg-gradient-to-br from-blue-500/20 to-cyan-500/20 border-gray-800 text-white">
        <CardContent class="p-4">
          <div class="text-2xl font-bold mb-1">₱{{ totalRefunded.toLocaleString() }}</div>
          <div class="text-sm text-gray-300">Total Refunded</div>
        </CardContent>
      </Card>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
      <Card class="lg:col-span-2 bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Return Reasons</h3>
          <div class="space-y-4">
            <div v-for="reason in returnReasons" :key="reason.name" class="space-y-2">
              <div class="flex items-center justify-between">
                <span class="text-gray-300">{{ reason.name }}</span>
                <span class="text-white font-medium">{{ reason.count }}</span>
              </div>
              <Progress :model-value="reason.percentage" 
                       class="h-2 bg-gray-700" 
                       :indicator-class="reason.color" />
              <div class="text-xs text-gray-400">{{ reason.percentage }}% of total returns</div>
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card class="bg-gray-900/50 backdrop-blur-sm border-gray-800 text-white">
        <CardContent class="p-6">
          <h3 class="text-lg font-semibold text-white mb-6">Quick Actions</h3>
          <div class="space-y-4">
            <Button @click="showReturnForm = true" 
                   class="w-full h-auto p-4 bg-gradient-to-r from-red-500/20 to-pink-500/20 border border-red-500/30 hover:from-red-500/30 hover:to-pink-500/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-red-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
              <span class="text-white">Process New Return</span>
            </Button>
            <Button @click="viewReturnHistory" 
                   class="w-full h-auto p-4 bg-gradient-to-r from-blue-500/20 to-cyan-500/20 border border-blue-500/30 hover:from-blue-500/30 hover:to-cyan-500/30 flex items-center justify-center">
              <svg class="w-6 h-6 text-blue-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
              <span class="text-white">View Return History</span>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <Card class="mb-6 bg-gray-900/50 border-gray-800">
      <CardContent class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div class="md:col-span-2 space-y-2">
            <Label class="text-gray-300">Search</Label>
            <Input type="text" v-model="searchQuery" placeholder="Search by order ID or client..." 
                   class="bg-gray-800 border-gray-700 text-white placeholder:text-gray-500" />
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Status</Label>
            <Select v-model="selectedStatus">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Status" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_status_placeholder">All Status</SelectItem>
                <SelectItem value="Pending">Pending</SelectItem>
                <SelectItem value="Approved">Approved</SelectItem>
                <SelectItem value="Rejected">Rejected</SelectItem>
                <SelectItem value="Refunded">Refunded</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="space-y-2">
            <Label class="text-gray-300">Reason</Label>
            <Select v-model="selectedReason">
              <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                <SelectValue placeholder="All Reasons" />
              </SelectTrigger>
              <SelectContent class="bg-gray-800 border-gray-700 text-white">
                <SelectItem value="all_reasons_placeholder">All Reasons</SelectItem>
                <SelectItem value="Damaged Item">Damaged Item</SelectItem>
                <SelectItem value="Wrong Item">Wrong Item</SelectItem>
                <SelectItem value="Not as Described">Not as Described</SelectItem>
                <SelectItem value="Changed Mind">Changed Mind</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex items-end">
            <Button @click="resetFilters" variant="outline" 
                   class="w-full bg-gray-800 border-gray-700 text-white hover:bg-gray-700">
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
              <TableHead class="text-gray-300">Return ID</TableHead>
              <TableHead class="text-gray-300">Order Details</TableHead>
              <TableHead class="text-gray-300">Reason</TableHead>
              <TableHead class="text-gray-300">Status</TableHead>
              <TableHead class="text-gray-300">Refund Amount</TableHead>
              <TableHead class="text-gray-300">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="returnReq in filteredReturns" :key="returnReq.id" 
                class="border-gray-800 hover:bg-white/5">
              <TableCell>
                <div class="font-mono text-white font-medium">{{ returnReq.returnId }}</div>
                <div class="text-sm text-gray-400">{{ returnReq.date }}</div>
              </TableCell>
              <TableCell>
                <div class="flex items-center">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                    </svg>
                  </div>
                  <div>
                    <div class="text-white">{{ returnReq.product }}</div>
                    <div class="text-sm text-gray-400">Order: {{ returnReq.orderId }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <div class="text-gray-300">{{ returnReq.reason }}</div>
                <div v-if="returnReq.details" class="text-sm text-gray-400">{{ returnReq.details }}</div>
              </TableCell>
              <TableCell>
                <Badge :class="[
                  'rounded-full border-0 font-medium',
                  statusClasses[returnReq.status]
                ]">
                  {{ returnReq.status }}
                </Badge>
              </TableCell>
              <TableCell>
                <div v-if="returnReq.status !== 'Rejected'" class="text-white font-medium">
                  ₱{{ returnReq.refundAmount.toLocaleString() }}
                </div>
                <div v-else class="text-gray-400">-</div>
              </TableCell>
              <TableCell>
                <div class="flex space-x-2">
                  <Button size="sm" variant="ghost" @click="reviewReturn(returnReq)" 
                         class="bg-blue-500/20 text-blue-300 hover:bg-blue-500/30 hover:text-blue-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Review
                  </Button>
                  <Button size="sm" variant="ghost" v-if="returnReq.status === 'Approved'" @click="processRefund(returnReq)" 
                         class="bg-green-500/20 text-green-300 hover:bg-green-500/30 hover:text-green-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                    </svg>
                    Refund
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </Card>

    <Dialog v-model:open="showReturnForm">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>Process Return Request</DialogTitle>
        </DialogHeader>
        
        <form @submit.prevent="submitReturn" class="space-y-6 pt-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <Label class="text-gray-300">Order ID *</Label>
              <Select v-model="returnForm.orderId">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select Order" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="ORD-2024-001">ORD-2024-001 - Juan Dela Cruz</SelectItem>
                  <SelectItem value="ORD-2024-002">ORD-2024-002 - Maria Santos</SelectItem>
                  <SelectItem value="ORD-2024-003">ORD-2024-003 - Pedro Reyes</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Return Reason *</Label>
              <Select v-model="returnForm.reason">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Select Reason" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="Damaged Item">Damaged Item</SelectItem>
                  <SelectItem value="Wrong Item">Wrong Item</SelectItem>
                  <SelectItem value="Not as Described">Not as Described</SelectItem>
                  <SelectItem value="Changed Mind">Changed Mind</SelectItem>
                  <SelectItem value="Defective">Defective Product</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="md:col-span-2 space-y-2">
              <Label class="text-gray-300">Return Details *</Label>
              <Textarea v-model="returnForm.details" rows="3" required 
                       class="bg-gray-800 border-gray-700 text-white"
                       placeholder="Describe the issue..." />
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Requested Refund Amount *</Label>
              <div class="relative">
                <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 z-10">₱</span>
                <Input type="number" v-model="returnForm.refundAmount" required 
                       class="pl-8 bg-gray-800 border-gray-700 text-white" />
              </div>
            </div>
            
            <div class="space-y-2">
              <Label class="text-gray-300">Refund Method</Label>
              <Select v-model="returnForm.refundMethod">
                <SelectTrigger class="bg-gray-800 border-gray-700 text-white">
                  <SelectValue placeholder="Original Payment Method" />
                </SelectTrigger>
                <SelectContent class="bg-gray-800 border-gray-700 text-white">
                  <SelectItem value="Original Payment">Original Payment Method</SelectItem>
                  <SelectItem value="GCash">GCash</SelectItem>
                  <SelectItem value="Bank Transfer">Bank Transfer</SelectItem>
                  <SelectItem value="Store Credit">Store Credit</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
          
          <div class="space-y-2">
            <Label class="text-gray-300">Upload Proof</Label>
            <div class="border-2 border-dashed border-gray-700 rounded-lg p-6 text-center hover:border-gray-600 transition-colors cursor-pointer">
              <svg class="w-12 h-12 text-gray-500 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="text-gray-400">Drag & drop images or click to browse</p>
              <p class="text-sm text-gray-500 mt-1">Upload photos of damaged/wrong items</p>
            </div>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4 border-t border-gray-800">
            <Button type="button" variant="outline" @click="showReturnForm = false" 
                   class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
              Cancel
            </Button>
            <Button type="submit" 
                   class="bg-gradient-to-r from-red-500 to-pink-500 text-white border-0 hover:opacity-90">
              Submit Return
            </Button>
          </div>
        </form>
      </DialogContent>
    </Dialog>

    <Dialog :open="!!selectedReturn" @update:open="(val) => !val && (selectedReturn = null)">
      <DialogContent class="bg-gray-900 border-gray-800 text-white sm:max-w-2xl">
        <DialogHeader>
          <DialogTitle>Review Return Request</DialogTitle>
        </DialogHeader>
        
        <div class="space-y-6 pt-4" v-if="selectedReturn">
          <Card class="bg-gray-800/50 border-0 text-white">
            <CardContent class="p-4">
              <h4 class="text-sm text-gray-300 mb-3">Return Details</h4>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-xs text-gray-400 mb-1">Return ID</div>
                  <div class="text-white font-medium">{{ selectedReturn.returnId }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Order ID</div>
                  <div class="text-white">{{ selectedReturn.orderId }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Reason</div>
                  <div class="text-white">{{ selectedReturn.reason }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-400 mb-1">Requested Amount</div>
                  <div class="text-white">₱{{ selectedReturn.refundAmount.toLocaleString() }}</div>
                </div>
              </div>
              <div class="mt-4">
                <div class="text-xs text-gray-400 mb-1">Description</div>
                <div class="text-gray-300">{{ selectedReturn.details }}</div>
              </div>
            </CardContent>
          </Card>
          
          <Card class="bg-gray-800/50 border-0 text-white">
            <CardContent class="p-4">
              <h4 class="text-sm text-gray-300 mb-3">Client Information</h4>
              <div class="flex items-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500 flex items-center justify-center text-white text-lg mr-4">
                  {{ selectedReturn.client.charAt(0) }}
                </div>
                <div>
                  <div class="text-white font-medium">{{ selectedReturn.client }}</div>
                  <div class="text-sm text-gray-400">{{ selectedReturn.email }}</div>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card class="bg-gray-800/50 border-0 text-white">
            <CardContent class="p-4">
              <h4 class="text-sm text-gray-300 mb-3">Proof Images</h4>
              <div class="grid grid-cols-2 gap-4">
                <div class="border border-gray-700 rounded-lg p-4 text-center">
                  <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-400">Damaged Product</p>
                </div>
                <div class="border border-gray-700 rounded-lg p-4 text-center">
                  <svg class="w-12 h-12 text-gray-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  <p class="text-sm text-gray-400">Package Condition</p>
                </div>
              </div>
            </CardContent>
          </Card>
          
          <Card class="bg-gray-800/50 border-0 text-white">
            <CardContent class="p-4">
              <h4 class="text-sm text-gray-300 mb-3">Review Decision</h4>
              <div class="space-y-4">
                <div class="space-y-2">
                  <Label class="text-gray-300">Approved Refund Amount</Label>
                  <div class="relative">
                    <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 z-10">₱</span>
                    <Input type="number" v-model="reviewForm.approvedAmount" 
                           class="pl-8 bg-gray-800 border-gray-700 text-white"
                           :placeholder="selectedReturn.refundAmount.toString()" />
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="text-gray-300">Decision Notes</Label>
                  <Textarea v-model="reviewForm.notes" rows="2" 
                           class="bg-gray-800 border-gray-700 text-white" />
                </div>
              </div>
            </CardContent>
          </Card>
          
          <div class="flex justify-between mt-6 pt-6 border-t border-gray-800">
            <Button @click="rejectReturn(selectedReturn)" variant="outline" 
                   class="bg-red-500/20 text-red-300 border-red-500/30 hover:bg-red-500/30 hover:text-red-200">
              Reject
            </Button>
            <div class="flex space-x-3">
              <Button variant="outline" @click="selectedReturn = null" 
                     class="border-gray-700 text-gray-300 hover:bg-gray-800 hover:text-white bg-transparent">
                Cancel
              </Button>
              <Button @click="approveReturn(selectedReturn)" 
                     class="bg-gradient-to-r from-green-500 to-emerald-500 text-white border-0 hover:opacity-90">
                Approve
              </Button>
            </div>
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
import { Textarea } from '@/components/ui/textarea'
import { Progress } from '@/components/ui/progress'
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

const searchQuery = ref('')
const selectedStatus = ref('')
const selectedReason = ref('')
const showReturnForm = ref(false)
const selectedReturn = ref(null)

const statusClasses = {
  'Pending': 'bg-amber-500/20 text-amber-300',
  'Approved': 'bg-green-500/20 text-green-300',
  'Rejected': 'bg-red-500/20 text-red-300',
  'Refunded': 'bg-blue-500/20 text-blue-300'
}

const returnRequests = ref([
  { 
    id: 1, 
    returnId: 'RET-2024-001', 
    orderId: 'ORD-2024-005', 
    product: 'Premium White Paint',
    reason: 'Damaged Item', 
    details: 'Can arrived dented, paint leaking',
    status: 'Approved',
    date: '2024-03-12',
    refundAmount: 2450,
    client: 'Luis Garcia',
    email: 'luis.garcia@email.com'
  },
  { 
    id: 2, 
    returnId: 'RET-2024-002', 
    orderId: 'ORD-2024-004', 
    product: 'Anti-Mold Exterior Paint',
    reason: 'Wrong Item', 
    details: 'Received interior paint instead of exterior',
    status: 'Pending',
    date: '2024-03-14',
    refundAmount: 3850,
    client: 'Ana Lopez',
    email: 'ana.lopez@email.com'
  },
  { 
    id: 3, 
    returnId: 'RET-2024-003', 
    orderId: 'ORD-2024-003', 
    product: 'Gloss Finish Coating',
    reason: 'Not as Described', 
    details: 'Color does not match swatch',
    status: 'Rejected',
    date: '2024-03-10',
    refundAmount: 2980,
    client: 'Pedro Reyes',
    email: 'pedro.reyes@email.com'
  },
  { 
    id: 4, 
    returnId: 'RET-2024-004', 
    orderId: 'ORD-2024-002', 
    product: 'Weatherproof Exterior Paint',
    reason: 'Defective', 
    details: 'Paint separates after mixing',
    status: 'Refunded',
    date: '2024-03-08',
    refundAmount: 3250,
    client: 'Maria Santos',
    email: 'maria.santos@email.com'
  }
])

const returnForm = ref({
  orderId: '',
  reason: 'Damaged Item',
  details: '',
  refundAmount: '',
  refundMethod: 'Original Payment'
})

const reviewForm = ref({
  approvedAmount: '',
  notes: ''
})

const returnReasons = ref([
  { name: 'Damaged Item', count: 8, percentage: 40, color: 'bg-red-500' },
  { name: 'Wrong Item', count: 5, percentage: 25, color: 'bg-amber-500' },
  { name: 'Not as Described', count: 4, percentage: 20, color: 'bg-blue-500' },
  { name: 'Changed Mind', count: 2, percentage: 10, color: 'bg-purple-500' },
  { name: 'Defective', count: 1, percentage: 5, color: 'bg-gray-500' }
])

const pendingReturns = computed(() => {
  return returnRequests.value.filter(r => r.status === 'Pending').length
})

const approvedReturns = computed(() => {
  return returnRequests.value.filter(r => r.status === 'Approved' || r.status === 'Refunded').length
})

const totalRefunded = computed(() => {
  return returnRequests.value
    .filter(r => r.status === 'Refunded')
    .reduce((sum, r) => sum + r.refundAmount, 0)
})

const filteredReturns = computed(() => {
  return returnRequests.value.filter(returnReq => {
    const matchesSearch = searchQuery.value === '' || 
      returnReq.returnId.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      returnReq.orderId.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      returnReq.client.toLowerCase().includes(searchQuery.value.toLowerCase())
    
    // Check against potential placeholders or empty string
    const matchesStatus = selectedStatus.value === '' || selectedStatus.value === 'all_status_placeholder' ||
      returnReq.status === selectedStatus.value
    
    const matchesReason = selectedReason.value === '' || selectedReason.value === 'all_reasons_placeholder' ||
      returnReq.reason === selectedReason.value
    
    return matchesSearch && matchesStatus && matchesReason
  })
})

const resetFilters = () => {
  searchQuery.value = ''
  selectedStatus.value = ''
  selectedReason.value = ''
}

const reviewReturn = (returnReq) => {
  selectedReturn.value = returnReq
  reviewForm.value.approvedAmount = returnReq.refundAmount
}

const submitReturn = () => {
  const newId = Math.max(...returnRequests.value.map(r => r.id)) + 1
  returnRequests.value.push({
    id: newId,
    returnId: `RET-2024-00${newId}`,
    orderId: returnForm.value.orderId,
    product: 'New Product',
    reason: returnForm.value.reason,
    details: returnForm.value.details,
    status: 'Pending',
    date: new Date().toISOString().split('T')[0],
    refundAmount: parseInt(returnForm.value.refundAmount) || 0,
    client: 'New Client',
    email: 'new@email.com'
  })
  
  showReturnForm.value = false
  returnForm.value = {
    orderId: '',
    reason: 'Damaged Item',
    details: '',
    refundAmount: '',
    refundMethod: 'Original Payment'
  }
}

const approveReturn = (returnReq) => {
  returnReq.status = 'Approved'
  if (reviewForm.value.approvedAmount) {
    returnReq.refundAmount = parseInt(reviewForm.value.approvedAmount)
  }
  selectedReturn.value = null
}

const rejectReturn = (returnReq) => {
  returnReq.status = 'Rejected'
  selectedReturn.value = null
}

const processRefund = (returnReq) => {
  returnReq.status = 'Refunded'
}

const viewReturnHistory = () => {
  alert('Showing return history...')
}
</script>

<style scoped>
.ecommerce-returns {
  min-height: 100vh;
}

/* Mobile optimizations */
@media (max-width: 768px) {
  .ecommerce-returns {
    padding: 1rem;
  }
}
</style>