<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { toast } from 'vue-sonner'
import api from '@/utils/axios' // ADJUST PATH AS NEEDED
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
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
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { ScrollArea } from '@/components/ui/scroll-area'
import { 
  Users, UserCheck, ShieldCheck, Eye, ChevronRight, Check, LayoutDashboard, 
  Handshake, UserPlus, ClipboardList, PackageCheck, ScrollText, Container, 
  Truck, MapPin, FileText, Wallet, Clock, X
} from 'lucide-vue-next'

// --- Interfaces ---
interface Personnel {
  id: number;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  personnel_type: string;
  employment_type: string;
  hire_date: string;
  status: string;
  accessibilities?: { module_name: string, module_path: string }[];
}

interface NavItem {
  name: string;
  path: string;
  icon: any;
  color?: string;
  badge?: string;
  badgeColor?: string;
  requiresVerify?: boolean;
}

interface NavGroup {
  title: string;
  items: NavItem[];
}

// --- State & Computed ---
const personnelList = ref<Personnel[]>([])
const isLoading = ref(true)

const isWizardOpen = ref(false)
const isConfirmDialogOpen = ref(false)
const currentStep = ref(1)
const selectedPersonnel = ref<Personnel | null>(null)
// Stores the specific path strings of selected modules
const selectedAccessPaths = ref<string[]>([])
const isActivating = ref(false)

// Dashboard Stats
const totalPersonnel = computed(() => personnelList.value.length)
const pendingCount = computed(() => personnelList.value.filter(p => p.status === 'pending').length)
const activeCount = computed(() => personnelList.value.filter(p => p.status === 'active').length)

// --- Provided Navigation Modules ---
const navigation: NavGroup[] = [
  {
    title: 'Overview',
    items: [
      { name: 'Dashboard', path: '/supplier/SupplierDashboard', icon: LayoutDashboard, color: 'text-emerald-400', badge: 'Live', requiresVerify: true }
    ]
  },
  {
    title: 'Network',
    items: [
      { name: 'Distributor Partner', path: '/supplier/DistributorPartnerReq', icon: Handshake, color: 'text-indigo-400', badge: 'req', badgeColor: 'bg-amber-500/20 text-amber-300', requiresVerify: true },
      { name: 'Personnel Officer', path: '/supplier/PersonnelOfficer', icon: Users, color: 'text-emerald-400', badgeColor: 'bg-emerald-500/20 text-emerald-300' },
      { name: 'Add Personnel', path: '/supplier/AddPersonnel', icon: UserPlus, color: 'text-cyan-400', badge: 'new', badgeColor: 'bg-cyan-500/20 text-cyan-300' },
      { name: 'Role Activation', path: '/supplier/RoleActivation', icon: ShieldCheck, color: 'text-purple-400', badge: 'pending', badgeColor: 'bg-purple-500/20 text-purple-300' }
    ]
  },
  {
    title: 'Order Management',
    items: [
      { name: 'Order Request', path: '/supplier/SupplierOrderRequest', icon: ClipboardList, color: 'text-emerald-400', badgeColor: 'bg-emerald-500/20 text-emerald-300', requiresVerify: true },
      { name: 'Process Orders', path: '/supplier/SupplierProcessOrders', icon: PackageCheck, color: 'text-amber-400', badge: 'Pending', badgeColor: 'bg-amber-500/20 text-amber-300', requiresVerify: true },
      { name: 'Order History', path: '/supplier/OrderHistory', icon: ScrollText, color: 'text-indigo-400', requiresVerify: true }
    ]
  },
  {
    title: 'Inventory & Materials',
    items: [
      { name: 'Raw Materials', path: '/supplier/SupplierRawMaterials', icon: Container, color: 'text-amber-400', requiresVerify: true },
    ]
  },
  {
    title: 'Logistics',
    items: [
      { name: 'Shipments', path: '/supplier/SupplierShipments', icon: Truck, color: 'text-purple-400', requiresVerify: true },
      { name: 'Delivery', path: '/supplier/Delivery', icon: MapPin, color: 'text-blue-400', requiresVerify: true },
    ]
  },
  {
    title: 'Financials',
    items: [
      { name: 'Invoices', path: '/supplier/Invoices', icon: FileText, color: 'text-cyan-400', requiresVerify: true },
      { name: 'Payments', path: '/supplier/Payments', icon: Wallet, color: 'text-green-400', requiresVerify: true }
    ]
  },
]

// Flatten navigation items to easily find details by path
const allNavItems = computed<NavItem[]>(() => {
  return navigation.flatMap(group => group.items)
})

// --- Methods ---

const fetchPersonnel = async () => {
  isLoading.value = true
  try {
    const response = await api.get('/supplier/personnel-officer/role-activation')
    personnelList.value = response.data.data
  } catch (error: any) {
    toast.error('Failed to load personnel data')
    console.error(error)
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  fetchPersonnel()
})

const openWizard = (personnel: Personnel) => {
  selectedPersonnel.value = personnel
  selectedAccessPaths.value = [] 
  currentStep.value = 1
  isWizardOpen.value = true
}

const closeWizard = () => {
  isWizardOpen.value = false
  setTimeout(() => {
    selectedPersonnel.value = null
    currentStep.value = 1
  }, 300)
}

const nextStep = () => {
  if (currentStep.value < 3) currentStep.value++
}

const prevStep = () => {
  if (currentStep.value > 1) currentStep.value--
}

const handleAccessChange = (modulePath: string, isChecked: boolean | string) => {
  if (isChecked === true) {
    if (!selectedAccessPaths.value.includes(modulePath)) {
      selectedAccessPaths.value.push(modulePath)
    }
  } else {
    const index = selectedAccessPaths.value.indexOf(modulePath)
    if (index > -1) {
      selectedAccessPaths.value.splice(index, 1)
    }
  }
}

const openConfirmDialog = () => {
  isConfirmDialogOpen.value = true
}

const closeConfirmDialog = () => {
  isConfirmDialogOpen.value = false
}

const activatePersonnel = async () => {
  if (!selectedPersonnel.value) return
  
  isActivating.value = true
  
  try {
    // Map paths back to { name, path } structure for the API
    const modulesPayload = selectedAccessPaths.value.map(path => {
      const item = allNavItems.value.find((nav: NavItem) => nav.path === path)
      return {
        name: item?.name || path,
        path: path
      }
    })

    const response = await api.post(`/supplier/personnel-officer/role-activation/${selectedPersonnel.value.id}/activate`, {
      modules: modulesPayload
    })

    // Update local state immediately without full refresh
    const index = personnelList.value.findIndex(p => p.id === selectedPersonnel.value?.id)
    if (index !== -1) {
      personnelList.value[index].status = 'active'
      // Store in the format expected by the local interface
      personnelList.value[index].accessibilities = modulesPayload.map(m => ({
        module_name: m.name,
        module_path: m.path
      }))
    }

    toast.success('Personnel Activated', {
      description: `${selectedPersonnel.value.first_name} has been granted access to ${selectedAccessPaths.value.length} modules.`
    })

    closeConfirmDialog()
    closeWizard()

  } catch (error: any) {
    toast.error('Failed to activate personnel', {
      description: error.response?.data?.message || 'Check your network connection.'
    })
  } finally {
    isActivating.value = false
  }
}

const formatEmploymentType = (type: string) => {
  if(!type) return ''
  return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ')
}
</script>

<template>
  <div class="max-w-8xl mx-auto p-4 md:p-6 space-y-6">
    
    <div class="flex items-center space-x-4 mb-2">
      <div class="p-3 bg-primary/10 rounded-full">
        <UserCheck class="w-6 h-6 text-primary" />
      </div>
      <div>
        <h2 class="text-2xl font-bold tracking-tight">Role Activation Dashboard</h2>
        <p class="text-muted-foreground">Review pending personnel and assign their system access roles.</p>
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 animate-in fade-in slide-in-from-bottom-4 duration-500">
      <Card class="border-muted/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
          <CardTitle class="text-sm font-medium">Total Personnel</CardTitle>
          <Users class="w-4 h-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold">
            <span v-if="isLoading" class="animate-pulse">--</span>
            <span v-else>{{ totalPersonnel }}</span>
          </div>
          <p class="text-xs text-muted-foreground mt-1">Total registered in the system</p>
        </CardContent>
      </Card>

      <Card class="border-muted/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
          <CardTitle class="text-sm font-medium">Pending Activation</CardTitle>
          <Clock class="w-4 h-4 text-amber-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-amber-600 dark:text-amber-500">
            <span v-if="isLoading" class="animate-pulse">--</span>
            <span v-else>{{ pendingCount }}</span>
          </div>
          <p class="text-xs text-muted-foreground mt-1">Awaiting role assignment</p>
        </CardContent>
      </Card>

      <Card class="border-muted/60 shadow-sm">
        <CardHeader class="flex flex-row items-center justify-between pb-2 space-y-0">
          <CardTitle class="text-sm font-medium">Active Personnel</CardTitle>
          <ShieldCheck class="w-4 h-4 text-emerald-500" />
        </CardHeader>
        <CardContent>
          <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-500">
            <span v-if="isLoading" class="animate-pulse">--</span>
            <span v-else>{{ activeCount }}</span>
          </div>
          <p class="text-xs text-muted-foreground mt-1">Currently have system access</p>
        </CardContent>
      </Card>
    </div>

    <Card class="border-muted/60 shadow-sm">
      <CardHeader>
        <CardTitle>Personnel List</CardTitle>
        <CardDescription>Manage your personnel accounts and their permissions.</CardDescription>
      </CardHeader>
      <CardContent>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
          <div class="inline-block min-w-full align-middle">
            <div class="overflow-hidden border rounded-lg sm:border-0 relative">
              
              <div v-if="isLoading" class="absolute inset-0 bg-background/50 z-10 flex items-center justify-center min-h-50">
                <span class="animate-pulse font-medium text-muted-foreground">Loading personnel...</span>
              </div>

              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>Name</TableHead>
                    <TableHead>Role / Type</TableHead>
                    <TableHead>Contact</TableHead>
                    <TableHead>Status</TableHead>
                    <TableHead class="text-right">Action</TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  <TableRow v-for="personnel in personnelList" :key="personnel.id">
                    <TableCell class="font-medium">
                      {{ personnel.first_name }} {{ personnel.last_name }}
                    </TableCell>
                    <TableCell>
                      <div class="flex flex-col">
                        <span>{{ personnel.personnel_type }}</span>
                        <span class="text-xs text-muted-foreground">{{ formatEmploymentType(personnel.employment_type) }}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <div class="flex flex-col text-sm">
                        <span>{{ personnel.email }}</span>
                        <span class="text-muted-foreground">{{ personnel.phone || 'N/A' }}</span>
                      </div>
                    </TableCell>
                    <TableCell>
                      <Badge :variant="personnel.status === 'active' ? 'default' : 'secondary'">
                        {{ personnel.status.toUpperCase() }}
                      </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                      <Button 
                        v-if="personnel.status === 'pending'"
                        variant="outline" 
                        size="sm" 
                        @click="openWizard(personnel)"
                        class="whitespace-nowrap"
                      >
                        <ShieldCheck class="w-4 h-4 mr-2" />
                        <span class="hidden sm:inline">Review & Activate</span>
                        <span class="sm:hidden">Activate</span>
                      </Button>
                      <Button 
                        v-else
                        variant="ghost" 
                        size="sm" 
                        disabled
                        class="whitespace-nowrap"
                      >
                        <Check class="w-4 h-4 mr-2 text-emerald-500" />
                        <span class="hidden sm:inline">Activated</span>
                        <span class="sm:hidden">Active</span>
                      </Button>
                    </TableCell>
                  </TableRow>
                  <TableRow v-if="!isLoading && personnelList.length === 0">
                    <TableCell colspan="5" class="h-24 text-center text-muted-foreground">
                      No personnel records found.
                    </TableCell>
                  </TableRow>
                </TableBody>
              </Table>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <Dialog :open="isWizardOpen" @update:open="closeWizard">
      <DialogContent class="sm:max-w-175 p-0 overflow-hidden h-[90vh] sm:h-[85vh] flex flex-col">
        
        <div class="bg-muted/30 px-4 sm:px-6 py-3 sm:py-4 border-b flex items-center justify-between">
          <DialogTitle class="text-base sm:text-xl">Activate Personnel Account</DialogTitle>
          <Button variant="ghost" size="icon" class="sm:hidden -mr-2" @click="closeWizard">
            <X class="h-5 w-5" />
          </Button>
        </div>
        
        <div class="bg-muted/30 px-4 sm:px-6 pb-4 border-b">
          <DialogDescription class="sr-only">Wizard to activate personnel account and assign roles.</DialogDescription>
          
          <div class="flex items-center justify-between relative">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-muted rounded-full overflow-hidden z-0">
              <div 
                class="h-full bg-primary transition-all duration-500 ease-in-out"
                :style="{ width: `${((currentStep - 1) / 2) * 100}%` }"
              ></div>
            </div>
            
            <div v-for="step in 3" :key="step" class="relative z-10 flex flex-col items-center gap-1 bg-background p-1 rounded-full">
              <div 
                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center border-2 transition-colors duration-300 shadow-sm text-xs sm:text-sm font-medium"
                :class="[
                  step < currentStep ? 'bg-primary border-primary text-primary-foreground' : 
                  step === currentStep ? 'bg-background border-primary text-primary' : 
                  'bg-background border-muted text-muted-foreground'
                ]"
              >
                <Check v-if="step < currentStep" class="w-3 h-3 sm:w-4 sm:h-4" />
                <span v-else>{{ step }}</span>
              </div>
            </div>
          </div>
          <div class="flex justify-between mt-2 text-[10px] sm:text-xs font-medium text-muted-foreground px-0.5 sm:px-1">
            <span :class="{'text-primary': currentStep >= 1}">Review</span>
            <span :class="{'text-primary': currentStep >= 2}">Access</span>
            <span :class="{'text-primary': currentStep === 3}">Confirm</span>
          </div>
        </div>

        <div class="flex-1 overflow-hidden relative">
          <ScrollArea class="h-full">
            <div class="p-4 sm:p-6 pb-8">
              
              <div v-if="currentStep === 1" class="space-y-4 sm:space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                <div>
                  <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                    <Eye class="w-4 h-4 sm:w-5 sm:h-5 text-primary"/> 
                    Review Information
                  </h3>
                  <p class="text-xs sm:text-sm text-muted-foreground">Verify the details before assigning system access.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-y-4 sm:gap-x-6 bg-muted/20 p-4 sm:p-5 rounded-lg border">
                  <div class="space-y-0.5 sm:space-y-1">
                    <span class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">Full Name</span>
                    <p class="text-sm sm:font-medium wrap-break-word">{{ selectedPersonnel?.first_name }} {{ selectedPersonnel?.last_name }}</p>
                  </div>
                  <div class="space-y-0.5 sm:space-y-1">
                    <span class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">Email Address</span>
                    <p class="text-sm sm:font-medium wrap-break-word">{{ selectedPersonnel?.email }}</p>
                  </div>
                  <div class="space-y-0.5 sm:space-y-1">
                    <span class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">Phone Number</span>
                    <p class="text-sm sm:font-medium">{{ selectedPersonnel?.phone || 'N/A' }}</p>
                  </div>
                  <div class="space-y-0.5 sm:space-y-1">
                    <span class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">Hire Date</span>
                    <p class="text-sm sm:font-medium">{{ selectedPersonnel?.hire_date || 'N/A' }}</p>
                  </div>
                  <div class="col-span-1 sm:col-span-2 pt-2 sm:pt-2 border-t mt-1 sm:mt-2 flex flex-col sm:flex-row justify-between gap-2 sm:gap-0">
                    <div class="space-y-0.5 sm:space-y-1">
                      <span class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">Requested Role</span>
                      <Badge variant="outline" class="font-medium bg-background text-xs sm:text-sm">{{ selectedPersonnel?.personnel_type }}</Badge>
                    </div>
                    <div class="space-y-0.5 sm:space-y-1 text-left sm:text-right">
                      <span class="text-[10px] sm:text-xs text-muted-foreground uppercase tracking-wider">Employment</span>
                      <p class="text-sm sm:font-medium">{{ formatEmploymentType(selectedPersonnel?.employment_type || '') }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="currentStep === 2" class="space-y-4 sm:space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                <div>
                  <h3 class="text-base sm:text-lg font-semibold flex items-center gap-2">
                    <ShieldCheck class="w-4 h-4 sm:w-5 sm:h-5 text-primary"/> 
                    Module Accessibility
                  </h3>
                  <p class="text-xs sm:text-sm text-muted-foreground">Select the navigation modules this personnel can access.</p>
                </div>

                <div class="space-y-6">
                  <div v-for="group in navigation" :key="group.title" class="space-y-3">
                    <h4 class="text-sm font-semibold border-b pb-1">{{ group.title }}</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 sm:gap-3">
                      <label 
                        v-for="module in group.items" 
                        :key="module.path"
                        class="flex items-center space-x-3 border rounded-lg p-3 cursor-pointer transition-colors"
                        :class="selectedAccessPaths.includes(module.path) ? 'border-primary bg-primary/5' : 'hover:bg-muted/50'"
                      >
                        <Checkbox 
                          :id="module.path" 
                          :model-value="selectedAccessPaths.includes(module.path)"
                          @update:model-value="handleAccessChange(module.path, $event)"
                        />
                        <div class="flex items-center gap-2 flex-1">
                          <div class="p-1.5 rounded-md bg-muted/50">
                            <component :is="module.icon" class="w-4 h-4" :class="module.color" />
                          </div>
                          <span class="text-sm font-medium">{{ module.name }}</span>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>

              <div v-if="currentStep === 3" class="space-y-4 sm:space-y-6 animate-in fade-in slide-in-from-right-4 duration-500">
                <div class="text-center py-2 sm:py-4">
                  <div class="w-12 h-12 sm:w-16 sm:h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-2 sm:mb-4">
                    <ShieldCheck class="w-6 h-6 sm:w-8 sm:h-8 text-primary" />
                  </div>
                  <h3 class="text-base sm:text-xl font-semibold">Ready to Activate</h3>
                  <p class="text-xs sm:text-sm text-muted-foreground mt-1 max-w-md mx-auto px-2">
                    You are about to activate <strong class="wrap-break-word">{{ selectedPersonnel?.first_name }} {{ selectedPersonnel?.last_name }}</strong> as an active personnel.
                  </p>
                </div>

                <div class="bg-muted/20 border rounded-lg p-3 sm:p-5">
                  <h4 class="font-medium mb-2 sm:mb-3 text-xs sm:text-sm uppercase tracking-wider text-muted-foreground">Access Summary</h4>
                  <div v-if="selectedAccessPaths.length > 0" class="flex flex-wrap gap-1.5 sm:gap-2">
                    <Badge 
                      v-for="path in selectedAccessPaths" 
                      :key="path" 
                      variant="secondary" 
                      class="bg-primary/10 text-primary hover:bg-primary/20 border-0 text-[10px] sm:text-xs"
                    >
                      {{ allNavItems.find((m: NavItem) => m.path === path)?.name || path }}
                    </Badge>
                  </div>
                  <div v-else class="text-xs sm:text-sm text-destructive font-medium p-2 sm:p-2 bg-destructive/10 rounded-md border border-destructive/20">
                    Warning: No modules selected. This user will not be able to navigate the system.
                  </div>
                </div>
              </div>

            </div>
          </ScrollArea>
        </div>

        <DialogFooter class="p-3 sm:p-6 bg-muted/10 border-t flex flex-row items-center justify-between gap-2 sm:gap-0 mt-auto">
          <Button variant="outline" size="sm" @click="currentStep === 1 ? closeWizard() : prevStep()" :disabled="isActivating" class="text-xs sm:text-sm">
            {{ currentStep === 1 ? 'Cancel' : 'Back' }}
          </Button>
          
          <Button v-if="currentStep < 3" size="sm" @click="nextStep" class="text-xs sm:text-sm">
            Next <ChevronRight class="w-3 h-3 sm:w-4 sm:h-4 ml-1" />
          </Button>

          <Button v-else size="sm" @click="openConfirmDialog" :disabled="isActivating" class="text-xs sm:text-sm">
            <template v-if="isActivating">
              <span class="animate-pulse">Activating...</span>
            </template>
            <template v-else>
              <span class="hidden sm:inline">Confirm & Activate</span>
              <span class="sm:hidden">Activate</span>
            </template>
          </Button>
        </DialogFooter>

      </DialogContent>
    </Dialog>

    <AlertDialog :open="isConfirmDialogOpen" @update:open="closeConfirmDialog">
      <AlertDialogContent class="w-[95vw] max-w-md sm:max-w-lg p-4 sm:p-6">
        <AlertDialogHeader>
          <AlertDialogTitle class="flex items-center gap-2 text-base sm:text-lg">
            <ShieldCheck class="w-4 h-4 sm:w-5 sm:h-5 text-primary" />
            Confirm Activation
          </AlertDialogTitle>
          <AlertDialogDescription class="space-y-2 pt-2 sm:pt-3 text-sm sm:text-base">
            <p>
              You are about to activate <strong class="wrap-break-word">{{ selectedPersonnel?.first_name }} {{ selectedPersonnel?.last_name }}</strong> with access to the following modules:
            </p>
            <div v-if="selectedAccessPaths.length > 0" class="flex flex-wrap gap-1.5 pt-2">
              <Badge 
                v-for="path in selectedAccessPaths" 
                :key="path" 
                variant="secondary" 
                class="bg-primary/10 text-primary border-primary/20 text-[10px] sm:text-xs"
              >
                {{ allNavItems.find((m: NavItem) => m.path === path)?.name }}
              </Badge>
            </div>
            <div v-else class="text-xs sm:text-sm text-destructive font-medium p-2 bg-destructive/10 rounded-md border border-destructive/20 mt-2">
              Warning: No modules selected. This user will not have access to any system features.
            </div>
            <p class="pt-2 sm:pt-3 text-foreground/80 text-sm sm:text-base">
              This action cannot be undone. Are you sure you want to proceed?
            </p>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="flex-col sm:flex-row gap-2 sm:gap-0">
          <AlertDialogCancel @click="closeConfirmDialog" :disabled="isActivating" class="w-full sm:w-auto text-sm">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction @click="activatePersonnel" :disabled="isActivating" class="w-full sm:w-auto text-sm bg-primary text-primary-foreground">
            <template v-if="isActivating">
              <span class="animate-pulse">Activating...</span>
            </template>
            <template v-else>
              Yes, Activate Account
            </template>
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

  </div>
</template>