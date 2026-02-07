<template>
  <div class="min-h-screen p-4 md:p-6 text-slate-200">
    <div class="mb-8">
      <div class="flex flex-col md:flex-row md:items-center justify-between">
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-white mb-2">Virtual Paint Color Mixer</h1>
          <p class="text-slate-400 text-sm md:text-base">Real-time 3D visualization using Unity WebGL</p>
        </div>
        <div class="mt-4 md:mt-0 flex items-center space-x-4">
          <div class="inline-flex items-center px-4 py-2 bg-emerald-500/10 border border-emerald-500/30 rounded-full backdrop-blur-md">
            <div class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></div>
            <span class="text-emerald-400 text-sm font-medium">Unity Connected</span>
          </div>
          
          <div class="flex items-center gap-2">
            <Button size="icon" variant="outline" class="bg-slate-800 border-slate-700 text-slate-300 hover:text-white" @click="toggleFullscreen">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" /></svg>
            </Button>
            <Button size="icon" variant="outline" class="bg-slate-800 border-slate-700 text-slate-300 hover:text-white" @click="resetScene">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" /></svg>
            </Button>
          </div>
        </div>
      </div>
      
      <div class="mt-6 grid grid-cols-1 md:grid-cols-3 gap-4">
        <Card class="bg-gradient-to-br from-slate-900 to-slate-800 border-slate-700 hover:border-blue-500/50 transition-colors">
          <CardContent class="p-5 flex items-center">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center text-white shadow-lg shadow-blue-500/20 mr-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
            </div>
            <div>
              <p class="text-slate-400 text-sm">Client</p>
              <p class="text-white font-medium">Juan Dela Cruz</p>
            </div>
          </CardContent>
        </Card>
        
        <Card class="bg-gradient-to-br from-slate-900 to-slate-800 border-slate-700 hover:border-amber-500/50 transition-colors">
          <CardContent class="p-5 flex items-center">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-amber-500 to-orange-400 flex items-center justify-center text-white shadow-lg shadow-amber-500/20 mr-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
            </div>
            <div>
              <p class="text-slate-400 text-sm">Project</p>
              <p class="text-white font-medium">Living Room Painting</p>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-slate-900 to-slate-800 border-slate-700 hover:border-teal-500/50 transition-colors">
          <CardContent class="p-5 flex items-center">
            <div class="w-12 h-12 rounded-xl bg-gradient-to-r from-teal-500 to-emerald-400 flex items-center justify-center text-white shadow-lg shadow-teal-500/20 mr-4">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
            </div>
            <div>
              <p class="text-slate-400 text-sm">Distributor</p>
              <p class="text-white font-medium">Cavite Paint Supply</p>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <div class="lg:col-span-2">
        <div class="relative rounded-2xl overflow-hidden shadow-2xl bg-black min-h-[500px] border border-slate-800">
          <div v-if="isUnityLoading" class="absolute inset-0 bg-black/90 backdrop-blur-md flex items-center justify-center z-10">
            <div class="text-center max-w-sm p-8">
              <div class="relative w-20 h-20 mx-auto mb-8">
                <div class="absolute w-full h-full border-8 border-transparent border-t-blue-500 rounded-full animate-spin"></div>
              </div>
              <h3 class="text-xl font-bold text-white mb-2">Loading Unity WebGL</h3>
              <p class="text-slate-300 mb-4">Initializing 3D visualization engine...</p>
              <div class="w-full h-2 bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-purple-500 transition-all duration-300" :style="{ width: loadingProgress + '%' }"></div>
              </div>
              <span class="text-sm text-slate-400 mt-2 block">{{ loadingProgress }}%</span>
            </div>
          </div>

          <div class="unity-webgl-canvas w-full h-[500px] bg-[#1a1a1a]">
            <div class="relative w-full h-full flex items-center justify-center">
              <div class="placeholder-house">
                <div class="house-container">
                  <div class="house-main" :style="{ backgroundColor: currentColor.hex }">
                    <div class="house-roof"></div>
                    <div class="house-door"></div>
                    <div class="house-windows">
                      <div class="window left-window"></div>
                      <div class="window right-window"></div>
                      <div class="window top-window"></div>
                    </div>
                  </div>
                  <div class="side-wall" :style="{ backgroundColor: currentColor.hex }"></div>
                  <div class="ground"></div>
                </div>
              </div>
              
              <div class="absolute top-5 right-5 bg-gradient-to-r from-violet-600 to-pink-600 text-white px-4 py-2 rounded-full flex flex-col items-center shadow-lg">
                <span class="text-xs font-bold tracking-widest uppercase">UNITY</span>
                <span class="text-[0.6rem] opacity-80">WebGL</span>
              </div>
              
              <div class="absolute bottom-5 left-5 bg-black/70 backdrop-blur-md p-3 rounded-lg border border-slate-700">
                <div class="flex items-center mb-2">
                  <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l4-4 4 4m0 6l-4 4-4-4" /></svg>
                  <span class="text-slate-300 text-sm">Drag to rotate view</span>
                </div>
                <div class="flex items-center">
                  <svg class="w-4 h-4 mr-2 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                  <span class="text-slate-300 text-sm">Scroll to zoom</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <Button 
            v-for="scene in ['living-room', 'bedroom', 'kitchen', 'exterior']"
            :key="scene"
            @click="changeScene(scene)"
            :variant="activeScene === scene ? 'default' : 'outline'"
            :class="['h-auto py-3', activeScene === scene ? 'bg-gradient-to-r from-blue-500 to-purple-500 border-0 text-white' : 'bg-slate-900 border-slate-700 text-slate-300 hover:text-white hover:border-blue-500']"
          >
             <span class="capitalize">{{ scene.replace('-', ' ') }}</span>
          </Button>
        </div>
      </div>

      <div class="space-y-6">
        <Card class="bg-slate-900 border-slate-800">
          <CardHeader><h2 class="text-xl font-bold text-white">Color Controls</h2></CardHeader>
          <CardContent>
            <div class="bg-slate-950/50 rounded-xl p-6 border border-slate-800 mb-6">
              <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-white">Current Color</h3>
                <div class="text-right">
                  <p class="text-slate-400 text-xs font-mono">RGB: {{ currentColor.rgb.join(', ') }}</p>
                  <p class="text-slate-400 text-xs font-mono">HEX: {{ currentColor.hex }}</p>
                </div>
              </div>
              <div class="h-24 rounded-lg flex items-center justify-center shadow-inner transition-colors duration-300" :style="{ backgroundColor: currentColor.hex }">
                <div class="bg-black/40 backdrop-blur-sm px-4 py-2 rounded-full text-white font-bold text-sm">{{ currentColor.name }}</div>
              </div>
            </div>

            <div class="space-y-6">
              <div v-for="(val, index) in ['Red', 'Green', 'Blue']" :key="index" class="space-y-3">
                <div class="flex justify-between">
                  <Label class="text-slate-300 font-medium">{{ val }}</Label>
                  <span class="text-white font-bold font-mono">{{ currentColor.rgb[index] }}</span>
                </div>
                <input 
                  type="range" min="0" max="255" 
                  v-model="currentColor.rgb[index]" 
                  @input="updateColor"
                  class="w-full h-2 bg-slate-700 rounded-lg appearance-none cursor-pointer accent-white"
                  :class="{'accent-red-500': index===0, 'accent-green-500': index===1, 'accent-blue-500': index===2}"
                />
              </div>

              <div class="mt-6">
                <h4 class="text-slate-300 font-medium mb-3 text-sm">Color Presets</h4>
                <div class="grid grid-cols-5 gap-3">
                  <button 
                    v-for="preset in colorPresets" 
                    :key="preset.name"
                    @click="selectPreset(preset)"
                    class="w-full aspect-square rounded-lg border-2 border-transparent hover:border-white hover:scale-110 transition-all shadow-sm"
                    :style="{ backgroundColor: preset.hex }"
                    :title="preset.name"
                  ></button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-slate-900 border-slate-800">
          <CardHeader><h2 class="text-xl font-bold text-white">Save & Export</h2></CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label>Color Name</Label>
              <Input v-model="saveData.name" placeholder="e.g., Ocean Breeze" class="bg-slate-950 border-slate-700" />
            </div>
            
            <div class="space-y-2">
              <Label>Project Reference</Label>
              <Select v-model="saveData.projectId">
                <SelectTrigger class="bg-slate-950 border-slate-700"><SelectValue placeholder="Select project" /></SelectTrigger>
                <SelectContent class="bg-slate-900 border-slate-700 text-white">
                  <SelectItem v-for="p in projects" :key="p.id" :value="String(p.id)">{{ p.name }} - {{ p.client }}</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label>Paint Brand</Label>
              <Select v-model="saveData.paintBrand">
                <SelectTrigger class="bg-slate-950 border-slate-700"><SelectValue placeholder="Select brand" /></SelectTrigger>
                <SelectContent class="bg-slate-900 border-slate-700 text-white">
                   <SelectItem value="Boysen">Boysen</SelectItem>
                   <SelectItem value="Davies">Davies</SelectItem>
                   <SelectItem value="Rainbow">Rainbow</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label>Notes</Label>
              <Textarea v-model="saveData.notes" rows="2" placeholder="Add notes..." class="bg-slate-950 border-slate-700" />
            </div>
            
            <div class="grid grid-cols-2 gap-3 pt-2">
              <Button @click="saveColor" class="bg-gradient-to-r from-blue-500 to-purple-500 border-0 text-white">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" /></svg>
                Save Color
              </Button>
              <Button @click="exportColor" variant="outline" class="bg-slate-800 border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" /></svg>
                Export
              </Button>
            </div>

            <div class="mt-6">
              <h4 class="text-slate-300 font-medium mb-3 text-sm">Recent Colors</h4>
              <div class="space-y-2">
                 <div v-for="color in recentColors" :key="color.id" @click="loadRecentColor(color)" class="flex items-center p-2 rounded-lg hover:bg-slate-800 cursor-pointer transition-colors group">
                    <div class="w-8 h-8 rounded-md mr-3 border border-slate-700" :style="{ backgroundColor: color.hex }"></div>
                    <div class="flex-1 min-w-0">
                       <p class="text-white text-sm font-medium truncate">{{ color.name }}</p>
                       <p class="text-slate-500 text-xs">{{ color.hex }}</p>
                    </div>
                    <button @click.stop="deleteRecentColor(color.id)" class="text-slate-600 hover:text-red-400 opacity-0 group-hover:opacity-100 transition-opacity">
                       <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                    </button>
                 </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-r from-purple-900/20 to-pink-900/20 border-purple-800/50">
           <CardContent class="p-6">
             <h2 class="text-xl font-bold text-white mb-4">System Integration</h2>
             <p class="text-slate-400 mb-6 text-sm">This color will be linked to:</p>
             <div class="space-y-4">
                <div class="flex items-center p-3 bg-white/5 rounded-xl">
                   <div class="w-9 h-9 rounded-lg bg-gradient-to-r from-blue-500 to-cyan-400 flex items-center justify-center text-white mr-3"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" /></svg></div>
                   <div><p class="text-slate-400 text-xs">Client</p><p class="text-white font-medium text-sm">Juan Dela Cruz</p></div>
                </div>
                </div>
             <Button @click="linkToSystem" class="w-full mt-6 bg-gradient-to-r from-purple-600 to-pink-600 border-0 text-white">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                Link to System
             </Button>
           </CardContent>
        </Card>
      </div>
    </div>

    <Dialog v-model:open="showSaveModal">
      <DialogContent class="bg-slate-900 border-slate-700 text-slate-200 sm:max-w-md">
         <div class="flex flex-col items-center text-center p-4">
            <div class="w-16 h-16 rounded-full bg-gradient-to-r from-green-500 to-emerald-400 flex items-center justify-center mb-6 shadow-lg shadow-green-500/20">
               <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            </div>
            <h3 class="text-2xl font-bold text-white mb-2">Color Saved!</h3>
            <p class="text-xl font-medium text-blue-400 mb-6">{{ savedColorName }}</p>
            
            <div class="w-32 h-32 rounded-2xl shadow-inner mb-6 border border-slate-700" :style="{ backgroundColor: currentColor.hex }"></div>
            
            <div class="grid grid-cols-2 gap-8 w-full mb-6">
               <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                  <p class="text-slate-500 text-xs">RGB</p>
                  <p class="text-white font-mono">{{ currentColor.rgb.join(', ') }}</p>
               </div>
               <div class="bg-slate-950 p-3 rounded-lg border border-slate-800">
                  <p class="text-slate-500 text-xs">HEX</p>
                  <p class="text-white font-mono">{{ currentColor.hex }}</p>
               </div>
            </div>
            
            <div class="flex w-full gap-3">
               <Button variant="outline" class="flex-1 border-slate-700 hover:bg-slate-800 text-slate-300" @click="closeSaveModal">Continue Mixing</Button>
               <Button class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 border-0 text-white" @click="goToJobs">View Jobs</Button>
            </div>
         </div>
      </DialogContent>
    </Dialog>
  </div>
</template>

<script>
import { Card, CardContent, CardHeader } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'

export default {
  name: 'UnityColorMixer',
  components: { Card, CardContent, CardHeader, Button, Input, Select, SelectContent, SelectItem, SelectTrigger, SelectValue, Dialog, DialogContent, Label, Textarea },
  data() {
    return {
      isUnityLoading: false,
      loadingProgress: 0,
      activeScene: 'living-room',
      currentColor: { name: 'Ocean Breeze', hex: '#4CC9F0', rgb: [76, 201, 240] },
      colorPresets: [
        { name: 'Ocean Blue', hex: '#4CC9F0', rgb: [76, 201, 240] },
        { name: 'Sage Green', hex: '#8A9A5B', rgb: [138, 154, 91] },
        { name: 'Coral Sunset', hex: '#FF7F50', rgb: [255, 127, 80] },
        { name: 'Midnight Blue', hex: '#191970', rgb: [25, 25, 112] },
        { name: 'Soft Lavender', hex: '#B19CD9', rgb: [177, 156, 217] },
        // ... rest of presets
      ],
      saveData: { name: '', projectId: '', paintBrand: '', notes: '' },
      projects: [
        { id: 1, name: 'Living Room Painting', client: 'Juan Dela Cruz' },
        { id: 2, name: 'Bedroom Renovation', client: 'Maria Gonzales' },
        // ...
      ],
      recentColors: [
        { id: 1, name: 'Ocean Breeze', hex: '#4CC9F0', rgb: [76, 201, 240] },
        { id: 2, name: 'Sage Green', hex: '#8A9A5B', rgb: [138, 154, 91] },
        // ...
      ],
      showSaveModal: false,
      savedColorName: ''
    }
  },
  mounted() {
    this.simulateUnityLoading()
    this.setupColorPicker()
  },
  methods: {
    simulateUnityLoading() {
      this.isUnityLoading = true
      const interval = setInterval(() => {
        this.loadingProgress += 10
        if (this.loadingProgress >= 100) {
          clearInterval(interval)
          setTimeout(() => { this.isUnityLoading = false }, 500)
        }
      }, 300)
    },
    setupColorPicker() { this.updateColor() },
    updateColor() {
      const [r, g, b] = this.currentColor.rgb
      this.currentColor.hex = this.rgbToHex(Number(r), Number(g), Number(b))
      this.updateUnityColor()
    },
    rgbToHex(r, g, b) { return "#" + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1) },
    updateUnityColor() { console.log('Updating Unity color to:', this.currentColor) },
    selectPreset(preset) {
      this.currentColor = JSON.parse(JSON.stringify(preset)) // Deep copy to avoid reference issues
      this.saveData.name = preset.name
      this.updateColor()
    },
    changeScene(scene) { this.activeScene = scene },
    saveColor() {
      if (!this.saveData.name) { alert('Please enter a color name'); return }
      const colorData = { name: this.saveData.name, hex: this.currentColor.hex, rgb: this.currentColor.rgb, ...this.saveData, date: new Date().toISOString() }
      setTimeout(() => {
        this.recentColors.unshift({ id: Date.now(), name: colorData.name, hex: colorData.hex, rgb: colorData.rgb })
        if (this.recentColors.length > 10) this.recentColors = this.recentColors.slice(0, 10)
        this.savedColorName = colorData.name
        this.showSaveModal = true
        this.saveData = { name: '', projectId: '', paintBrand: '', notes: '' }
      }, 500)
    },
    exportColor() {
      const colorData = { name: this.saveData.name || this.currentColor.name, hex: this.currentColor.hex, rgb: this.currentColor.rgb, date: new Date().toISOString() }
      const dataStr = JSON.stringify(colorData, null, 2)
      const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr)
      const exportFileDefaultName = `${colorData.name.replace(/\s+/g, '_')}_${Date.now()}.json`
      const linkElement = document.createElement('a')
      linkElement.setAttribute('href', dataUri)
      linkElement.setAttribute('download', exportFileDefaultName)
      linkElement.click()
    },
    loadRecentColor(color) {
      this.currentColor = JSON.parse(JSON.stringify(color))
      this.saveData.name = color.name
      this.updateColor()
    },
    deleteRecentColor(colorId) { this.recentColors = this.recentColors.filter(color => color.id !== colorId) },
    linkToSystem() { alert('Color linked to system successfully!') },
    toggleFullscreen() {
      const element = document.querySelector('.unity-webgl-canvas')
      if (!document.fullscreenElement) { element.requestFullscreen().catch(err => { console.error(`Error: ${err.message}`) }) } else { document.exitFullscreen() }
    },
    resetScene() { this.currentColor = { name: 'Ocean Breeze', hex: '#4CC9F0', rgb: [76, 201, 240] }; this.updateColor() },
    closeSaveModal() { this.showSaveModal = false },
    goToJobs() { this.closeSaveModal(); this.$router.push('/service-provider/jobs') }
  }
}
</script>

<style scoped>
/* Unity House CSS Art (Preserved from original) */
.placeholder-house { position: relative; width: 300px; height: 300px; perspective: 1000px; }
.house-container { position: relative; width: 100%; height: 100%; transform-style: preserve-3d; transform: rotateY(-30deg) rotateX(10deg); }
.house-main { position: absolute; width: 200px; height: 150px; background: #4CC9F0; left: 50px; top: 100px; border-radius: 10px 10px 0 0; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3); transition: background-color 0.3s ease; }
.house-roof { position: absolute; width: 0; height: 0; border-left: 120px solid transparent; border-right: 120px solid transparent; border-bottom: 80px solid #2d3748; top: -70px; left: -20px; }
.house-door { position: absolute; width: 40px; height: 60px; background: #8B4513; bottom: 0; left: 80px; border-radius: 5px 5px 0 0; }
.house-windows { position: absolute; top: 30px; width: 100%; }
.window { position: absolute; width: 30px; height: 30px; background: #ADD8E6; border: 3px solid #1a202c; border-radius: 5px; }
.left-window { left: 20px; }
.right-window { right: 20px; }
.top-window { left: 85px; }
.side-wall { position: absolute; width: 100px; height: 150px; background: #4CC9F0; left: 200px; top: 100px; transform: skewY(30deg); border-radius: 0 10px 0 0; transition: background-color 0.3s ease; }
.ground { position: absolute; width: 400px; height: 50px; background: #4a5568; bottom: 0; left: -50px; border-radius: 10px; }

input[type=range]::-webkit-slider-thumb {
  -webkit-appearance: none; appearance: none;
  width: 20px; height: 20px; border-radius: 50%; 
  background: white; cursor: pointer; border: 2px solid rgba(0,0,0,0.1); box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}
</style>