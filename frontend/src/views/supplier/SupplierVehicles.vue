<template>
  <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-8  min-h-screen rounded-3xl">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-6 rounded-3xl shadow-sm border border-slate-100">
      <div>
        <h1 class="text-3xl font-extrabold bg-gradient-to-r from-emerald-600 to-teal-500 bg-clip-text text-transparent tracking-tight">
          Vehicle Management
        </h1>
        <p class="text-sm text-slate-500 mt-1 font-medium">Monitor and manage your logistics fleet capacity.</p>
      </div>
      <Button 
        class="bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white rounded-2xl shadow-lg shadow-emerald-500/20 px-6 py-5 transition-all hover:scale-105" 
        @click="openAddModal"
      >
        <Plus class="w-5 h-5 mr-2" /> 
        <span class="font-bold tracking-wide">Register Vehicle</span>
      </Button>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="relative w-16 h-16 flex items-center justify-center">
        <div class="absolute inset-0 border-4 border-emerald-100 rounded-full"></div>
        <Loader2 class="w-10 h-10 animate-spin text-emerald-500 absolute" />
      </div>
      <p class="text-sm font-semibold text-emerald-600 animate-pulse">Syncing fleet data...</p>
    </div>

    <div v-else-if="vehicles.length === 0" class="flex flex-col items-center justify-center py-20 px-4 text-center bg-white rounded-3xl border border-dashed border-slate-200">
      <div class="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-6">
        <Truck class="w-12 h-12 text-emerald-400 opacity-50" />
      </div>
      <h3 class="text-xl font-bold text-slate-800 mb-2">No Vehicles Registered</h3>
      <p class="text-slate-500 max-w-sm mb-6">Your logistics fleet is currently empty. Add your first vehicle to start tracking capacities.</p>
      <Button variant="outline" class="border-emerald-200 text-emerald-600 hover:bg-emerald-50 rounded-xl" @click="openAddModal">
        Add Your First Vehicle
      </Button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
      <Card 
        v-for="vehicle in vehicles" 
        :key="vehicle.id" 
        class="group relative bg-white border border-slate-100 rounded-[2rem] shadow-sm hover:shadow-xl hover:shadow-emerald-500/10 transition-all duration-500 hover:-translate-y-1 overflow-hidden"
      >
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-emerald-400 to-teal-400 opacity-50 group-hover:opacity-100 transition-opacity"></div>

        <div class="p-6">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center shadow-inner shadow-white/20">
                <Truck v-if="vehicle.type === 'Truck'" class="w-7 h-7 text-white drop-shadow-md" />
                <Car v-else-if="vehicle.type === 'Van'" class="w-7 h-7 text-white drop-shadow-md" />
                <Bike v-else-if="vehicle.type === 'Motorcycle'" class="w-7 h-7 text-white drop-shadow-md" />
              </div>
              <div>
                <h3 class="text-lg font-black text-slate-800 tracking-tight">{{ vehicle.plate_number }}</h3>
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ vehicle.model }}</p>
              </div>
            </div>
            <Badge :class="vehicle.status === 'Active' ? 'bg-emerald-50 text-emerald-600 border-emerald-200' : 'bg-amber-50 text-amber-600 border-amber-200'" class="px-3 py-1 rounded-full font-bold shadow-sm">
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
                <span class="text-[11px] font-bold text-emerald-600 uppercase tracking-widest flex items-center gap-1">
                  <PaintBucket class="w-3.5 h-3.5" /> Paint Load
                </span>
                <span class="font-black text-emerald-600">{{ vehicle.paint_capacity }} <span class="text-xs font-semibold text-emerald-400/70">kg</span></span>
              </div>
              <div class="w-full bg-emerald-100 rounded-full h-2 overflow-hidden">
                <div class="bg-gradient-to-r from-emerald-400 to-teal-400 h-full rounded-full relative" :style="`width: ${(vehicle.paint_capacity / vehicle.max_weight) * 100}%`">
                  <div class="absolute inset-0 bg-white/20 w-full animate-[shimmer_2s_infinite]"></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex justify-end gap-3 bg-white">
          <Button variant="outline" size="sm" class="rounded-xl border-slate-200 text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200 transition-colors" @click="openEditModal(vehicle)">
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
        <div class="bg-gradient-to-r from-emerald-500 to-teal-500 p-6 text-white">
          <DialogTitle class="text-2xl font-black">
            {{ isEditing ? 'Edit Vehicle Asset' : 'Register Vehicle' }}
          </DialogTitle>
          <p class="text-emerald-50 text-sm mt-1 opacity-90">Enter the logistics configuration below.</p>
        </div>
        
        <div class="grid gap-5 p-6 bg-white">
          <div v-if="errorMessage" class="p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-2xl font-semibold flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-red-500 shrink-0"></div>
            {{ errorMessage }}
          </div>
          
          <div class="space-y-2">
            <Label for="plate" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Plate Number</Label>
            <Input id="plate" v-model="newVehicle.plate_number" placeholder="e.g. ABC-1234" :disabled="isEditing" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
          </div>
          
          <div class="space-y-2">
            <Label for="model" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Vehicle Model</Label>
            <Input id="model" v-model="newVehicle.model" placeholder="e.g. Isuzu Elf" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div class="space-y-2">
              <Label for="type" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Type</Label>
              <select id="type" v-model="newVehicle.type" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none appearance-none bg-slate-50 focus:bg-white transition-colors">
                <option value="Truck">Cargo Truck</option>
                <option value="Van">Delivery Van</option>
                <option value="Motorcycle">Motorcycle</option>
              </select>
            </div>
            <div class="space-y-2">
              <Label for="status" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Status</Label>
              <select id="status" v-model="newVehicle.status" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-emerald-500 focus:outline-none appearance-none bg-slate-50 focus:bg-white transition-colors">
                <option value="Active">Active</option>
                <option value="Under Maintenance">Under Maintenance</option>
              </select>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 p-4 bg-emerald-50/50 rounded-2xl border border-emerald-100/50">
            <div class="space-y-2">
              <Label for="maxWeight" class="text-xs font-bold text-slate-600 uppercase tracking-wider">Gross (kg)</Label>
              <Input id="maxWeight" type="number" v-model="newVehicle.max_weight" placeholder="3000" class="rounded-xl h-11 border-white shadow-sm" />
            </div>
            <div class="space-y-2">
              <Label for="paintCapacity" class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Paint (kg)</Label>
              <Input id="paintCapacity" type="number" v-model="newVehicle.paint_capacity" placeholder="2500" class="rounded-xl h-11 border-white shadow-sm ring-offset-emerald-50 focus-visible:ring-emerald-500" />
            </div>
          </div>
        </div>

        <div class="p-6 pt-0 bg-white flex gap-3">
          <Button variant="outline" class="flex-1 rounded-xl border-slate-200 h-11 font-bold text-slate-600" @click="showAddModal = false">Cancel</Button>
          <Button class="flex-1 rounded-xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-600 hover:to-teal-600 text-white h-11 shadow-lg shadow-emerald-500/20 font-bold" :disabled="submitting" @click="saveVehicle">
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
          <AlertDialogTitle class="text-2xl font-black text-slate-900">Confirm Deletion</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500 text-base mt-2">
            This action cannot be undone. This will permanently delete the vehicle data from our logistics records.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-8 flex gap-3 sm:justify-center">
          <AlertDialogCancel class="flex-1 rounded-xl border-slate-200 h-12 font-bold mt-0" @click="showDeleteDialog = false">Keep Vehicle</AlertDialogCancel>
          <AlertDialogAction class="flex-1 rounded-xl bg-red-600 text-white hover:bg-red-700 h-12 shadow-lg shadow-red-500/20 font-bold" @click="confirmDeleteVehicle">
            Yes, Delete it
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { Plus, Truck, Car, Bike, Weight, Paintbrush as PaintBucket, Trash2, Edit, Loader2 } from 'lucide-vue-next'
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

const newVehicle = ref({ plate_number: '', model: '', type: 'Truck', status: 'Active', max_weight: null, paint_capacity: null })

const fetchVehicles = async () => {
  try {
    loading.value = true
    const response = await api.get('/supplier/vehicles')
    if (response.data.status === 'success') vehicles.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally { loading.value = false }
}

const openAddModal = () => {
  isEditing.value = false
  errorMessage.value = ''
  newVehicle.value = { plate_number: '', model: '', type: 'Truck', status: 'Active', max_weight: null, paint_capacity: null }
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
      response = await api.post(`/supplier/vehicles/update/${selectedVehicleId.value}`, newVehicle.value)
    } else {
      response = await api.post('/supplier/vehicles', newVehicle.value)
    }
    if (response.data.status === 'success') {
      showAddModal.value = false
      fetchVehicles()
    }
  } catch (error) {
    errorMessage.value = error.response?.data?.message || 'Failed saving vehicle data.'
  } finally { submitting.value = false }
}

const triggerDeleteDialog = (id) => {
  targetDeleteId.value = id
  showDeleteDialog.value = true
}

const confirmDeleteVehicle = async () => {
  try {
    await api.delete(`/supplier/vehicles/${targetDeleteId.value}`)
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