<template>
  <Dialog :open="true" @update:open="$emit('close')">
    <DialogContent class="bg-slate-900/95 backdrop-blur-md border-slate-800 text-white max-w-[500px] rounded-3xl p-0 overflow-hidden shadow-2xl">
      <div class="h-2 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 w-full" />
      
      <div class="p-8">
        <div class="flex flex-col items-center text-center">
          <div class="relative mb-6">
            <div class="w-24 h-24 rounded-full bg-yellow-500/10 flex items-center justify-center relative z-10">
              <ShieldAlert class="w-12 h-12 text-yellow-500 animate-pulse" />
            </div>
            <div class="absolute inset-0 bg-yellow-500/5 rounded-full animate-ping" />
          </div>

          <DialogHeader>
            <DialogTitle class="text-3xl font-black bg-gradient-to-r from-yellow-400 to-orange-500 bg-clip-text text-transparent mb-2">
              Verification Required
            </DialogTitle>
            <DialogDescription class="text-slate-400 text-base leading-relaxed">
              Your account is currently in the <strong>{{ verificationStatus }}</strong> stage. 
              {{ descriptionText }}
            </DialogDescription>
          </DialogHeader>

          <div class="w-full mt-8 p-4 rounded-2xl bg-slate-800/50 border border-slate-700/50 flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-xl bg-slate-900 flex items-center justify-center border border-slate-700">
                <FileText class="w-5 h-5 text-blue-400" />
              </div>
              <div class="text-left">
                <p class="text-xs text-slate-500 uppercase font-bold tracking-wider">Current Status</p>
                <p class="text-sm font-semibold text-slate-200 capitalize">{{ verificationStatus }}</p>
              </div>
            </div>
            <Badge variant="outline" class="bg-yellow-500/10 text-yellow-500 border-yellow-500/20">Action Needed</Badge>
          </div>

          <div class="flex flex-col w-full gap-3 mt-8">
            <Button 
              class="w-full h-12 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-500 hover:to-purple-500 font-bold transition-all duration-300"
              @click="goToVerification"
            >
              Submit Requirements
            </Button>
          </div>
        </div>
      </div>
      
      <div class="bg-slate-950/50 p-4 border-t border-slate-800/50 text-center">
        <p class="text-[10px] text-slate-500 uppercase tracking-widest flex items-center justify-center gap-2">
          <Lock class="w-3 h-3" /> Secure Business Verification
        </p>
      </div>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ShieldAlert, FileText, Lock } from 'lucide-vue-next'
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription } from '@/components/ui/dialog'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { useRouter } from 'vue-router'

const props = defineProps({
  verificationStatus: {
    type: String,
    default: 'none'
  },
  // Added redirect route with distributor as default
  redirectRoute: {
    type: String,
    default: '/distributor/ProfileSettings' 
  },
  // Added dynamic description with distributor as default
  descriptionText: {
    type: String,
    default: 'To access all distributor features like inventory management and sales analytics, please complete your business verification.'
  }
})

const emit = defineEmits(['close'])
const router = useRouter()

const goToVerification = () => {
  emit('close')
  // Use the dynamic prop instead of a hardcoded string
  router.push(props.redirectRoute) 
}
</script>