<template>
  <div class="technical-reports-container min-h-screen p-4 md:p-8">
    <div class="max-w-6xl mx-auto">
      
      <!-- Header -->
      <div class="mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
          <h1 class="text-3xl font-black text-slate-900 tracking-tight flex items-center gap-3">
            <Wrench class="w-8 h-8 text-blue-600" />
            Technical Support
          </h1>
          <p class="text-slate-600 mt-1">Encountered an issue? Report bugs or system errors directly to our administrators.</p>
        </div>
        
        <div class="flex items-center gap-3 bg-slate-100 p-1.5 rounded-xl border border-slate-200">
            <button 
                @click="activeTab = 'create'" 
                :class="['px-4 py-2 rounded-lg text-sm font-bold transition-all', activeTab === 'create' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900']"
            >
                Submit Report
            </button>
            <button 
                @click="activeTab = 'history'" 
                :class="['px-4 py-2 rounded-lg text-sm font-bold transition-all', activeTab === 'history' ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-500 hover:text-slate-900']"
            >
                My Reports
            </button>
        </div>
      </div>

      <!-- CREATE REPORT TAB (WIZARD) -->
      <div v-if="activeTab === 'create'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
        
        <!-- Wizard Progress Bar -->
        <div class="flex items-center border-b border-slate-100 bg-slate-50 px-6 py-4">
            <div v-for="(step, index) in steps" :key="index" class="flex-1 flex items-center relative">
                <div class="flex flex-col items-center relative z-10">
                    <div :class="[
                        'w-8 h-8 rounded-full flex items-center justify-center font-black text-sm transition-all duration-300 ring-4',
                        currentStep > index + 1 ? 'bg-emerald-500 text-white ring-emerald-100' :
                        currentStep === index + 1 ? 'bg-blue-600 text-white ring-blue-100' :
                        'bg-slate-200 text-slate-500 ring-transparent'
                    ]">
                        <Check v-if="currentStep > index + 1" class="w-4 h-4" />
                        <span v-else>{{ index + 1 }}</span>
                    </div>
                    <span :class="['text-[10px] uppercase tracking-wider font-bold mt-2 absolute top-full whitespace-nowrap', currentStep >= index + 1 ? 'text-blue-600' : 'text-slate-400']">
                        {{ step }}
                    </span>
                </div>
                <div v-if="index < steps.length - 1" :class="[
                    'flex-1 h-1 mx-4 rounded-full transition-colors duration-500',
                    currentStep > index + 1 ? 'bg-emerald-500/50' : 'bg-slate-200'
                ]"></div>
            </div>
        </div>

        <!-- Wizard Form Area -->
        <div class="p-6 md:p-10 min-h-[400px]">
            
            <!-- Step 1: Issue Classification -->
            <transition name="fade-slide" mode="out-in">
                <div v-if="currentStep === 1" class="space-y-6 max-w-2xl mx-auto">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 mb-1">Issue Classification</h2>
                        <p class="text-sm text-slate-600 mb-6">Help us understand what kind of problem you're facing in the Distributor Hub.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Category <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select v-model="form.category" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl px-4 py-3 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none">
                                    <option value="" disabled>Select a category...</option>
                                    <option value="bug">System Bug / Glitch</option>
                                    <option value="system_error">System Error (e.g. 500 Server Error)</option>
                                    <option value="login_issue">Login / Authentication Issue</option>
                                    <option value="payment_issue">Payment / Deposit Issue</option>
                                    <option value="order_issue">Order Processing Issue</option>
                                    <option value="inventory_issue">Inventory / Stock Issue</option>
                                    <option value="performance_issue">Performance / Lag Issue</option>
                                    <option value="display_issue">UI / Display Issue</option>
                                    <option value="security_issue">Security Vulnerability</option>
                                    <option value="other">Other</option>
                                </select>
                                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            </div>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Page Encountered <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select v-model="form.page" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl px-4 py-3 pr-10 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all appearance-none">
                                    <option value="" disabled>Where did this happen?</option>
                                    <option value="Dashboard">Dashboard</option>
                                    <option value="Supplier">Supplier</option>
                                    <option value="Product Available">Product Available</option>
                                    <option value="Paint Inventory">Paint Inventory</option>
                                    <option value="Product Deployment">Product Deployment</option>
                                    <option value="Orders / Requests">Orders / Requests</option>
                                    <option value="Procurement Approval">Procurement Approval</option>
                                    <option value="Color Demand Insights">Color Demand Insights</option>
                                    <option value="Sales History">Sales History</option>
                                    <option value="Operational Distributors">Operational Distributors</option>
                                    <option value="HR Managers">HR Managers</option>
                                    <option value="Finance Managers">Finance Managers</option>
                                    <option value="Working Hours">Working Hours</option>
                                    <option value="Payroll Frequency">Payroll Frequency</option>
                                    <option value="Service Providers">Service Providers</option>
                                    <option value="Partner Supplier">Partner Supplier</option>
                                    <option value="Reports">Reports</option>
                                    <option value="Technical Reports">Technical Reports</option>
                                    <option value="Notifications">Notifications</option>
                                    <option value="Profile / Settings">Profile / Settings</option>
                                    <option value="Other">Other (General)</option>
                                </select>
                                <ChevronDown class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Environment -->
                <div v-else-if="currentStep === 2" class="space-y-6 max-w-2xl mx-auto">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 mb-1">Environment Specs</h2>
                        <p class="text-sm text-slate-600 mb-6">We've auto-detected these details, but feel free to edit if you were using a different device.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                                <Smartphone class="w-4 h-4 text-blue-600" /> Device Type <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.device" type="text" placeholder="e.g. Windows PC, iPhone 13, Mac" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest flex items-center gap-2">
                                <Globe class="w-4 h-4 text-emerald-500" /> Browser <span class="text-red-500">*</span>
                            </label>
                            <input v-model="form.browser" type="text" placeholder="e.g. Chrome, Safari, Firefox" class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- Step 3: Issue Details -->
                <div v-else-if="currentStep === 3" class="space-y-6 max-w-2xl mx-auto">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 mb-1">Issue Details</h2>
                        <p class="text-sm text-slate-600 mb-6">Describe exactly what went wrong and provide any visual evidence.</p>
                    </div>

                    <div class="space-y-4">
                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest">Error Message / Description <span class="text-red-500">*</span></label>
                            <textarea v-model="form.error_message" rows="5" placeholder="Please describe the steps to reproduce the issue, and what exactly happened..." class="w-full bg-white border border-slate-300 text-slate-900 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all resize-none"></textarea>
                        </div>

                        <div class="space-y-1.5">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-widest flex justify-between">
                                <span>Screenshot / Attachment (Optional)</span>
                                <span class="text-slate-500 normal-case font-normal text-[10px]">Max 5MB (JPG/PNG)</span>
                            </label>
                            <div class="w-full border-2 border-dashed border-slate-300 rounded-xl p-4 flex flex-col items-center justify-center bg-slate-50 hover:bg-slate-100 transition-colors relative group">
                                <input type="file" @change="handleFileUpload" accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" />
                                <div v-if="!attachmentPreview" class="flex flex-col items-center">
                                    <UploadCloud class="w-8 h-8 text-blue-500 mb-2 group-hover:scale-110 transition-transform" />
                                    <p class="text-sm font-medium text-slate-600">Click or drag an image here</p>
                                </div>
                                <div v-else class="relative w-full">
                                    <img :src="attachmentPreview" class="max-h-48 mx-auto rounded-lg shadow-sm border border-slate-200" />
                                    <button @click.prevent="clearFile" class="absolute top-2 right-2 bg-red-500 text-white p-1.5 rounded-full hover:bg-red-600 shadow-md z-20">
                                        <X class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 4: Final Review -->
                <div v-else-if="currentStep === 4" class="space-y-6 max-w-3xl mx-auto">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <FileSearch class="w-8 h-8" />
                        </div>
                        <h2 class="text-2xl font-black text-slate-900 mb-2 tracking-tight">Review Your Report</h2>
                        <p class="text-sm text-slate-600 mb-8">Please confirm the details below before sending it to our administrators.</p>
                    </div>

                    <div class="bg-slate-50 border border-slate-200 rounded-2xl p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Category</p>
                            <p class="text-slate-900 font-medium capitalize">{{ form.category.replace('_', ' ') }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Page Encountered</p>
                            <p class="text-slate-900 font-medium">{{ form.page }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Device Specs</p>
                            <p class="text-slate-900 font-medium">{{ form.device }}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Browser Info</p>
                            <p class="text-slate-900 font-medium">{{ form.browser }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-1">Issue Description</p>
                            <div class="bg-white p-4 rounded-xl text-slate-700 text-sm whitespace-pre-line border border-slate-200 shadow-sm">
                                {{ form.error_message }}
                            </div>
                        </div>
                        <div v-if="attachmentPreview" class="md:col-span-2">
                            <p class="text-[10px] text-slate-500 font-bold uppercase tracking-widest mb-2">Attachment Preview</p>
                            <img :src="attachmentPreview" class="max-h-40 rounded-lg border border-slate-200 shadow-sm" />
                        </div>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Wizard Navigation Footer -->
        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
            <button 
                @click="prevStep" 
                :class="['px-6 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2', currentStep === 1 ? 'opacity-0 pointer-events-none' : 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-100']"
            >
                <ArrowLeft class="w-4 h-4" /> Back
            </button>

            <button 
                v-if="currentStep < 4" 
                @click="nextStep" 
                :disabled="!isStepValid"
                class="px-8 py-2.5 rounded-xl font-bold transition-all flex items-center gap-2 bg-blue-600 hover:bg-blue-500 text-white disabled:opacity-50 disabled:cursor-not-allowed shadow-md shadow-blue-500/20"
            >
                Next <ArrowRight class="w-4 h-4" />
            </button>

            <button 
                v-else 
                @click="submitReport" 
                :disabled="isSubmitting"
                class="px-8 py-2.5 rounded-xl font-black transition-all flex items-center gap-2 bg-emerald-500 hover:bg-emerald-400 text-white disabled:opacity-50 shadow-md shadow-emerald-500/20"
            >
                <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                <Send v-else class="w-4 h-4" />
                {{ isSubmitting ? 'Sending...' : 'Submit Report' }}
            </button>
        </div>
      </div>

      <!-- MY REPORTS TAB -->
      <div v-if="activeTab === 'history'" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden p-6 min-h-[500px]">
        <h2 class="text-xl font-black text-slate-900 mb-6">Submission History</h2>
        
        <div v-if="isLoadingReports" class="flex justify-center items-center py-20">
            <Loader2 class="w-8 h-8 animate-spin text-blue-600" />
        </div>

        <div v-else-if="reports.length === 0" class="text-center py-20">
            <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <FileX class="w-10 h-10 text-slate-400" />
            </div>
            <h3 class="text-lg font-bold text-slate-900 mb-1">No Reports Found</h3>
            <p class="text-slate-500 text-sm">You haven't submitted any technical reports yet.</p>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div v-for="report in reports" :key="report.id" class="bg-white border border-slate-200 rounded-xl p-5 hover:border-blue-400 hover:shadow-md transition-all">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <Badge variant="outline" :class="[
                            'text-[9px] font-black uppercase tracking-widest px-2 py-0.5 border-none',
                            report.status === 'reviewed' ? 'bg-emerald-100 text-emerald-600' : 'bg-amber-100 text-amber-600'
                        ]">
                            {{ report.status }}
                        </Badge>
                        <h3 class="text-slate-900 font-bold mt-2 capitalize">{{ report.category.replace('_', ' ') }}</h3>
                    </div>
                    <span class="text-xs text-slate-400 font-mono">{{ formatDate(report.created_at) }}</span>
                </div>
                
                <p class="text-sm text-slate-600 line-clamp-2 mb-4">{{ report.error_message }}</p>
                
                <div class="flex items-center justify-between text-xs border-t border-slate-100 pt-3">
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <MapPin class="w-3.5 h-3.5 text-blue-500" /> {{ report.page }}
                    </div>
                    <div v-if="report.attachment" class="flex items-center gap-1 text-blue-600 font-medium cursor-pointer hover:underline" @click="viewImage(report.attachment)">
                        <Image class="w-3.5 h-3.5" /> View Proof
                    </div>
                </div>
            </div>
        </div>
      </div>

    </div>

    <!-- Image Viewer Modal -->
    <Dialog :open="!!selectedImage" @update:open="selectedImage = null">
      <DialogContent class="bg-white border-slate-200 shadow-2xl max-w-4xl p-2 sm:rounded-2xl flex justify-center items-center">
        <img :src="selectedImage" class="max-h-[80vh] object-contain rounded-xl" />
      </DialogContent>
    </Dialog>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { 
    Wrench, Check, ArrowRight, ArrowLeft, Send, 
    Smartphone, Globe, UploadCloud, X, FileSearch, 
    Loader2, FileX, MapPin, Image, ChevronDown 
} from 'lucide-vue-next'
import { Dialog, DialogContent } from '@/components/ui/dialog'
import { Badge } from '@/components/ui/badge'
import { toast } from 'vue-sonner'
import api from '@/utils/axios'
import echo from '@/utils/websocket'

const activeTab = ref('create')
const currentStep = ref(1)
const steps = ['Issue Type', 'Environment', 'Details', 'Review']

const isSubmitting = ref(false)
const isLoadingReports = ref(false)
const reports = ref([])
const selectedImage = ref(null)
const userId = ref(null)

const form = ref({
    category: '',
    page: '',
    device: '',
    browser: '',
    error_message: '',
    attachment: null
})

const attachmentPreview = ref(null)

onMounted(() => {
    const data = localStorage.getItem('user_data')
    if (data) {
        const user = JSON.parse(data)
        userId.value = user.id
        setupWebsockets()
    }

    form.value.device = getOS()
    form.value.browser = getBrowser()
    fetchMyReports()
})

onUnmounted(() => {
    if (echo && userId.value) {
        echo.leave(`user.${userId.value}.technical_reports`)
    }
})

const setupWebsockets = () => {
    if (!userId.value) return

    echo.private(`user.${userId.value}.technical_reports`)
        .listen('.report.updated', (e) => {
            const index = reports.value.findIndex(r => r.id === e.report.id)
            if (index !== -1) {
                reports.value[index] = e.report
                toast.success('Report Updated', { 
                    description: `Your technical report regarding "${e.report.category.replace('_', ' ')}" has been reviewed.`
                })
            }
        })
}

const isStepValid = computed(() => {
    if (currentStep.value === 1) return form.value.category !== '' && form.value.page !== ''
    if (currentStep.value === 2) return form.value.device !== '' && form.value.browser !== ''
    if (currentStep.value === 3) return form.value.error_message.trim() !== ''
    return true
})

const nextStep = () => {
    if (isStepValid.value && currentStep.value < 4) {
        currentStep.value++
    }
}

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--
    }
}

const handleFileUpload = (e) => {
    const file = e.target.files[0]
    if (!file) return

    if (file.size > 5 * 1024 * 1024) {
        toast.error('File too large', { description: 'Please select an image under 5MB.' })
        return
    }

    form.value.attachment = file
    const reader = new FileReader()
    reader.onload = (e) => {
        attachmentPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
}

const clearFile = () => {
    form.value.attachment = null
    attachmentPreview.value = null
}

const submitReport = async () => {
    isSubmitting.value = true
    try {
        const formData = new FormData()
        formData.append('category', form.value.category)
        formData.append('page', form.value.page)
        formData.append('device', form.value.device)
        formData.append('browser', form.value.browser)
        formData.append('error_message', form.value.error_message)
        
        if (form.value.attachment) {
            formData.append('attachment', form.value.attachment)
        }

        const response = await api.post('/distributor/technical-reports', formData, {
            headers: { 'Content-Type': 'multipart/form-data' }
        })

        if (response.data.success) {
            toast.success('Report Submitted', { description: 'Thank you! Our administrators will review it shortly.' })
            
            form.value = {
                category: '', page: '', device: getOS(), browser: getBrowser(), error_message: '', attachment: null
            }
            attachmentPreview.value = null
            currentStep.value = 1
            
            fetchMyReports()
            activeTab.value = 'history'
        }
    } catch (error) {
        toast.error('Submission Failed', { description: error.response?.data?.message || 'Something went wrong.' })
    } finally {
        isSubmitting.value = false
    }
}

const fetchMyReports = async () => {
    isLoadingReports.value = true
    try {
        const response = await api.get('/distributor/technical-reports')
        if (response.data.success) {
            reports.value = response.data.data
        }
    } catch (error) {
        console.error("Failed to fetch reports:", error)
    } finally {
        isLoadingReports.value = false
    }
}

const viewImage = (url) => {
    selectedImage.value = url
}

const formatDate = (dateString) => {
    if (!dateString) return ''
    return new Date(dateString).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })
}

const getOS = () => {
    let userAgent = window.navigator.userAgent, platform = window.navigator.platform, macosPlatforms = ['Macintosh', 'MacIntel', 'MacPPC', 'Mac68K'], windowsPlatforms = ['Win32', 'Win64', 'Windows', 'WinCE'], iosPlatforms = ['iPhone', 'iPad', 'iPod'], os = null;
    if (macosPlatforms.indexOf(platform) !== -1) os = 'Mac OS';
    else if (iosPlatforms.indexOf(platform) !== -1) os = 'iOS';
    else if (windowsPlatforms.indexOf(platform) !== -1) os = 'Windows';
    else if (/Android/.test(userAgent)) os = 'Android';
    else if (!os && /Linux/.test(platform)) os = 'Linux';
    return os || 'Unknown Device';
}

const getBrowser = () => {
    let userAgent = navigator.userAgent, match = userAgent.match(/(opera|chrome|safari|firefox|msie|trident(?=\/))\/?\s*(\d+)/i) || [];
    if (/trident/i.test(match[1])) return 'IE';
    if (match[1] === 'Chrome') {
        let temp = userAgent.match(/\b(OPR|Edge)\/(\d+)/);
        if (temp != null) return temp.slice(1).join(' ').replace('OPR', 'Opera');
    }
    match = match[2] ? [match[1], match[2]] : [navigator.appName, navigator.appVersion, '-?'];
    let temp;
    if ((temp = userAgent.match(/version\/(\d+)/i)) != null) match.splice(1, 1, temp[1]);
    return match.join(' ');
}
</script>

<style scoped>
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.3s ease;
}
.fade-slide-enter-from {
  opacity: 0;
  transform: translateX(20px);
}
.fade-slide-leave-to {
  opacity: 0;
  transform: translateX(-20px);
}
</style>