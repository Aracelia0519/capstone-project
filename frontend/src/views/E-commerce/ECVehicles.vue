<template>
  <div class="p-6 md:p-8 max-w-7xl mx-auto space-y-8 min-h-screen rounded-3xl">
    <!-- Header -->
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

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-24 space-y-4">
      <div class="relative w-16 h-16 flex items-center justify-center">
        <div class="absolute inset-0 border-4 border-indigo-100 rounded-full"></div>
        <Loader2 class="w-10 h-10 animate-spin text-indigo-600 absolute" />
      </div>
      <p class="text-sm font-semibold text-indigo-600 animate-pulse">Loading fleet units...</p>
    </div>

    <!-- Empty State -->
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

    <!-- Fleet Grid -->
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
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">{{ vehicle.make }} - {{ vehicle.model }}</p>
              </div>
            </div>
            <Badge :class="vehicle.status === 'Active' ? 'bg-indigo-50 text-indigo-700 border-indigo-200' : 'bg-amber-50 text-amber-700 border-amber-200'" class="px-3 py-1 rounded-full font-bold shadow-sm">
              {{ vehicle.status }}
            </Badge>
          </div>

          <!-- Capacity Bars -->
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

          <!-- View Documents -->
          <div class="mt-4 flex flex-wrap gap-2">
            <a v-if="vehicle.cr_file_path" :href="getFileUrl(vehicle.cr_file_path)" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
              <FileText class="w-3.5 h-3.5" /> CR
            </a>
            <a v-if="vehicle.or_file_path" :href="getFileUrl(vehicle.or_file_path)" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
              <FileText class="w-3.5 h-3.5" /> OR
            </a>
            <a v-if="vehicle.proof_of_ownership_path" :href="getFileUrl(vehicle.proof_of_ownership_path)" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition-colors">
              <FileText class="w-3.5 h-3.5" /> Ownership
            </a>
            <span v-if="!vehicle.cr_file_path && !vehicle.or_file_path && !vehicle.proof_of_ownership_path" class="text-xs text-slate-400 font-medium italic mt-1">
              No documents uploaded.
            </span>
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

    <!-- Wizard Registration/Editing Modal -->
    <Dialog :open="showAddModal" @update:open="showAddModal = $event">
      <DialogContent class="sm:max-w-[600px] rounded-[2rem] border-slate-100 shadow-2xl p-0 overflow-hidden" @interact-outside="e => e.preventDefault()">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6 text-white">
          <DialogTitle class="text-2xl font-black">
            {{ isEditing ? 'Modify Fleet Unit' : 'Register Fleet Unit' }}
          </DialogTitle>
          <p class="text-indigo-100 text-sm mt-1 opacity-90">Step {{ currentStep }} of {{ totalSteps }}: {{ stepTitles[currentStep - 1] }}</p>
        </div>
        
        <div class="p-6 bg-white min-h-[380px]">
          <!-- Progress Indicator -->
          <div class="flex justify-between items-center mb-8 relative">
            <div class="absolute left-0 top-1/2 -translate-y-1/2 w-full h-1 bg-slate-100 rounded-full z-0"></div>
            <div class="absolute left-0 top-1/2 -translate-y-1/2 h-1 bg-indigo-500 rounded-full z-0 transition-all duration-300" :style="{ width: `${((currentStep - 1) / (totalSteps - 1)) * 100}%` }"></div>
            
            <div v-for="step in totalSteps" :key="step" class="relative z-10 flex flex-col items-center justify-center">
              <div :class="['w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm shadow-sm transition-colors duration-300', 
                  currentStep === step ? 'bg-indigo-600 text-white ring-4 ring-indigo-100' : 
                  (currentStep > step ? 'bg-indigo-500 text-white' : 'bg-white text-slate-400 border border-slate-200')]">
                <Check v-if="currentStep > step" class="w-4 h-4" />
                <span v-else>{{ step }}</span>
              </div>
            </div>
          </div>

          <div v-if="errorMessage" class="mb-6 p-3 bg-red-50 border border-red-100 text-red-600 text-sm rounded-2xl font-semibold flex items-center gap-2 animate-in fade-in slide-in-from-top-2">
            <div class="w-2 h-2 rounded-full bg-red-500 shrink-0"></div>
            {{ errorMessage }}
          </div>
          
          <!-- Step 1: Basic Information -->
          <div v-if="currentStep === 1" class="grid gap-5 animate-in fade-in slide-in-from-right-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="plate" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Plate Number <span class="text-red-500">*</span></Label>
                <Input id="plate" v-model="newVehicle.plate_number" placeholder="e.g. XYZ-9876" :disabled="isEditing" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
              </div>

              <div class="space-y-2">
                <Label for="makeSelection" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Make <span class="text-red-500">*</span></Label>
                <select id="makeSelection" v-model="makeSelection" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none appearance-none bg-slate-50 focus:bg-white transition-colors">
                  <option value="Toyota">Toyota</option>
                  <option value="Honda">Honda</option>
                  <option value="Mitsubishi">Mitsubishi</option>
                  <option value="Nissan">Nissan</option>
                  <option value="Isuzu">Isuzu</option>
                  <option value="Others">Others (Specify)</option>
                </select>
              </div>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2" v-if="makeSelection === 'Others'">
                <Label for="customMake" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Specify Make <span class="text-red-500">*</span></Label>
                <Input id="customMake" v-model="customMake" placeholder="Enter Vehicle Make" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
              </div>
              <div class="space-y-2" :class="makeSelection === 'Others' ? '' : 'col-span-2'">
                <Label for="model" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Model <span class="text-red-500">*</span></Label>
                <Input id="model" v-model="newVehicle.model" placeholder="e.g. Hiace" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
              </div>
            </div>

            <div class="space-y-2">
              <Label for="vin" class="text-xs font-bold text-slate-500 uppercase tracking-wider">VIN (Optional)</Label>
              <Input id="vin" v-model="newVehicle.vin" placeholder="Vehicle Identification No." class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
            </div>
          </div>

          <!-- Step 2: Technical Specs -->
          <div v-if="currentStep === 2" class="grid gap-5 animate-in fade-in slide-in-from-right-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="mv_file_number" class="text-xs font-bold text-slate-500 uppercase tracking-wider">MV File No. <span class="text-red-500">*</span></Label>
                <Input id="mv_file_number" v-model="newVehicle.mv_file_number" maxlength="15" placeholder="15-digit ID Code" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
                <p class="text-[10px] text-slate-400">Must be exactly 15 characters</p>
              </div>
              <div class="space-y-2">
                <Label for="chassis_number" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Chassis No. <span class="text-red-500">*</span></Label>
                <Input id="chassis_number" v-model="newVehicle.chassis_number" maxlength="17" placeholder="17-character code" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
                <p class="text-[10px] text-slate-400">Must be exactly 17 characters</p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-2">
                <Label for="color" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Color <span class="text-red-500">*</span></Label>
                <Input id="color" v-model="newVehicle.color" placeholder="e.g. Alpine White" class="rounded-xl h-11 bg-slate-50 focus:bg-white transition-colors" />
              </div>
              <div class="space-y-2">
                <Label for="fuel_type" class="text-xs font-bold text-slate-500 uppercase tracking-wider">Fuel Type <span class="text-red-500">*</span></Label>
                <select id="fuel_type" v-model="newVehicle.fuel_type" class="w-full h-11 px-3 rounded-xl border border-slate-200 text-sm font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none appearance-none bg-slate-50 focus:bg-white transition-colors">
                  <option value="Gasoline">Gasoline</option>
                  <option value="Diesel">Diesel</option>
                  <option value="Electric">Electric</option>
                  <option value="Hybrid">Hybrid</option>
                </select>
              </div>
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
          </div>

          <!-- Step 3: Capacity -->
          <div v-if="currentStep === 3" class="grid gap-6 animate-in fade-in slide-in-from-right-4">
            <div class="p-6 bg-indigo-50/50 rounded-[2rem] border border-indigo-100/50 flex flex-col gap-6">
              <div class="space-y-2">
                <Label for="maxWeight" class="text-xs font-bold text-slate-600 uppercase tracking-wider">Gross Weight Limit (kg) <span class="text-red-500">*</span></Label>
                <Input id="maxWeight" type="number" v-model="newVehicle.max_weight" placeholder="e.g. 1500" class="rounded-xl h-12 bg-white border-slate-200 shadow-sm text-lg font-bold" />
                <p class="text-xs text-slate-500 mt-1">Total physical carrying limit of the vehicle.</p>
              </div>
              <div class="space-y-2">
                <Label for="paintCapacity" class="text-xs font-bold text-purple-700 uppercase tracking-wider">Paint Capacity (kg) <span class="text-red-500">*</span></Label>
                <Input id="paintCapacity" type="number" v-model="newVehicle.paint_capacity" placeholder="e.g. 1000" class="rounded-xl h-12 bg-white border-slate-200 shadow-sm ring-offset-indigo-50 focus-visible:ring-indigo-500 text-lg font-bold" />
                <p class="text-xs text-slate-500 mt-1">Cannot exceed the Gross Weight Limit.</p>
              </div>
            </div>
          </div>

          <!-- Step 4: Documents -->
          <div v-if="currentStep === 4" class="grid gap-5 animate-in fade-in slide-in-from-right-4">
            <div class="space-y-5 p-1">
              <div class="space-y-2 group">
                <div class="flex justify-between items-center mb-1">
                  <Label class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Certificate of Registration (CR)</Label>
                  <a v-if="isEditing && newVehicle.cr_file_path" :href="getFileUrl(newVehicle.cr_file_path)" target="_blank" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1 transition-colors">
                    <ExternalLink class="w-3 h-3" /> View Current
                  </a>
                </div>
                <Input type="file" accept="image/*,.pdf" @change="e => handleFileChange(e, 'cr_file')" class="cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 group-hover:file:bg-indigo-100 h-auto py-2 px-3 bg-slate-50 border-dashed border-2 transition-colors" />
              </div>
              
              <div class="space-y-2 group">
                <div class="flex justify-between items-center mb-1">
                  <Label class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Official Receipt (OR)</Label>
                  <a v-if="isEditing && newVehicle.or_file_path" :href="getFileUrl(newVehicle.or_file_path)" target="_blank" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1 transition-colors">
                    <ExternalLink class="w-3 h-3" /> View Current
                  </a>
                </div>
                <Input type="file" accept="image/*,.pdf" @change="e => handleFileChange(e, 'or_file')" class="cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 group-hover:file:bg-indigo-100 h-auto py-2 px-3 bg-slate-50 border-dashed border-2 transition-colors" />
              </div>

              <div class="space-y-2 group">
                <div class="flex justify-between items-center mb-1">
                  <Label class="text-xs font-bold text-slate-600 uppercase tracking-wider block">Proof of Ownership</Label>
                  <a v-if="isEditing && newVehicle.proof_of_ownership_path" :href="getFileUrl(newVehicle.proof_of_ownership_path)" target="_blank" class="text-[10px] text-indigo-600 hover:text-indigo-800 font-bold flex items-center gap-1 transition-colors">
                    <ExternalLink class="w-3 h-3" /> View Current
                  </a>
                </div>
                <Input type="file" accept="image/*,.pdf" @change="e => handleFileChange(e, 'proof_of_ownership')" class="cursor-pointer file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 group-hover:file:bg-indigo-100 h-auto py-2 px-3 bg-slate-50 border-dashed border-2 transition-colors" />
              </div>

              <div class="bg-blue-50 text-blue-700 p-4 rounded-xl text-xs font-medium leading-relaxed border border-blue-100 mt-2">
                <Info class="w-4 h-4 inline-block mr-1 mb-0.5" /> Please ensure uploaded documents are clear and legible. Formats accepted: JPG, PNG, PDF (Max 5MB). Existing files will be preserved unless a new one is uploaded.
              </div>
            </div>
          </div>
        </div>

        <div class="p-6 bg-slate-50 border-t border-slate-100 flex gap-3">
          <Button v-if="currentStep > 1" variant="outline" class="flex-1 rounded-xl border-slate-200 h-11 font-bold text-slate-600 shadow-sm" @click="prevStep">
            <ChevronLeft class="w-4 h-4 mr-1" /> Previous
          </Button>
          <Button v-else variant="outline" class="flex-1 rounded-xl border-slate-200 h-11 font-bold text-slate-600 shadow-sm" @click="showAddModal = false">
            Cancel
          </Button>

          <Button v-if="currentStep < totalSteps" class="flex-1 rounded-xl bg-slate-800 hover:bg-slate-900 text-white h-11 shadow-md font-bold" @click="nextStep">
            Next <ChevronRight class="w-4 h-4 ml-1" />
          </Button>
          <Button v-else class="flex-1 rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white h-11 shadow-lg shadow-indigo-500/20 font-bold" @click="promptSave">
            <Save class="w-4 h-4 mr-2" /> {{ isEditing ? 'Review & Update' : 'Review & Register' }}
          </Button>
        </div>
      </DialogContent>
    </Dialog>

    <!-- Save/Update Confirmation Dialog -->
    <AlertDialog :open="showSaveDialog" @update:open="showSaveDialog = $event">
      <AlertDialogContent class="rounded-[2rem] bg-white border border-slate-100 shadow-2xl p-8">
        <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mb-6 mx-auto">
          <Save class="w-8 h-8 text-indigo-500" />
        </div>
        <AlertDialogHeader class="text-center">
          <AlertDialogTitle class="text-2xl font-black text-slate-900">Confirm Action</AlertDialogTitle>
          <AlertDialogDescription class="text-slate-500 text-base mt-2">
            Are you sure you want to {{ isEditing ? 'save changes to this' : 'register this new' }} fleet unit? Please ensure all provided information is accurate.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter class="mt-8 flex gap-3 sm:justify-center">
          <AlertDialogCancel class="flex-1 rounded-xl border-slate-200 h-12 font-bold mt-0" :disabled="submitting" @click="showSaveDialog = false">Review Again</AlertDialogCancel>
          <AlertDialogAction class="flex-1 rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 h-12 shadow-lg shadow-indigo-500/20 font-bold" :disabled="submitting" @click="confirmSave">
            <Loader2 v-if="submitting" class="w-5 h-5 mr-2 animate-spin" /> 
            Yes, Confirm
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Deletion Confirmation Dialog -->
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
import { Plus, Truck, Car, Bike, PackageOpen, Weight, Paintbrush as PaintBucket, Trash2, Edit, Loader2, ChevronRight, ChevronLeft, Check, Save, Info, ExternalLink, FileText } from 'lucide-vue-next'
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

// Modals and Wizard States
const showAddModal = ref(false)
const showDeleteDialog = ref(false)
const showSaveDialog = ref(false)

const currentStep = ref(1)
const totalSteps = 4
const stepTitles = ['Basic Information', 'Technical Specs', 'Capacity Details', 'Registration Documents']

const submitting = ref(false)
const isEditing = ref(false)
const selectedVehicleId = ref(null)
const targetDeleteId = ref(null)
const errorMessage = ref('')

const makeSelection = ref('Toyota')
const customMake = ref('')
const files = ref({ cr_file: null, or_file: null, proof_of_ownership: null })

const newVehicle = ref({ 
  plate_number: '', model: '', make: '', vin: '', 
  mv_file_number: '', chassis_number: '', color: '', fuel_type: 'Gasoline', 
  type: 'Van', status: 'Active', max_weight: null, paint_capacity: null 
})

// Corrected URL function utilizing axios.js baseURL for dynamic mapping
const getFileUrl = (path) => {
  if (!path) return '#'
  if (path.startsWith('http')) return path

  // Use the baseURL configured in your axios.js file
  const baseUrl = api.defaults.baseURL;
  
  // Remove '/api' or '/api/' from the end of the base URL to get the root domain
  const serverUrl = baseUrl.replace(/\/api\/?$/, '')

  // Remove leading 'storage/' if it already exists in the database path to avoid duplication
  const cleanPath = path.startsWith('storage/') ? path.replace('storage/', '') : path

  return `${serverUrl}/storage/${cleanPath}`
}

const fetchVehicles = async () => {
  try {
    loading.value = true
    const response = await api.get('/operation-distributor/vehicles')
    if (response.data.status === 'success') vehicles.value = response.data.data
  } catch (error) {
    console.error(error)
  } finally { loading.value = false }
}

const handleFileChange = (e, field) => {
  if (e.target.files.length > 0) {
    files.value[field] = e.target.files[0]
  } else {
    files.value[field] = null
  }
}

const resetFiles = () => {
  files.value = { cr_file: null, or_file: null, proof_of_ownership: null }
}

const openAddModal = () => {
  isEditing.value = false
  errorMessage.value = ''
  currentStep.value = 1
  makeSelection.value = 'Toyota'
  customMake.value = ''
  resetFiles()
  newVehicle.value = { 
    plate_number: '', model: '', make: '', vin: '', 
    mv_file_number: '', chassis_number: '', color: '', fuel_type: 'Gasoline', 
    type: 'Van', status: 'Active', max_weight: null, paint_capacity: null 
  }
  showAddModal.value = true
}

const openEditModal = (vehicle) => {
  isEditing.value = true
  selectedVehicleId.value = vehicle.id
  errorMessage.value = ''
  currentStep.value = 1
  resetFiles()
  
  const standardMakes = ['Toyota', 'Honda', 'Mitsubishi', 'Nissan', 'Isuzu']
  if (standardMakes.includes(vehicle.make)) {
    makeSelection.value = vehicle.make
    customMake.value = ''
  } else {
    makeSelection.value = 'Others'
    customMake.value = vehicle.make || ''
  }
  
  newVehicle.value = { ...vehicle }
  showAddModal.value = true
}

// Wizard Navigation logic
const nextStep = () => {
  errorMessage.value = ''
  
  // Step validations before proceeding
  if (currentStep.value === 1) {
    if (!newVehicle.value.plate_number) return errorMessage.value = 'Plate Number is required.'
    if (makeSelection.value === 'Others' && !customMake.value) return errorMessage.value = 'Please specify the custom vehicle make.'
    if (!newVehicle.value.model) return errorMessage.value = 'Vehicle Model is required.'
  } 
  else if (currentStep.value === 2) {
    if (!newVehicle.value.mv_file_number || newVehicle.value.mv_file_number.length !== 15) return errorMessage.value = 'MV File Number must be exactly 15 characters long.'
    if (!newVehicle.value.chassis_number || newVehicle.value.chassis_number.length !== 17) return errorMessage.value = 'Chassis Number must be exactly 17 characters long.'
    if (!newVehicle.value.color) return errorMessage.value = 'Vehicle color is required.'
  } 
  else if (currentStep.value === 3) {
    if (!newVehicle.value.max_weight) return errorMessage.value = 'Gross Weight Limit is required.'
    if (!newVehicle.value.paint_capacity) return errorMessage.value = 'Paint Capacity is required.'
    if (Number(newVehicle.value.paint_capacity) > Number(newVehicle.value.max_weight)) return errorMessage.value = 'The max paint load cannot be greater than the total max load.'
  }

  currentStep.value++
}

const prevStep = () => {
  errorMessage.value = ''
  currentStep.value--
}

const promptSave = () => {
  errorMessage.value = ''
  // Final checks before showing confirmation dialog
  if (Number(newVehicle.value.paint_capacity) > Number(newVehicle.value.max_weight)) {
    errorMessage.value = 'The max paint load cannot be greater than the total max load.'
    return
  }
  showSaveDialog.value = true
}

const confirmSave = async () => {
  try {
    submitting.value = true
    const formData = new FormData()
    
    const finalMake = makeSelection.value === 'Others' ? customMake.value : makeSelection.value
    
    formData.append('make', finalMake)
    formData.append('plate_number', newVehicle.value.plate_number)
    if (newVehicle.value.vin) formData.append('vin', newVehicle.value.vin)
    formData.append('mv_file_number', newVehicle.value.mv_file_number)
    formData.append('chassis_number', newVehicle.value.chassis_number)
    formData.append('color', newVehicle.value.color)
    formData.append('fuel_type', newVehicle.value.fuel_type)
    formData.append('model', newVehicle.value.model)
    formData.append('type', newVehicle.value.type)
    formData.append('max_weight', newVehicle.value.max_weight)
    formData.append('paint_capacity', newVehicle.value.paint_capacity)
    formData.append('status', newVehicle.value.status)
    
    if (files.value.cr_file) formData.append('cr_file', files.value.cr_file)
    if (files.value.or_file) formData.append('or_file', files.value.or_file)
    if (files.value.proof_of_ownership) formData.append('proof_of_ownership', files.value.proof_of_ownership)
    
    let response
    const config = { headers: { 'Content-Type': 'multipart/form-data' } }

    if (isEditing.value) {
      response = await api.post(`/operation-distributor/vehicles/update/${selectedVehicleId.value}`, formData, config)
    } else {
      response = await api.post('/operation-distributor/vehicles', formData, config)
    }
    
    if (response.data.status === 'success') {
      showSaveDialog.value = false
      showAddModal.value = false
      fetchVehicles()
    }
  } catch (error) {
    showSaveDialog.value = false // Close the alert to show the error on the modal
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