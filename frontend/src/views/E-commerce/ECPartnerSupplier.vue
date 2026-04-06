<template>
  <div class="p-4 md:p-6 min-h-screen space-y-6 md:space-y-8 text-slate-100 relative">

    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl md:text-3xl font-bold tracking-tight text-white">Partner Suppliers</h1>
        <p class="text-slate-400 mt-1 text-sm md:text-base">Discover and connect with verified suppliers for your distribution network.</p>
      </div>
      <div class="flex items-center gap-2">
         <Button 
            v-if="permissions.can_manage"
            @click="requirePermission('manage', openCreateWizard)" 
            class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-xs md:text-sm"
         >
            <i class="fas fa-plus mr-1 md:mr-2"></i> Create Supplier
         </Button>
         <Button variant="outline" @click="fetchSuppliers" class="border-slate-700 bg-slate-800/50 hover:bg-slate-800 text-slate-300 text-xs md:text-sm">
            <i class="fas fa-sync-alt mr-1 md:mr-2 text-slate-500"></i> Refresh
         </Button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
       <div class="lg:col-span-3">
          <div class="relative">
             <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-500"></i>
             <Input 
                v-model="searchQuery" 
                placeholder="Search suppliers by name, email, or location..." 
                class="pl-10 h-12 text-base md:text-lg bg-slate-900/50 border-slate-700 text-slate-100 placeholder:text-slate-600 focus:border-blue-500 focus:ring-blue-500/20"
             />
          </div>
       </div>
       
       <Card class="bg-gradient-to-br from-blue-900 to-indigo-900 text-white border-blue-800 shadow-lg">
          <CardContent class="p-4 flex items-center justify-between">
             <div>
                <p class="text-blue-200 text-sm font-medium">Available Partners</p>
                <p class="text-2xl font-bold">{{ filteredSuppliers.length }}</p>
             </div>
             <div class="h-10 w-10 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm border border-white/10">
                <i class="fas fa-handshake"></i>
             </div>
          </CardContent>
       </Card>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
       <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mb-4"></div>
       <p class="text-slate-400">Loading suppliers...</p>
    </div>

    <div v-else-if="filteredSuppliers.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <Card 
        v-for="supplier in filteredSuppliers" 
        :key="supplier.id" 
        class="group bg-slate-900/50 border-slate-800 hover:border-slate-600 hover:shadow-xl hover:shadow-blue-900/10 transition-all duration-300 overflow-hidden flex flex-col backdrop-blur-sm"
      >
        <div class="h-24 bg-slate-800/50 relative border-b border-slate-800">
           <div class="absolute inset-0 bg-gradient-to-r from-slate-900/80 to-transparent"></div>
           
           <div class="absolute -bottom-6 left-6">
              <div class="h-16 w-16 rounded-xl border-4 border-slate-900 bg-slate-800 shadow-lg flex items-center justify-center overflow-hidden">
                 <span class="text-xl font-bold text-blue-400 select-none">{{ getInitials(supplier.name) }}</span>
              </div>
           </div>
           <div class="absolute top-3 right-3">
              <Badge 
                 variant="secondary" 
                 class="bg-slate-950/80 backdrop-blur text-slate-300 border border-slate-800 shadow-sm"
              >
                 <i class="fas fa-star text-amber-500 mr-1"></i> {{ supplier.rating }}
              </Badge>
           </div>
        </div>

        <CardContent class="pt-8 px-6 flex-1">
           <div class="mb-4">
              <h3 class="text-lg font-bold text-slate-100 group-hover:text-blue-400 transition-colors">{{ supplier.name }}</h3>
              <div class="flex items-center gap-2 mt-1">
                 <Badge variant="outline" class="text-xs font-normal text-slate-400 border-slate-700 bg-slate-800/50">
                    {{ supplier.category }}
                 </Badge>
                 <span class="text-xs text-slate-600">•</span>
                 <h2 class="text-xs text-slate-500 flex items-center gap-1">
                    <i class="fas fa-map-marker-alt"></i> {{ supplier.location }}
                 </h2>
              </div>
           </div>
           
           <h2 class="text-sm text-slate-400 line-clamp-3 mb-4 leading-relaxed">
              {{ supplier.description }}
           </h2>

           <div class="grid grid-cols-2 gap-2 text-xs text-slate-500 mb-4 bg-slate-950/50 p-3 rounded-lg border border-slate-800">
              <div class="flex flex-col">
                 <span class="text-slate-500">Min. Order</span>
                 <span class="font-semibold text-slate-300">{{ formatCurrency(supplier.min_order) }}</span>
              </div>
              <div class="flex flex-col border-l border-slate-800 pl-2">
                 <span class="text-slate-500">Fulfillment</span>
                 <span class="font-semibold text-slate-300">{{ supplier.fulfillment_rate }}%</span>
              </div>
           </div>
           
           <div v-if="supplier.status !== 'available'" class="mb-2">
              <p class="text-xs text-center text-slate-400">
                Status: 
                <span :class="{
                  'text-amber-400': supplier.status === 'pending',
                  'text-green-400': supplier.status === 'connected',
                  'text-red-400': supplier.status === 'rejected'
                }" class="font-medium capitalize">{{ supplier.partnership_details?.status === 'pending_internal' ? 'Waiting Internal Approval' : supplier.status }}</span>
              </p>
           </div>
        </CardContent>

        <CardFooter class="px-6 pb-6 pt-0 mt-auto">
           <Button 
              v-if="supplier.status === 'available' || supplier.status === 'rejected'"
              @click="requirePermission('manage', () => initiatePartnership(supplier))" 
              class="w-full bg-slate-100 hover:bg-white text-slate-900 transition-all shadow-md hover:shadow-lg font-medium"
           >
              <i class="fas fa-plus-circle mr-2"></i> {{ supplier.status === 'rejected' ? 'Re-apply' : 'Request Partnership' }}
           </Button>
           
           <Button 
              v-else-if="supplier.status === 'pending'"
              disabled
              variant="secondary"
              class="w-full bg-amber-900/20 text-amber-500 border border-amber-900/50"
           >
              <i class="fas fa-clock mr-2"></i> Pending
           </Button>

           <Button 
              v-else-if="supplier.status === 'connected'"
              variant="outline"
              class="w-full text-green-400 border-green-900/50 bg-green-900/20 hover:bg-green-900/30"
           >
              <i class="fas fa-check-circle mr-2"></i> Partnered
           </Button>
        </CardFooter>
      </Card>
    </div>

    <Dialog :open="showCreateWizard" @update:open="showCreateWizard = $event">
      <div v-if="showCreateWizard" class="fixed inset-0 z-50 flex items-center justify-center p-2 md:p-4 bg-black/80 backdrop-blur-md">
        <div class="w-full max-w-3xl bg-slate-900 rounded-2xl shadow-2xl border border-slate-800 overflow-hidden flex flex-col md:flex-row max-h-[95vh] md:max-h-[85vh]">
          
          <div class="md:w-1/3 bg-gradient-to-br from-blue-900/40 to-indigo-900/40 p-4 md:p-6 flex flex-col relative border-b md:border-b-0 md:border-r border-slate-800 shrink-0">
             <div class="absolute inset-0 bg-blue-500/5 mix-blend-overlay"></div>
             <div class="relative z-10">
                <h2 class="text-lg md:text-xl font-bold text-white mb-1 md:mb-2">Create Supplier</h2>
                <p class="text-xs text-slate-400 mb-4 md:mb-8 hidden md:block">Follow the steps to establish a new supplier account in the system.</p>
                
                <div class="flex md:flex-col items-start gap-2 md:gap-6 overflow-x-auto custom-scrollbar pb-2 md:pb-0">
                   <div v-for="(step, index) in createSteps" :key="index" class="flex md:items-start gap-2 md:gap-4 shrink-0">
                      <div class="relative flex md:flex-col items-center">
                         <div class="h-6 w-6 md:h-8 md:w-8 rounded-full border-2 flex items-center justify-center text-[10px] md:text-xs font-bold transition-colors duration-300"
                              :class="createStep > index + 1 ? 'bg-blue-600 border-blue-600 text-white' : createStep === index + 1 ? 'border-blue-500 text-blue-400 bg-slate-900 ring-4 ring-blue-500/20' : 'border-slate-700 text-slate-600 bg-slate-900'">
                            <i v-if="createStep > index + 1" class="fas fa-check"></i>
                            <span v-else>{{ index + 1 }}</span>
                         </div>
                         <div v-if="index < createSteps.length - 1" class="hidden md:block h-10 w-0.5 mt-2 transition-colors duration-300" :class="createStep > index + 1 ? 'bg-blue-600' : 'bg-slate-800'"></div>
                      </div>
                      <div class="pt-0.5 md:pt-1">
                         <h4 class="text-xs md:text-sm font-medium whitespace-nowrap md:whitespace-normal" :class="createStep >= index + 1 ? 'text-white' : 'text-slate-500'">{{ step.title }}</h4>
                         <p class="hidden md:block text-xs text-slate-500 mt-0.5">{{ step.desc }}</p>
                      </div>
                   </div>
                </div>
             </div>
          </div>

          <div class="md:w-2/3 p-4 md:p-6 flex flex-col relative bg-slate-900 overflow-y-auto custom-scrollbar">
            <button @click="showCreateWizard = false" class="absolute top-3 right-3 md:top-4 md:right-4 h-8 w-8 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-700 hover:border-slate-600 transition z-50">
              <i class="fas fa-times text-sm md:text-lg"></i>
            </button>
            
            <div class="flex-1 mt-4 md:mt-6">
              <transition name="fade" mode="out-in">
                <div v-if="createStep === 1" key="step1" class="space-y-4">
                  <h3 class="text-lg font-semibold text-white">Business Information</h3>
                  <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-300">Company / Supplier Name</label>
                    <div class="relative">
                      <i class="fas fa-building absolute left-3 top-3 text-slate-500"></i>
                      <Input 
                        v-model="createForm.companyName" 
                        @input="validateCreateStep(1)"
                        placeholder="e.g. Boysen Corp" 
                        class="pl-10 h-10 bg-slate-950 border-slate-800 text-slate-200 focus:border-blue-500" 
                        :class="{'border-red-500/50': wizardErrors.companyName}"
                      />
                    </div>
                    <span v-if="wizardErrors.companyName" class="text-[10px] text-red-400">{{ wizardErrors.companyName }}</span>
                  </div>
                  <div class="p-3 bg-blue-900/20 border border-blue-900 rounded-lg flex gap-3 text-sm mt-4">
                    <i class="fas fa-info-circle text-blue-400 mt-0.5"></i>
                    <p class="text-blue-300 text-xs">The supplier will be prompted to upload their official legal documents (DTI, Permits) inside their portal once logged in.</p>
                  </div>
                </div>

                <div v-else-if="createStep === 2" key="step2" class="space-y-4">
                  <h3 class="text-lg font-semibold text-white">Contact Person</h3>
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                      <label class="text-xs font-medium text-slate-300">First Name</label>
                      <Input 
                        v-model="createForm.firstName" 
                        @input="validateCreateStep(2)"
                        placeholder="Julian" 
                        class="h-10 bg-slate-950 border-slate-800 text-slate-200 focus:border-blue-500" 
                        :class="{'border-red-500/50': wizardErrors.firstName}"
                      />
                      <span v-if="wizardErrors.firstName" class="text-[10px] text-red-400">{{ wizardErrors.firstName }}</span>
                    </div>
                    <div class="space-y-1.5">
                      <label class="text-xs font-medium text-slate-300">Last Name</label>
                      <Input 
                        v-model="createForm.lastName" 
                        @input="validateCreateStep(2)"
                        placeholder="Namoc" 
                        class="h-10 bg-slate-950 border-slate-800 text-slate-200 focus:border-blue-500" 
                        :class="{'border-red-500/50': wizardErrors.lastName}"
                      />
                      <span v-if="wizardErrors.lastName" class="text-[10px] text-red-400">{{ wizardErrors.lastName }}</span>
                    </div>
                  </div>
                  <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-300">Contact Number</label>
                    <div class="relative">
                      <i class="fas fa-phone absolute left-3 top-3 text-slate-500"></i>
                      <Input 
                        v-model="createForm.phone" 
                        @input="formatPhoneCreate"
                        placeholder="0912 345 6789" 
                        class="pl-10 h-10 bg-slate-950 border-slate-800 text-slate-200 focus:border-blue-500" 
                        :class="{'border-red-500/50': wizardErrors.phone}"
                      />
                    </div>
                    <span v-if="wizardErrors.phone" class="text-[10px] text-red-400">{{ wizardErrors.phone }}</span>
                    <span v-else-if="createForm.phone" class="text-[10px] text-cyan-400">Format: 09XX XXX XXXX</span>
                  </div>
                </div>

                <div v-else-if="createStep === 3" key="step3" class="space-y-4">
                  <h3 class="text-lg font-semibold text-white">Account Details</h3>
                  <div class="space-y-1.5">
                    <label class="text-xs font-medium text-slate-300">Supplier Email</label>
                    <div class="relative">
                      <i class="fas fa-envelope absolute left-3 top-3 text-slate-500"></i>
                      <Input 
                        v-model="createForm.email" 
                        @input="validateCreateStep(3)"
                        type="email" 
                        placeholder="supplier@example.com" 
                        class="pl-10 h-10 bg-slate-950 border-slate-800 text-slate-200 focus:border-blue-500" 
                        :class="{'border-red-500/50': wizardErrors.email}"
                      />
                    </div>
                    <span v-if="wizardErrors.email" class="text-[10px] text-red-400">{{ wizardErrors.email }}</span>
                  </div>

                  <div class="mt-6 p-4 rounded-xl border border-emerald-900/50 bg-emerald-900/10">
                    <div class="flex items-start gap-3">
                      <div class="p-2 rounded-full bg-emerald-500/20 text-emerald-400">
                        <i class="fas fa-magic text-lg"></i>
                      </div>
                      <div>
                        <h4 class="text-sm font-semibold text-emerald-400 mb-1">Automated Actions</h4>
                        <ul class="text-xs text-slate-400 space-y-1 list-disc pl-4">
                           <li>Password will automatically be set to the user's <strong class="text-emerald-300">Last Name</strong>.</li>
                           <li>An email containing credentials will be dispatched to the supplier.</li>
                           <li>An email notification will be sent to the Distributor.</li>
                           <li>A <strong class="text-emerald-300">Partnership Request</strong> will be automatically initiated to fast-track your connection!</li>
                        </ul>
                      </div>
                    </div>
                  </div>
                </div>
              </transition>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-800 flex justify-between shrink-0">
               <Button v-if="createStep > 1" variant="ghost" @click="createStep--" class="text-slate-400 hover:text-white">Back</Button>
               <Button v-else variant="ghost" @click="showCreateWizard = false" class="text-slate-500 hover:text-white">Cancel</Button>

               <Button v-if="createStep < createSteps.length" @click="nextCreateStep" class="bg-blue-600 hover:bg-blue-700 text-white min-w-[100px]">Next</Button>
               
               <Button v-else @click="confirmCreateSupplier" :disabled="isProcessingCreation" class="bg-emerald-600 hover:bg-emerald-700 text-white min-w-[140px]">
                 <i v-if="isProcessingCreation" class="fas fa-circle-notch fa-spin mr-2"></i>
                 {{ isProcessingCreation ? 'Creating...' : 'Create & Partner' }}
               </Button>
            </div>

          </div>
        </div>
      </div>
    </Dialog>

    <AlertDialog :open="showCreateConfirmDialog" @update:open="showCreateConfirmDialog = $event">
      <AlertDialogContent class="bg-slate-900 border border-slate-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-white">Confirm Supplier Creation</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400">
            Are you sure you want to create a new supplier account for <span class="text-blue-400 font-medium">{{ createForm.companyName }}</span>? <br><br>
            This action will automatically:
            <ul class="list-disc pl-5 mt-2 space-y-1 text-xs">
               <li>Send an email with credentials to <strong class="text-slate-300">{{ createForm.email }}</strong>.</li>
               <li>Generate a formal supply partnership document.</li>
            </ul>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-transparent text-slate-300 hover:bg-slate-800 border-slate-700">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executeSubmitCreateSupplier" class="bg-emerald-600 text-white hover:bg-emerald-700">Yes, Create Supplier</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <Dialog :open="showConfirmDialog" @update:open="showConfirmDialog = $event">
      <div v-if="showConfirmDialog && selectedSupplier" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <Card class="w-full max-w-2xl shadow-2xl border border-slate-800 bg-slate-900 animate-in fade-in zoom-in duration-200 flex flex-col max-h-[90vh]">
           <CardHeader class="border-b border-slate-800 bg-slate-900/50 pb-6 shrink-0">
              <div class="flex items-center gap-4">
                 <div class="h-12 w-12 rounded-lg bg-slate-800 border border-slate-700 shadow-sm flex items-center justify-center overflow-hidden shrink-0">
                    <span class="text-lg font-bold text-blue-400 select-none">{{ getInitials(selectedSupplier.name) }}</span>
                 </div>
                 <div>
                    <CardTitle class="text-lg text-white">Partner with {{ selectedSupplier.name }}?</CardTitle>
                    <CardDescription class="text-slate-400">Review the terms and send a formal partnership request.</CardDescription>
                 </div>
              </div>
           </CardHeader>
           
           <CardContent class="pt-6 space-y-6 overflow-y-auto custom-scrollbar">
              <div class="p-3 bg-blue-900/20 text-blue-300 text-sm rounded-md border border-blue-800/50 flex gap-3">
                 <i class="fas fa-info-circle mt-0.5 shrink-0"></i>
                 <p>This request will first be sent to your Business Owner for approval, then forwarded to the supplier alongside a generated formal agreement.</p>
              </div>

              <div class="space-y-2">
                 <label class="text-sm font-medium text-slate-300 flex items-center gap-2">
                    <i class="fas fa-file-contract text-blue-400"></i> Supply Partnership Terms and Conditions
                 </label>
                 <div class="h-48 overflow-y-auto rounded-md border border-slate-700 bg-slate-950/50 p-4 text-xs text-slate-400 space-y-4 custom-scrollbar">
                    <h4 class="font-bold text-slate-200 text-sm text-center border-b border-slate-800 pb-2">STANDARD SUPPLY PARTNERSHIP AGREEMENT</h4>
                    
                    <div>
                        <h5 class="font-semibold text-slate-300">1. Introduction and Scope</h5>
                        <p class="mt-1">This document establishes the official business relationship between the Requesting Distributor and the Supplier. Upon acceptance, the Supplier agrees to provide products to the Distributor according to the terms defined herein.</p>
                    </div>

                    <div>
                        <h5 class="font-semibold text-slate-300">2. Orders and Fulfillment</h5>
                        <p class="mt-1">The Distributor shall place procurement orders through the e-commerce platform. The Supplier commits to fulfilling these orders in a timely manner, subject to stock availability and operational capacity.</p>
                    </div>

                    <div>
                        <h5 class="font-semibold text-slate-300">3. Pricing and Payment Terms</h5>
                        <p class="mt-1">Product pricing is dynamically determined by the Supplier's catalog at the time of order placement. Payment terms (e.g., GCash, Cash on Delivery) must be explicitly agreed upon per transaction. The Distributor is legally obligated to remit full payment as dictated by the chosen payment method.</p>
                    </div>

                    <div>
                        <h5 class="font-semibold text-slate-300">4. Shipping and Risk of Loss</h5>
                        <p class="mt-1">For items shipped directly by the Supplier, the risk of loss, theft, or damage passes to the Distributor upon successful delivery and confirmation of receipt.</p>
                    </div>

                    <div>
                        <h5 class="font-semibold text-slate-300">5. Returns and Warranties</h5>
                        <p class="mt-1">Any claims regarding defective, damaged, or incorrect products must be processed exclusively through the system's official Return Management module.</p>
                    </div>

                    <div>
                        <h5 class="font-semibold text-slate-300">6. Term, Suspension, and Termination</h5>
                        <p class="mt-1">This Agreement remains active until terminated by either party through the platform. All pending financial obligations and active orders must be completely settled prior to the full severance of the partnership.</p>
                    </div>
                 </div>

                 <div class="flex items-start gap-3 mt-3 pt-2 bg-slate-900/30 p-3 rounded-md border border-slate-800">
                    <input 
                        type="checkbox" 
                        id="terms" 
                        v-model="agreedToTerms" 
                        class="mt-0.5 rounded border-slate-600 bg-slate-900 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 h-4 w-4 shrink-0 cursor-pointer" 
                    />
                    <label for="terms" class="text-sm font-medium text-slate-300 cursor-pointer select-none leading-tight">
                        I acknowledge that I have read and agree to the Supply Partnership Terms and Conditions on behalf of my business. I understand a formal digital copy will be generated and stored upon request.
                    </label>
                 </div>
              </div>
              
              <div class="space-y-2">
                 <label class="text-sm font-medium text-slate-300">Message (Optional)</label>
                 <textarea 
                    v-model="requestMessage"
                    class="flex w-full rounded-md border border-slate-700 bg-slate-950 px-3 py-2 text-sm text-slate-200 ring-offset-background placeholder:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 focus-visible:ring-offset-slate-900 disabled:cursor-not-allowed disabled:opacity-50 min-h-[80px]" 
                    placeholder="Introduce your business..."
                 ></textarea>
              </div>
           </CardContent>
           
           <CardFooter class="flex justify-end gap-3 pt-4 pb-6 bg-slate-900 border-t border-slate-800 shrink-0">
              <Button variant="ghost" class="text-slate-400 hover:text-white hover:bg-slate-800" @click="showConfirmDialog = false">Cancel</Button>
              <Button @click="confirmPartnership" :disabled="isProcessing" class="bg-blue-600 hover:bg-blue-700 text-white min-w-[120px]">
                 <i v-if="isProcessing" class="fas fa-circle-notch fa-spin mr-2"></i>
                 {{ isProcessing ? 'Sending...' : 'Send Request' }}
              </Button>
           </CardFooter>
        </Card>
      </div>
    </Dialog>

    <AlertDialog :open="showAlertDialog" @update:open="showAlertDialog = $event">
      <AlertDialogContent class="bg-slate-900 border border-slate-800">
        <AlertDialogHeader>
          <AlertDialogTitle class="text-white">Are you absolutely sure?</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-400">
            This will formally send a partnership request to <span class="text-blue-400 font-medium">{{ selectedSupplier?.name }}</span> and generate your digital agreement based on the terms accepted.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="bg-transparent text-slate-300 hover:bg-slate-800 border-slate-700">Cancel</AlertDialogCancel>
          <AlertDialogAction @click="executePartnershipRequest" class="bg-blue-600 text-white hover:bg-blue-700">Submit Agreement & Request</AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import api from '@/utils/axios' 
import { Toaster, toast } from 'vue-sonner' 
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card'
import { Dialog } from '@/components/ui/dialog'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'

// State
const searchQuery = ref('')
const showConfirmDialog = ref(false)
const showAlertDialog = ref(false)
const selectedSupplier = ref(null)
const isProcessing = ref(false)
const loading = ref(false)
const requestMessage = ref('')
const suppliers = ref([])
const agreedToTerms = ref(false) 

// Wizard States
const showCreateWizard = ref(false)
const showCreateConfirmDialog = ref(false) // Triggered before submission
const createStep = ref(1)
const isProcessingCreation = ref(false)
const wizardErrors = reactive({}) // Holds validation errors
const createSteps = [
  { title: 'Company Details', desc: 'Business Info' },
  { title: 'Contact Details', desc: 'Personal info' },
  { title: 'Account Settings', desc: 'Emails & Logic' }
]
const createForm = reactive({
  companyName: '',
  firstName: '',
  lastName: '',
  phone: '',
  email: ''
})

// User Permissions setup via Level-Based RBAC
const permissions = ref({
  can_view: false,
  can_manage: false,
  can_approve: false
})

// RBAC Action Interceptor
const requirePermission = (action, callback) => {
  const permKey = `can_${action}`
  
  if (!permissions.value[permKey]) {
    toast.error(`Access Denied`, {
        description: `You do not have permission to perform this action.`,
        duration: 5000,
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
    });
    return;
  }
  if (callback) callback();
}

// Methods
const getInitials = (name) => {
  if (!name) return '??';
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
}

const formatCurrency = (value) => {
  if (!value) return '₱0.00';
  return new Intl.NumberFormat('en-PH', {
    style: 'currency',
    currency: 'PHP',
    minimumFractionDigits: 0
  }).format(value);
}

const fetchSuppliers = async () => {
  loading.value = true;
  try {
    const response = await api.get('/partners/suppliers');

    if (response.data.success) {
      suppliers.value = response.data.data;
      if (response.data.permissions) {
        permissions.value = response.data.permissions;
      }
    }
  } catch (error) {
    if (error.response?.status === 403) {
      toast.error('Access Denied', {
         description: error.response.data.message || 'You lack permissions to view partner suppliers.',
         style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      });
    } else {
      toast.error('Connection error: Unable to fetch suppliers', {
         style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      });
    }
  } finally {
    loading.value = false;
  }
}

// ======================= WIZARD VALIDATION & FORMATTING =======================

const formatPhoneCreate = () => {
  let val = createForm.phone.replace(/\D/g, '')
  if (val.length > 11) val = val.substring(0, 11)
  
  if (val.length > 6) val = val.replace(/(\d{4})(\d{3})(\d+)/, '$1 $2 $3')
  else if (val.length > 4) val = val.replace(/(\d{4})(\d+)/, '$1 $2')
  
  createForm.phone = val
  validateCreateStep(2) // Real-time validation
}

const validateCreateStep = (step) => {
  // Clear errors for the current step
  if (step === 1) delete wizardErrors.companyName;
  if (step === 2) { delete wizardErrors.firstName; delete wizardErrors.lastName; delete wizardErrors.phone; }
  if (step === 3) delete wizardErrors.email;

  let isValid = true;

  if (step === 1) {
     if (!createForm.companyName.trim()) {
        wizardErrors.companyName = 'Company / Supplier Name is required';
        isValid = false;
     }
  } else if (step === 2) {
     if (!createForm.firstName.trim()) { wizardErrors.firstName = 'First Name is required'; isValid = false; }
     if (!createForm.lastName.trim()) { wizardErrors.lastName = 'Last Name is required'; isValid = false; }
     
     if (!createForm.phone.trim()) { 
        wizardErrors.phone = 'Contact Number is required'; 
        isValid = false; 
     } else {
        const num = createForm.phone.replace(/\D/g, '');
        if (num.length < 11 || !num.startsWith('09')) { 
           wizardErrors.phone = 'Invalid PH number format'; 
           isValid = false; 
        }
     }
  } else if (step === 3) {
     if (!createForm.email.trim()) { 
        wizardErrors.email = 'Supplier Email is required'; 
        isValid = false; 
     } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(createForm.email)) { 
        wizardErrors.email = 'Invalid email address format'; 
        isValid = false; 
     }
  }
  
  return isValid;
}

// ======================= WIZARD METHODS =======================
const openCreateWizard = () => {
  createStep.value = 1;
  // Reset Form
  Object.keys(createForm).forEach(key => createForm[key] = '');
  // Clear Errors
  for (const key in wizardErrors) delete wizardErrors[key];
  showCreateWizard.value = true;
}

const nextCreateStep = () => {
  if (validateCreateStep(createStep.value)) {
    createStep.value++;
  } else {
    toast.error('Validation Error', { description: 'Please check the fields in red.', style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }});
  }
}

const confirmCreateSupplier = () => {
  if (validateCreateStep(3)) {
     showCreateConfirmDialog.value = true;
  } else {
     toast.error('Validation Error', { description: 'Please provide a valid email.', style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }});
  }
}

const executeSubmitCreateSupplier = async () => {
  showCreateConfirmDialog.value = false;
  isProcessingCreation.value = true;
  try {
    const payload = {
       company_name: createForm.companyName,
       first_name: createForm.firstName,
       last_name: createForm.lastName,
       phone: createForm.phone.replace(/\D/g, ''), // Send pure digits
       email: createForm.email
    };

    const response = await api.post('/partners/create-supplier', payload);

    if (response.data.success) {
      toast.success(`Supplier Created!`, {
        description: 'Emails dispatched and partnership request generated.',
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      });
      showCreateWizard.value = false;
      await fetchSuppliers(); // Refresh list
    }
  } catch (error) {
     const msg = error.response?.data?.message || 'Failed to create supplier.';
     toast.error(msg, { style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }});
  } finally {
     isProcessingCreation.value = false;
  }
}

// ======================= PARTNERSHIP REQUEST METHODS =======================
const initiatePartnership = (supplier) => {
  selectedSupplier.value = supplier;
  requestMessage.value = '';
  agreedToTerms.value = false; 
  showConfirmDialog.value = true;
}

const confirmPartnership = () => {
  if (!selectedSupplier.value) return;
  if (!agreedToTerms.value) {
     toast.error('Agreement Required', {
        description: 'You must read and agree to the Partnership Terms and Conditions to proceed.',
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
     });
     return;
  }
  showAlertDialog.value = true;
}

const executePartnershipRequest = async () => {
  isProcessing.value = true;
  try {
    const response = await api.post('/partners/request', {
       supplier_id: selectedSupplier.value.id,
       message: requestMessage.value
    });

    if (response.data.success) {
      toast.success(`Request sent to ${selectedSupplier.value.name}!`, {
        description: 'It has been submitted for approval and the agreement has been documented.',
        style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
      });
      
      await fetchSuppliers();
      showAlertDialog.value = false;
      showConfirmDialog.value = false;
      selectedSupplier.value = null;
    }
  } catch (error) {
    if (error.response?.status === 403) {
       toast.error('Action Restricted', {
          description: error.response.data.message || 'You do not have permission to request partnerships.',
          style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
       });
    } else {
       const msg = error.response?.data?.message || 'Failed to send request.';
       toast.error(msg, {
          style: { background: '#0f172a', color: '#ffffff', border: '1px solid #1e293b' }
       });
    }
    showAlertDialog.value = false; 
  } finally {
    isProcessing.value = false;
  }
}

// Computed
const filteredSuppliers = computed(() => {
  if (!searchQuery.value) return suppliers.value;
  
  const query = searchQuery.value.toLowerCase();
  return suppliers.value.filter(supplier => 
    (supplier.name && supplier.name.toLowerCase().includes(query)) || 
    (supplier.category && supplier.category.toLowerCase().includes(query)) ||
    (supplier.location && supplier.location.toLowerCase().includes(query)) ||
    (supplier.email && supplier.email.toLowerCase().includes(query))
  );
})

// Hooks
onMounted(() => {
  fetchSuppliers();
})
</script>

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.3s ease, transform 0.3s ease; }
.fade-enter-from { opacity: 0; transform: translateX(10px); }
.fade-leave-to { opacity: 0; transform: translateX(-10px); }

.custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(15, 23, 42, 0.5); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(51, 65, 85, 1); border-radius: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(71, 85, 105, 1); }
</style>