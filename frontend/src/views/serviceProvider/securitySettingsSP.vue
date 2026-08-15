<template>
  <div class="max-w-8xl px-4 py-8 mx-auto sm:px-6 lg:px-8">
    
    <!-- Header Section -->
    <div class="mb-8">
      <div class="flex items-center gap-3 mb-2">
        <div class="flex items-center justify-center w-10 h-10 bg-indigo-100 rounded-xl text-indigo-600 shadow-inner">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
          </svg>
        </div>
        <h1 class="text-3xl font-black tracking-tight text-white-900">Security Settings</h1>
      </div>
      <p class="text-gray-500 font-medium ml-1">
        Manage your account security, login preferences, and recovery options to keep your account safe.
      </p>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white border border-gray-100 rounded-3xl shadow-xl shadow-gray-200/50 relative overflow-hidden">
      <!-- Decorative background accent -->
      <div class="absolute top-0 right-0 w-40 h-40 -mt-10 -mr-10 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-full blur-3xl pointer-events-none"></div>
      
      <div class="relative z-10 p-6 sm:p-8">
        <div v-if="loading" class="flex flex-col items-center justify-center py-12">
          <div class="w-10 h-10 mb-4 border-4 border-indigo-200 rounded-full border-t-indigo-600 animate-spin"></div>
          <p class="font-bold text-gray-500 animate-pulse">Encrypting connection...</p>
        </div>

        <div v-else class="space-y-5">
          <!-- Setting Items -->
          <div 
            v-for="(item, key) in availableSettings" 
            :key="key" 
            class="group flex flex-col sm:flex-row sm:items-center justify-between p-5 bg-white border border-gray-100 rounded-2xl shadow-sm hover:shadow-md hover:border-indigo-100 transition-all duration-300 gap-4 sm:gap-0"
          >
            <div class="flex items-start w-full gap-4 sm:w-5/6">
              <!-- Dynamic Icon -->
              <div class="flex items-center justify-center shrink-0 w-12 h-12 rounded-xl bg-gray-50 text-gray-400 group-hover:bg-indigo-50 group-hover:text-indigo-600 transition-colors duration-300 shadow-inner" v-html="item.icon">
              </div>
              
              <div class="mt-0.5 space-y-1">
                <label class="text-base font-bold text-gray-900 transition-colors cursor-pointer group-hover:text-indigo-900">
                  {{ item.title }}
                </label>
                <p class="pr-4 text-sm font-medium leading-relaxed text-gray-500">
                  {{ item.description }}
                </p>
              </div>
            </div>
            
            <!-- Modern Switch -->
            <div class="flex shrink-0 pl-16 sm:justify-end sm:pl-0">
              <button 
                type="button" 
                role="switch" 
                :aria-checked="settings[key]" 
                @click="triggerToggle(key, !settings[key])"
                class="relative inline-flex h-7 w-12 shrink-0 cursor-pointer items-center rounded-full border-2 border-transparent transition-all duration-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 shadow-inner"
                :class="settings[key] ? 'bg-gradient-to-r from-blue-500 to-indigo-600 shadow-indigo-500/30' : 'bg-gray-200'"
              >
                <span 
                  class="pointer-events-none flex items-center justify-center h-6 w-6 rounded-full bg-white shadow-md ring-0 transition-transform duration-300 ease-out"
                  :class="settings[key] ? 'translate-x-5' : 'translate-x-0'"
                >
                  <!-- Inner checkmark when active -->
                  <svg v-if="settings[key]" class="w-3.5 h-3.5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Universal Modals via Teleport -->
    <Teleport to="body">
      <transition 
        enter-active-class="transition duration-300 ease-out" 
        enter-from-class="opacity-0" 
        enter-to-class="opacity-100" 
        leave-active-class="transition duration-200 ease-in" 
        leave-from-class="opacity-100" 
        leave-to-class="opacity-0"
      >
        <div v-if="activeModal !== null" class="fixed inset-0 z-[100] bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6">
          <transition 
            enter-active-class="transition duration-300 ease-out delay-75" 
            enter-from-class="opacity-0 scale-95 translate-y-4" 
            enter-to-class="opacity-100 scale-100 translate-y-0" 
            leave-active-class="transition duration-200 ease-in" 
            leave-from-class="opacity-100 scale-100 translate-y-0" 
            leave-to-class="opacity-0 scale-95 translate-y-4"
          >
            <div class="relative w-full max-w-lg overflow-y-auto bg-white shadow-2xl rounded-3xl max-h-[90vh] ring-1 ring-black/5">
              
              <!-- Close Button (Absolute) -->
              <button @click="closeModal" class="absolute p-2 transition-colors rounded-full top-5 right-5 bg-gray-50 text-gray-400 hover:text-gray-600 hover:bg-gray-100 focus:outline-none">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
              </button>

              <div class="p-6 sm:p-8">
                
                <!-- 1. STANDARD CONFIRMATION MODAL -->
                <div v-if="activeModal === 'standard'">
                  <div class="flex items-center justify-center w-14 h-14 mb-5 bg-amber-100 rounded-2xl text-amber-500">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                  </div>
                  <h2 class="text-2xl font-black tracking-tight text-gray-900">Confirm Action</h2>
                  <p class="mt-2 text-base font-medium leading-relaxed text-gray-500" v-if="pendingField">
                    Are you sure you want to <strong :class="pendingValue ? 'text-green-600' : 'text-red-600'">{{ pendingValue ? 'Enable' : 'Disable' }}</strong> 
                    "{{ availableSettings[pendingField]?.title }}"?
                  </p>
                  <div class="flex flex-col gap-3 mt-8 sm:flex-row sm:justify-end">
                    <button @click="closeModal" class="w-full sm:w-auto h-12 px-6 rounded-xl font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all active:scale-95">
                      Cancel
                    </button>
                    <button @click="confirmStandardToggle" :disabled="isProcessing" class="w-full sm:w-auto h-12 px-8 rounded-xl font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                      {{ isProcessing ? 'Saving...' : 'Yes, Continue' }}
                    </button>
                  </div>
                </div>

                <!-- 2. RECOVERY EMAIL ENTRY MODAL -->
                <div v-if="activeModal === 'recovery_email'">
                  <div class="flex items-center justify-center w-14 h-14 mb-5 text-blue-600 bg-blue-100 rounded-2xl">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                  </div>
                  <h2 class="text-2xl font-black tracking-tight text-gray-900">Setup Recovery Email</h2>
                  <p class="mt-2 text-sm font-medium text-gray-500">Enter the email address you wish to use as a secure backup.</p>
                  
                  <div class="mt-6">
                    <label class="block mb-2 text-xs font-bold tracking-wider text-gray-700 uppercase">Backup Email Address</label>
                    <input 
                      type="email" 
                      v-model="recoveryEmailInput" 
                      placeholder="e.g., backup@example.com" 
                      class="w-full p-4 font-medium transition-all border border-gray-200 outline-none bg-gray-50 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white"
                    />
                  </div>
                  
                  <div class="flex flex-col gap-3 mt-8 sm:flex-row sm:justify-end">
                    <button @click="closeModal" class="w-full sm:w-auto h-12 px-6 rounded-xl font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all active:scale-95">
                      Cancel
                    </button>
                    <button @click="sendRecoveryOtp" :disabled="isProcessing || !recoveryEmailInput" class="flex items-center justify-center w-full h-12 px-8 font-bold text-white transition-all shadow-lg sm:w-auto rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-indigo-500/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                      <svg v-if="isProcessing" class="w-5 h-5 mr-2 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      {{ isProcessing ? 'Sending...' : 'Send OTP' }}
                    </button>
                  </div>
                </div>

                <!-- 3. OTP VERIFICATION MODAL -->
                <div v-if="activeModal === 'otp'">
                  <div class="flex items-center justify-center w-14 h-14 mb-5 text-indigo-600 bg-indigo-100 rounded-2xl">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                  </div>
                  <h2 class="text-2xl font-black tracking-tight text-gray-900">Verify Identity</h2>
                  <p class="mt-2 text-sm font-medium text-gray-500">
                    Enter the 6-digit code sent to <strong class="text-gray-800">{{ pendingValue ? recoveryEmailInput : 'your registered email' }}</strong>.
                  </p>
                  
                  <div class="mt-8">
                    <input 
                      type="text" 
                      v-model="otpInput" 
                      placeholder="••••••" 
                      maxlength="6"
                      class="w-full p-4 text-center tracking-[0.5em] text-3xl font-black text-gray-900 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white outline-none transition-all placeholder:text-gray-300"
                    />
                  </div>
                  
                  <div class="flex flex-col gap-3 mt-8 sm:flex-row sm:justify-end">
                    <button @click="closeModal" class="w-full sm:w-auto h-12 px-6 rounded-xl font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all active:scale-95">
                      Cancel
                    </button>
                    <button @click="verifyRecoveryOtp" :disabled="isProcessing || otpInput.length < 6" class="flex items-center justify-center w-full h-12 px-8 font-bold text-white transition-all shadow-lg sm:w-auto rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-indigo-500/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed">
                      <svg v-if="isProcessing" class="w-5 h-5 mr-2 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                      {{ isProcessing ? 'Verifying...' : 'Verify & Save' }}
                    </button>
                  </div>
                </div>

                <!-- 4. SECURITY QUESTIONS WIZARD MODAL -->
                <div v-if="activeModal === 'security_questions'">
                  <div class="flex items-center justify-between pr-8 mb-6">
                    <h2 class="text-2xl font-black tracking-tight text-gray-900">Security Questions</h2>
                    <span class="px-3 py-1.5 text-xs font-black tracking-wider text-indigo-700 uppercase bg-indigo-100 rounded-full">
                      Step {{ currentQuestionIndex + 1 }} / {{ securityQuestionsList.length }}
                    </span>
                  </div>

                  <!-- Neon Glowing Progress Bar -->
                  <div class="relative w-full h-2 mb-8 overflow-hidden bg-gray-100 rounded-full">
                    <div 
                      class="relative h-full transition-all duration-500 ease-out rounded-full bg-gradient-to-r from-blue-500 to-indigo-500" 
                      :style="{ width: ((currentQuestionIndex + 1) / securityQuestionsList.length * 100) + '%' }"
                    >
                      <div class="absolute top-0 bottom-0 right-0 w-10 blur-md bg-white/30"></div>
                    </div>
                  </div>
                  
                  <!-- Question View -->
                  <div class="min-h-[140px] flex flex-col justify-center">
                    <transition name="slide-fade" mode="out-in">
                      <div :key="currentQuestionIndex" class="space-y-5">
                        <label class="block text-xl font-bold leading-snug text-gray-800">
                          {{ securityQuestionsList[currentQuestionIndex] }}
                        </label>
                        <input 
                          type="text" 
                          v-model="securityAnswers[currentQuestionIndex]" 
                          @keyup.enter="nextWizardStep"
                          placeholder="Type your secret answer..."
                          class="w-full p-4 text-base font-medium transition-all shadow-inner outline-none bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent focus:bg-white"
                          autofocus
                        />
                      </div>
                    </transition>
                  </div>

                  <!-- Wizard Controls -->
                  <div class="flex flex-col-reverse gap-3 pt-6 mt-10 border-t border-gray-100 sm:flex-row sm:justify-between sm:gap-0">
                    <button @click="closeModal" class="w-full px-6 transition-colors text-gray-500 h-12 font-bold hover:text-gray-900 sm:w-auto rounded-xl">
                      Cancel Setup
                    </button>
                    
                    <div class="flex justify-end w-full gap-3 sm:w-auto">
                      <button 
                        v-if="currentQuestionIndex > 0" 
                        @click="currentQuestionIndex--" 
                        class="w-full sm:w-auto h-12 px-6 rounded-xl font-bold text-gray-700 bg-gray-50 hover:bg-gray-100 border border-gray-200 transition-all active:scale-95"
                      >
                        Back
                      </button>

                      <button 
                        v-if="currentQuestionIndex < securityQuestionsList.length - 1" 
                        @click="nextWizardStep" 
                        :disabled="!securityAnswers[currentQuestionIndex].trim()" 
                        class="w-full sm:w-auto h-12 px-8 rounded-xl font-bold text-white bg-gray-900 hover:bg-black shadow-lg shadow-gray-900/20 transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        Next Step
                      </button>

                      <button 
                        v-else 
                        @click="saveSecurityQuestions" 
                        :disabled="isProcessing || !securityAnswers[currentQuestionIndex].trim()" 
                        class="flex items-center justify-center w-full h-12 px-8 font-bold text-white transition-all shadow-lg sm:w-auto rounded-xl bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 shadow-emerald-500/30 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
                      >
                        <svg v-if="isProcessing" class="w-5 h-5 mr-2 text-white animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ isProcessing ? 'Saving...' : 'Finish & Save' }}
                      </button>
                    </div>
                  </div>
                </div>

              </div>
            </div>
          </transition>
        </div>
      </transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '@/utils/axios'; 

const loading = ref(true);
const isProcessing = ref(false);

const settings = ref({
  email_login_alerts: false,
  one_device_login: false,
  session_timeout: false,
  remember_this_device: false,
  account_recovery_email: false,
  security_questions: false,
});

// Dictionary with SVG Icons
const availableSettings = {
  email_login_alerts: { 
    title: 'Email Login Alerts', 
    description: 'Receive an email notification whenever your account is accessed from a new device or IP address.',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>`
  },
  one_device_login: { 
    title: 'One Device Login', 
    description: 'Enhance security by ensuring only one device can be logged into your account at any given time.',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>`
  },
  session_timeout: { 
    title: 'Session Timeout', 
    description: 'Automatically logs the user out after a specific period of inactivity to prevent unauthorized access.',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
  },
  remember_this_device: { 
    title: 'Remember This Device', 
    description: 'Save this trusted device to bypass strict two-factor authentication requirements on future logins.',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`
  },
  account_recovery_email: { 
    title: 'Account Recovery Email', 
    description: 'Register an alternative backup email address to safely recover your account if you lose primary access.',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>`
  },
  security_questions: { 
    title: 'Security Questions', 
    description: 'Establish secret questions as a fallback verification method in case of severe account lockout.',
    icon: `<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>`
  },
};

const securityQuestionsList = [
  "What was the first food you learned to cook?",
  "What was the name of your first pet?",
  "What was your first job?",
  "What was the name of your favorite childhood teacher?",
  "What nickname did you have as a child?"
];

// Modal States
const activeModal = ref(null); 
const pendingField = ref(null);
const pendingValue = ref(false);

// Inputs
const recoveryEmailInput = ref('');
const otpInput = ref('');

// Wizard State
const currentQuestionIndex = ref(0);
const securityAnswers = ref(['', '', '', '', '']);

// Fetch Init
const fetchSettings = async () => {
  try {
    const response = await api.get('/service-provider/security-settings'); 
    if (response.data) {
      for (const key in settings.value) {
        if (response.data[key] !== undefined) {
          settings.value[key] = Boolean(response.data[key]);
        }
      }
    }
  } catch (error) {
    console.error('Error fetching security settings:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => fetchSettings());

// Routing the clicks
const triggerToggle = (field, newValue) => {
  pendingField.value = field;
  pendingValue.value = newValue;
  
  if (field === 'account_recovery_email') {
    if (newValue) {
      recoveryEmailInput.value = '';
      activeModal.value = 'recovery_email';
    } else {
      sendRecoveryOtp(); 
    }
  } else if (field === 'security_questions') {
    if (newValue) {
      securityAnswers.value = ['', '', '', '', ''];
      currentQuestionIndex.value = 0;
      activeModal.value = 'security_questions';
    } else {
      activeModal.value = 'standard'; 
    }
  } else {
    activeModal.value = 'standard';
  }
};

const closeModal = () => {
  activeModal.value = null;
  setTimeout(() => {
    pendingField.value = null;
  }, 300);
};

const nextWizardStep = () => {
  if (securityAnswers.value[currentQuestionIndex.value].trim() && currentQuestionIndex.value < securityQuestionsList.length - 1) {
    currentQuestionIndex.value++;
  } else if (currentQuestionIndex.value === securityQuestionsList.length - 1) {
    saveSecurityQuestions();
  }
};

// ------------------------------------
// API HANDLERS
// ------------------------------------

const confirmStandardToggle = async () => {
  isProcessing.value = true;
  const field = pendingField.value;
  const value = pendingValue.value;

  try {
    if (field === 'security_questions' && value === false) {
      await api.post('/service-provider/security-settings/questions', { action: 'disable' });
    } else {
      await api.put('/service-provider/security-settings', { field, value });
    }
    settings.value[field] = value;
    closeModal();
  } catch (error) {
    alert('Failed to update setting.');
  } finally {
    isProcessing.value = false;
  }
};

const sendRecoveryOtp = async () => {
  isProcessing.value = true;
  const action = pendingValue.value ? 'enable' : 'disable';
  
  try {
    await api.post('/service-provider/security-settings/send-otp', {
      action: action,
      recovery_email: pendingValue.value ? recoveryEmailInput.value : null
    });
    
    otpInput.value = '';
    activeModal.value = 'otp';
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to send OTP.');
    if(action === 'disable') closeModal();
  } finally {
    isProcessing.value = false;
  }
};

const verifyRecoveryOtp = async () => {
  isProcessing.value = true;
  try {
    await api.post('/service-provider/security-settings/verify-otp', { otp: otpInput.value });
    settings.value.account_recovery_email = pendingValue.value;
    closeModal();
  } catch (error) {
    alert(error.response?.data?.message || 'Invalid or Expired OTP.');
  } finally {
    isProcessing.value = false;
  }
};

const saveSecurityQuestions = async () => {
  if (!securityAnswers.value[currentQuestionIndex.value].trim()) return;

  isProcessing.value = true;
  try {
    await api.post('/service-provider/security-settings/questions', {
      action: 'enable',
      answers: securityAnswers.value
    });
    settings.value.security_questions = true;
    closeModal();
  } catch (error) {
    alert('Failed to save security questions.');
  } finally {
    isProcessing.value = false;
  }
};
</script>

<style scoped>
/* Sleek slide-fade transition for the wizard text to pop in */
.slide-fade-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-fade-leave-active {
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>