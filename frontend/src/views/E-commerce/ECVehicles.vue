<template>
  <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-8  min-h-screen rounded-3xl">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
      <div>
        <h1 class="text-3xl font-black bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-500 bg-clip-text text-transparent tracking-tight">
          Delivery Fleet
        </h1>
        <p class="text-sm text-slate-500 mt-1 font-medium">Manage e-commerce distribution vehicles and capacity.</p>
      </div>
      <Button 
        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-2xl shadow-lg shadow-indigo-500/20 px-6 py-5 transition-all hover:scale-105" 
        @click="openAddModal"
      >
        <Plus class="w-5 h-5 mr-2" /> 
        <span class="font-bold tracking-wide">Register Unit</span>
      </Button>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="relative w-16 h-16 flex items-center justify-center">
        <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
        <Loader2 class="w-10 h-10 animate-spin text-indigo-600 absolute" />
      </div>
      <p class="text-sm font-semibold text-indigo-600 animate-pulse">Loading fleet units...</p>
    </div>

    <div v-else-if="vehicles.length === 0" class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white rounded-3xl border border-dashed border-slate-200">
      <div class="w-24 h-24 bg-indigo-50 rounded-full flex items-center justify-center mb-6">
        <PackageOpen class="w-12 h-12 text-indigo-400 opacity-50" />
      </div>
      <h3 class="text-xl font-bold text-slate-800 mb-2">Fleet is Empty</h3>
      <p class="text-slate-500 max-w-sm mb-6">No distribution vehicles have been registered yet. Add a unit to manage deliveries.</p>
      <Button variant="outline" class="border-indigo-200 text-indigo-600 hover:bg-indigo-50 rounded-xl font-bold" @click="openAddModal">
        Add First Fleet Unit
      </Button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="vehicle in vehicles" 
        :key="vehicle.id" 
        class="group relative bg-white border border-slate-100 rounded-[2rem] shadow-sm hover:shadow-2xl hover:shadow-indigo-500/10 transition-all duration-500 hover:-translate-y-1 overflow-hidden"
      >
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 opacity-50 group-hover:opacity-100 transition-opacity"></div>

        <div class="p-6">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-inner shadow-white/20">
                <Truck v-if="vehicle.type === 'Truck'" class="w-7 h-7 text-white drop-shadow-md" />
                <Car v-else-if="vehicle.type === 'Van'" class="w-7 h-7 text-white drop-shadow-md" />
                <Bike v-else-if="vehicle.type === 'Motorcycle'" class="w-7 h-7 text-white drop-shadow-md" />
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ vehicle.plate_number }}</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ vehicle.model }}</p>
              </div>
            </div>
            <Badge :class="vehicle.status === 'Active' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-amber-50 text-amber-700 border-amber-200'" class="px-3 py-1 rounded-full font-bold shadow-sm">
              {{ vehicle.status }}
            </Badge>
          </div>

          <div class="space-y-5 bg-slate-50/50 p-4 rounded-2xl border border-slate-100">
            <div>
              <div class="flex justify-between items-end mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1">
                  <Weight class="w-3.5 h-3.5" /> Gross Wt.
                </span>
                <span class="font-black text-slate-700">{{ vehicle.max_weight }} <span class="text-xs font-semibold text-slate-400">kg</span></span>
              </div>
              <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                <div class="bg-slate-300 h-full w-full"></div>
              </div>
            </div>

            <div>
              <div class="flex justify-between items-end mb-2">
                <span class="text-[11px] font-bold text-purple-600 uppercase tracking-widest flex items-center gap-1">
                  <PaintBucket class="w-3.5 h-3.5" /> Paint Load
                </span>
                <span class="font-black text-purple-700">{{ vehicle.paint_capacity }} <span class="text-xs font-semibold text-purple-400/70">kg</span></span>
              </div>
              <div class="w-full bg-indigo-100 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-500 to-purple-500 h-full rounded-full relative" :style="`width: ${(vehicle.paint_capacity / vehicle.max_weight) * 100}%`">
                  <div class="absolute inset-0 bg-white/20 w-full animate-[shimmer_2s_infinite]"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
          <Button variant="outline" size="sm" class="rounded-xl border-slate-200 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 hover:border-indigo-200 transition-colors" @click="openEditModal(vehicle)">
            <Edit class="w-4 h-4 mr-1.5" /> Edit
          </Button>
          <Button variant="outline" size="sm" class="rounded-xl border-slate-200 text-slate-500 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors" @click="triggerDeleteDialog(vehicle.id)">
            <Trash2 class="w-4 h-4 mr-1.5" /> Remove
          </Button>
        </div>
      </Card>
    </div>

    <Dialog :open="showAddModal" @update:open="showAddModal = $event">
      <DialogContent class="sm:max-w-[425px] rounded-[2rem] border-slate-100 shadow-2xl p-0 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
          <DialogTitle class="text-2xl font-black">
            {{ isEditing ? 'Modify Fleet Unit' : 'Add Fleet Unit' }}
          </DialogTitle>
          <p class="text-indigo-100 text-sm mt-1 opacity-90">Manage details for your distribution vehicle.</p>
        </div>
        
        <div class="grid gap-5 p-6 bg-white">
          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-2xl font-semibold flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-red-500 shrink-0"></div>
            {{ errorMessage }}
          </div>
          
          <div class="space-y-2">
            <Label for="plate" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Plate Number</Label>
            <Input id="plate" v-model="newVehicle.plate_number" placeholder="e.g. XYZ-9876" :disabled="isEditing" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
          </div>
          
          <div class="space-y-2">
            <Label for="model" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Make & Model</Label>
            <Input id="model" v-model="newVehicle.model" placeholder="e.g. Ford Transit Custom" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="type" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Classification</Label>
              <select id="type" v-model="newVehicle.type" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none appearance-none bg-slate-50 focus:bg-white transition-colors">
                <option value="Van">Cargo Van</option>
                <option value="Truck">Box Truck</option>
                <option value="Motorcycle">Utility Motorcycle</option>
              </select>
            </div>
            <div class="space-y-2">
              <Label for="status" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</Label>
              <select id="status" v-model="newVehicle.status" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none appearance-none bg-slate-50 focus:bg-white transition-colors">
                <option value="Active">Active</option>
                <option value="Under Maintenance">Under Maintenance</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
            <div class="space-y-2">
              <Label for="maxWeight" class="text-xs font-bold text-slate-600 uppercase tracking-wider">Gross (kg)</Label>
              <Input id="maxWeight" type="number" v-model="newVehicle.max_weight" placeholder="1500" class="rounded-xl h-11 border-white shadow-sm" />
            </div>
            <div class="space-y-2">
              <Label for="paintCapacity" class="text-xs font-bold text-purple-700 uppercase tracking-wider">Paint (kg)</Label>
              <Input id="paintCapacity" type="number" v-model="newVehicle.paint_capacity" placeholder="1000" class="rounded-xl h-11 border-white shadow-sm ring-offset-indigo-50 focus-visible:ring-indigo-500" />
            </div>
          </div>
        </div>

        <div class="p-6 pt-0 bg-white flex gap-3">
          <Button variant="outline" class="flex-1 rounded-xl border-slate-200 h-11 font-bold text-slate-600" @click="showAddModal = false">Cancel</Button>
          <Button class="flex-1 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white h-11 shadow-lg shadow-indigo-500/20 font-bold" :disabled="submitting" @click="saveVehicle">
            <Loader2 v-if="submitting" class="w-5 h-5 mr-2 animate-spin" /> 
            {{ isEditing ? 'Save Changes' : 'Confirm Setup' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <AlertDialog :open="showDeleteDialog" @update:open="showDeleteDialog = $event">
      <AlertDialogContent class="rounded-[2rem] bg-white border border-slate-100 shadow-2xl p-8">
        <div class="w-16 h-16 rounded-full bg-red-50 flex items-center justify-center mb-6 mx-auto">
          <Trash2 class="w-8 h-8 text-red-500" />
        </div>
        <AlertDialogHeader class="text-center">
          <AlertDialogTitle class="text-2xl font-black text-slate-900">Confirm Removal</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500 text-base mt-2">
            Are you sure you want to completely clear this unit from the fleet registry? This step cannot be reversed.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-8 flex gap-3 sm:justify-center">
          <AlertDialogCancel class="flex-1 rounded-xl border-slate-200 h-12 font-bold mt-0" @click="showDeleteDialog = false">Cancel</AlertDialogCancel>
          <AlertDialogAction class="flex-1 rounded-xl bg-red-600 text-white hover:bg-red-700 h-12 shadow-lg shadow-red-500/20 font-bold" @click="confirmDeleteVehicle">
            Confirm Removal
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, Truck, Car, Bike, PackageOpen, Weight, Paintbrush as PaintBucket, Trash2, Edit, Loader2 } from 'lucide-vue-next'
import { Card } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Dialog, DialogContent, DialogTitle } from '@/components/ui/dialog'
import { AlertDialog, AlertDialogContent, AlertDialogHeader, AlertDialogTitle, AlertDialogDescription, AlertDialogFooter, AlertDialogCancel, AlertDialogAction } from '@/components/ui/alert-dialog'
import api from '@/utils/axios'

const vehicles = ref([])
const loading = ref(true)
const showAddModal = ref(false)
const showDeleteDialog = ref(false)
const submitting = ref(false)
const isEditing = ref(false)
const selectedVehicleId = ref(null)
const targetDeleteId = ref(null)
const errorMessage = ref('')

const newVehicle = ref({ plate_number: '', model: '', type: 'Van', status: 'Active', max_weight: null, paint_capacity: null })

const fetchVehicles = async () => {
  try {
    loading.value = true
    const response = await api.get('/operation-distributor/vehicles')
    if (response.data.status === 'success') vehicles.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally { loading.value = false }
}

const openAddModal = () => {
  isEditing.value = false
  errorMessage.value = ''
  newVehicle.value = { plate_number: '', model: '', type: 'Van', status: 'Active', max_weight: null, paint_capacity: null }
  showAddModal.value = true
}

const openEditModal = (vehicle) => {
  isEditing.value = true
  selectedVehicleId.value = vehicle.id
  errorMessage.value = ''
  newVehicle.value = { ...vehicle }
  showAddModal.value = true
}

const saveVehicle = async () => {
  errorMessage.value = ''
  if (Number(newVehicle.value.paint_capacity) > Number(newVehicle.value.max_weight)) {
    errorMessage.value = 'The max paint load cannot be greater than the total max load.'
    return
  }
  try {
    submitting.value = true
    let response
    if (isEditing.value) {
      response = await api.post(`/operation-distributor/vehicles/update/${selectedVehicleId.value}`, newVehicle.value)
    } else {
      response = await api.post('/operation-distributor/vehicles', newVehicle.value)
    }
    if (response.data.status === 'success') {
      showAddModal.value = false
      fetchVehicles()
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Access Denied or Database Error.'
  } finally { submitting.value = false }
}

const triggerDeleteDialog = (id) => {
  targetDeleteId.value = id
  showDeleteDialog.value = true
}

const confirmDeleteVehicle = async () => {
  try {
    await api.delete(`/operation-distributor/vehicles/${targetDeleteId.value}`)
    showDeleteDialog.value = false
    fetchVehicles()
  } catch (error) { console.error(error) }
}

onMounted(fetchVehicles)
</script>

<style scoped>
@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
</style>