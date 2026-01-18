<template>
  <div class="fixed inset-0 z-[9999] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm">
    <div class="relative w-full max-w-2xl">
      <!-- Animated background -->
      <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/10 via-purple-500/10 to-pink-500/10 rounded-3xl blur-2xl animate-pulse"></div>
      
      <div class="relative bg-gradient-to-br from-gray-900 to-gray-800 border border-gray-700/50 rounded-2xl shadow-2xl overflow-hidden">
        <!-- Modal header -->
        <div class="relative p-6 border-b border-gray-700/50">
          <!-- Icon with status -->
          <div class="flex items-center justify-center mb-4">
            <div class="relative">
              <!-- Status icon -->
              <div :class="[
                'w-24 h-24 rounded-full flex items-center justify-center shadow-2xl animate-pulse-slow',
                statusClass
              ]">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path v-if="verificationStatus === 'approved'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  <path v-else-if="verificationStatus === 'pending'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  <path v-else-if="verificationStatus === 'rejected'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <!-- Pulsing ring -->
              <div :class="[
                'absolute inset-0 border-4 rounded-full animate-ping-slow',
                statusBorderClass
              ]"></div>
            </div>
          </div>
          
          <h3 class="text-3xl font-bold text-center bg-gradient-to-r from-blue-400 via-purple-400 to-pink-400 bg-clip-text text-transparent mb-2">
            {{ title }}
          </h3>
          <p class="text-gray-300 text-center text-lg">
            {{ message }}
          </p>
        </div>

        <!-- Modal body -->
        <div class="p-6">
          <!-- Status-specific content -->
          <div v-if="verificationStatus === 'none'" class="mb-6">
            <div class="bg-gradient-to-r from-blue-500/10 to-purple-500/10 border border-blue-500/20 rounded-xl p-5">
              <h4 class="text-xl font-semibold text-white mb-3">📋 Requirements Checklist</h4>
              <ul class="space-y-3 text-gray-300">
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd" />
                  </svg>
                  <span>Company Name and Business Details</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd" />
                  </svg>
                  <span>Valid Government ID (Passport, Driver's License, etc.)</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd" />
                  </svg>
                  <span>DTI Certificate / SEC Registration</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd" />
                  </svg>
                  <span>Mayor's Business Permit</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd" />
                  </svg>
                  <span>Barangay Clearance Certificate</span>
                </li>
                <li class="flex items-start">
                  <svg class="w-5 h-5 text-yellow-400 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V5z" clip-rule="evenodd" />
                  </svg>
                  <span>Business Registration Plate/TIN Certificate</span>
                </li>
              </ul>
            </div>
            
            <p class="text-gray-400 text-sm mt-4">
              ⏰ Approval typically takes 1-3 business days after submission.
            </p>
          </div>

          <div v-else-if="verificationStatus === 'pending'" class="mb-6">
            <div class="bg-gradient-to-r from-yellow-500/10 to-orange-500/10 border border-yellow-500/20 rounded-xl p-5">
              <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-yellow-500/20 mr-3">
                  <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-xl font-semibold text-white">Under Review</h4>
                  <p class="text-yellow-300">Your documents are being verified by our team</p>
                </div>
              </div>
              
              <div class="space-y-3">
                <div class="flex items-center justify-between">
                  <span class="text-gray-300">Document Review</span>
                  <span class="text-yellow-400">In Progress</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2">
                  <div class="bg-gradient-to-r from-yellow-500 to-orange-400 h-2 rounded-full w-3/4 animate-pulse"></div>
                </div>
                <p class="text-gray-400 text-sm">
                  Our verification team is checking your submitted documents. You'll receive a notification once the review is complete.
                </p>
              </div>
            </div>
          </div>

          <div v-else-if="verificationStatus === 'rejected'" class="mb-6">
            <div class="bg-gradient-to-r from-red-500/10 to-pink-500/10 border border-red-500/20 rounded-xl p-5">
              <div class="flex items-center mb-4">
                <div class="p-2 rounded-lg bg-red-500/20 mr-3">
                  <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.882 16.5c-.77.833.192 2.5 1.732 2.5z" />
                  </svg>
                </div>
                <div>
                  <h4 class="text-xl font-semibold text-white">Verification Rejected</h4>
                  <p class="text-red-300">Please review the reasons below</p>
                </div>
              </div>
              
              <div class="bg-red-900/20 border border-red-700/30 rounded-lg p-4">
                <h5 class="font-semibold text-red-300 mb-2">Reasons for Rejection:</h5>
                <ul class="list-disc list-inside text-gray-300 space-y-1">
                  <li>Documents are unclear or blurry</li>
                  <li>Missing required documents</li>
                  <li>Information mismatch</li>
                  <li>Expired documents</li>
                </ul>
                <p class="text-gray-400 text-sm mt-3">
                  Please update your documents in the Profile Settings section and resubmit for verification.
                </p>
              </div>
            </div>
          </div>

          <!-- Action buttons -->
          <div class="flex flex-col sm:flex-row gap-3">
            <button
              v-if="verificationStatus === 'none' || verificationStatus === 'rejected'"
              @click="goToProfileSettings"
              class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-blue-600 hover:to-purple-600 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group relative overflow-hidden"
            >
              <!-- Animated shine effect -->
              <div class="absolute inset-0 overflow-hidden">
                <div class="absolute top-0 left-0 w-12 h-full bg-white/20 skew-x-12 animate-shine"></div>
              </div>
              
              <div class="relative flex items-center justify-center space-x-2">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                <span>Submit Requirements Now</span>
              </div>
            </button>
            
            <button
              v-else-if="verificationStatus === 'pending'"
              @click="goToProfileSettings"
              class="flex-1 py-3 px-6 rounded-xl bg-gradient-to-r from-yellow-500 to-orange-500 text-white font-semibold shadow-lg hover:shadow-xl hover:from-yellow-600 hover:to-orange-600 transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group relative overflow-hidden"
            >
              <div class="relative flex items-center justify-center space-x-2">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Check Status</span>
              </div>
            </button>

            <button
              @click="closeModal"
              :class="[
                'flex-1 py-3 px-6 rounded-xl border font-semibold transition-all duration-300 hover:scale-[1.02] active:scale-[0.98] group',
                verificationStatus === 'none' || verificationStatus === 'rejected'
                  ? 'border-gray-600 bg-gray-800/50 text-gray-300 hover:bg-gray-700/50 hover:text-white'
                  : 'border-yellow-600 bg-yellow-500/10 text-yellow-300 hover:bg-yellow-500/20 hover:text-yellow-200'
              ]"
            >
              <div class="flex items-center justify-center space-x-2">
                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span>{{ verificationStatus === 'pending' ? 'Close' : 'Maybe Later' }}</span>
              </div>
            </button>
          </div>
        </div>

        <!-- Modal footer -->
        <div class="px-6 py-4 border-t border-gray-700/50 bg-gray-900/50">
          <div class="flex items-center justify-between text-xs text-gray-500">
            <div class="flex items-center space-x-2">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Business Verification Required</span>
            </div>
            <span>CaviteGo Paint Distributor Portal</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'

const props = defineProps({
  verificationStatus: {
    type: String,
    default: 'none'
  }
})

const emit = defineEmits(['close'])

const router = useRouter()

const statusClass = computed(() => {
  switch (props.verificationStatus) {
    case 'approved': return 'bg-gradient-to-r from-green-500 to-emerald-400'
    case 'pending': return 'bg-gradient-to-r from-yellow-500 to-orange-400'
    case 'rejected': return 'bg-gradient-to-r from-red-500 to-pink-400'
    default: return 'bg-gradient-to-r from-blue-500 to-purple-500'
  }
})

const statusBorderClass = computed(() => {
  switch (props.verificationStatus) {
    case 'approved': return 'border-green-500/30'
    case 'pending': return 'border-yellow-500/30'
    case 'rejected': return 'border-red-500/30'
    default: return 'border-blue-500/30'
  }
})

const title = computed(() => {
  switch (props.verificationStatus) {
    case 'approved': return 'Business Verified!'
    case 'pending': return 'Verification in Progress'
    case 'rejected': return 'Verification Required'
    default: return 'Business Verification Required'
  }
})

const message = computed(() => {
  switch (props.verificationStatus) {
    case 'approved': return 'Your business is fully verified. Start operating now!'
    case 'pending': return 'Your documents are under review. Please wait for approval.'
    case 'rejected': return 'Your verification was rejected. Please update your documents.'
    default: return 'Please submit your business requirements to start operating as a distributor.'
  }
})

const closeModal = () => {
  emit('close')
}

const goToProfileSettings = () => {
  router.push('/distributor/profileSettings')
  emit('close')
}
</script>

<style scoped>
@keyframes shine {
  0% { transform: translateX(-100%) skewX(-12deg); }
  100% { transform: translateX(200%) skewX(-12deg); }
}

.animate-shine {
  animation: shine 2s infinite;
}

@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.animate-pulse-slow {
  animation: pulse-slow 3s infinite;
}

@keyframes ping-slow {
  0% { transform: scale(1); opacity: 0.8; }
  100% { transform: scale(1.5); opacity: 0; }
}

.animate-ping-slow {
  animation: ping-slow 2s infinite;
}
</style>