<template>
  <div class="client-dashboard min-h-screen p-4 md:p-6 lg:p-10 text-slate-200 font-sans antialiased overflow-x-hidden relative">
    
    <div v-if="isProcessingPayment" class="fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/80 backdrop-blur-sm">
      <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-sky-500 mb-4"></div>
      <p class="text-white font-bold text-lg">Processing your subscription...</p>
    </div>

    <!-- History Modal -->
    <div v-if="showHistoryModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/80 backdrop-blur-sm p-4">
      <div class="bg-slate-900 border border-slate-700 shadow-2xl rounded-3xl w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-200">
        <!-- Modal Header -->
        <div class="p-6 border-b border-slate-800 flex justify-between items-center bg-slate-800/30">
          <h2 class="text-xl font-bold text-white flex items-center gap-2">
            <History class="w-5 h-5 text-sky-400" />
            Billing & Subscription History
          </h2>
          <button @click="showHistoryModal = false" class="text-slate-400 hover:text-white transition-colors bg-slate-800 hover:bg-slate-700 p-2 rounded-full">
            <X class="w-5 h-5" />
          </button>
        </div>
        
        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto custom-scrollbar">
          <div v-if="isLoadingHistory" class="py-10 text-center text-slate-400 flex flex-col items-center justify-center gap-3">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-sky-500"></div>
            <span>Loading history...</span>
          </div>
          
          <div v-else-if="!subscriptionHistory.length" class="py-10 text-center text-slate-500">
            No past subscriptions found on your account.
          </div>
          
          <div v-else class="overflow-x-auto rounded-xl border border-slate-700/50">
            <Table>
              <TableHeader class="bg-slate-800/80">
                <TableRow class="border-slate-700/50 hover:bg-transparent">
                  <TableHead class="text-slate-300 py-4 font-semibold w-[200px]">Reference No.</TableHead>
                  <TableHead class="text-slate-300 py-4 font-semibold">Plan</TableHead>
                  <TableHead class="text-slate-300 py-4 font-semibold">Amount</TableHead>
                  <TableHead class="text-slate-300 py-4 font-semibold">Start Date</TableHead>
                  <TableHead class="text-slate-300 py-4 font-semibold">End Date</TableHead>
                  <TableHead class="text-slate-300 py-4 font-semibold text-right">Status</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                <TableRow 
                  v-for="sub in subscriptionHistory" 
                  :key="sub.id" 
                  class="border-slate-700/50 hover:bg-slate-800/30 transition-colors"
                >
                  <TableCell class="font-mono text-xs text-slate-400">
                    {{ sub.reference_number }}
                  </TableCell>
                  <TableCell class="font-medium text-white capitalize">
                    {{ sub.plan_name.replace('_', ' ') }}
                  </TableCell>
                  <TableCell class="text-slate-300">
                    ₱{{ parseFloat(sub.amount).toLocaleString('en-US', { minimumFractionDigits: 2 }) }}
                  </TableCell>
                  <TableCell class="text-slate-400 text-sm">
                    {{ formatDate(sub.start_date) }}
                  </TableCell>
                  <TableCell class="text-slate-400 text-sm">
                    {{ formatDate(sub.end_date) }}
                  </TableCell>
                  <TableCell class="text-right">
                    <Badge :class="getSubStatusClass(sub.status)">
                      {{ sub.status }}
                    </Badge>
                  </TableCell>
                </TableRow>
              </TableBody>
            </Table>
          </div>
        </div>
      </div>
    </div>

    <section class="hero-section relative overflow-hidden rounded-3xl border border-sky-500/10 bg-gradient-to-br from-slate-800/80 to-slate-900/90 p-6 md:p-10 mb-10 shadow-2xl">
      <div class="hero-gradient absolute inset-0 bg-[radial-gradient(circle_at_70%_50%,rgba(56,189,248,0.15),transparent_70%)]"></div>
      
      <div class="relative z-10 flex flex-col md:flex-row items-center justify-between gap-8">
        <div class="hero-left text-center md:text-left">
          <Badge class="bg-sky-500/20 text-sky-400 border-sky-500/30 mb-4 px-3 py-1">Welcome back, Client</Badge>
          <h1 class="text-3xl md:text-5xl font-extrabold tracking-tight leading-tight mb-4">
            <span class="block">Transform Your Space</span>
            <span class="block bg-gradient-to-r from-sky-400 via-sky-500 to-sky-700 bg-clip-text text-transparent">With Perfect Colors</span>
          </h1>
          <p class="text-slate-400 text-lg mb-6 max-w-xl">Visualize, select, and track your paint projects with real-time updates across the CaviteGo network.</p>
          
          <div class="flex flex-wrap justify-center md:justify-start items-center gap-4">
            <Button @click="goToShop" class="bg-sky-500 hover:bg-sky-600 text-white rounded-full px-6 py-5 shadow-lg shadow-sky-500/20 border-0 font-semibold text-base transition-all">
              <ShoppingCart class="w-5 h-5 mr-2" />
              Visit E-Commerce Shop
            </Button>
          </div>
        </div>

        <div class="hero-right flex justify-center items-center">
          <div class="color-wheel-animation relative w-32 h-32 md:w-48 md:h-48">
            <div class="color-circle absolute inset-0 rounded-full opacity-30 animate-[spin_20s_linear_infinite] bg-[conic-gradient(from_0deg,#38bdf8,#0ea5e9,#0284c7,#38bdf8)]"></div>
            <div class="color-circle-2 absolute inset-[10%] rounded-full animate-[spin_15s_linear_infinite_reverse] bg-[conic-gradient(from_180deg,#c084fc,#a855f7,#7c3aed,#c084fc)]"></div>
            <div class="color-circle-3 absolute inset-[20%] rounded-full animate-[spin_10s_linear_infinite] bg-[conic-gradient(from_90deg,#10b981,#059669,#047857,#10b981)]"></div>
          </div>
        </div>
      </div>
    </section>

    <!-- Pricing/Current Subscription Section -->
    <section class="mb-12">
      <div class="text-center mb-8">
        <h2 class="text-3xl md:text-4xl font-black tracking-tighter bg-gradient-to-r from-purple-400 via-pink-400 to-rose-400 bg-clip-text text-transparent inline-flex items-center gap-3">
          <Sparkles class="w-8 h-8 text-pink-400" />
          Color Mixing Lab Pro
        </h2>
        <p class="text-slate-400 mt-3 max-w-2xl mx-auto">Unlock advanced virtual color mixing, precision harmony palettes, and deep influence analysis to visualize your perfect blend before you buy.</p>
        
        <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
          <p v-if="currentSubscription" class="inline-block bg-slate-800 text-sky-400 font-bold px-4 py-2 rounded-full border border-slate-700">
            Current Plan: <span class="uppercase tracking-wider">{{ currentSubscription.plan_name.replace('_', ' ') }}</span>
            <span class="text-xs text-slate-400 font-normal ml-2">Valid until {{ formatDate(currentSubscription.end_date) }}</span>
          </p>
          
          <Button @click="showHistoryModal = true" variant="outline" class="bg-slate-800/50 border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700 rounded-full h-10 px-4 transition-all">
            <History class="w-4 h-4 mr-2 text-sky-400" />
            Billing History
          </Button>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card class="bg-slate-900/60 border-slate-700/50 backdrop-blur-xl relative overflow-hidden group hover:border-slate-500 transition-all" :class="{'ring-2 ring-slate-400': isPlanActive('starter')}">
          <CardHeader>
            <CardTitle class="text-xl text-slate-200">Starter</CardTitle>
            <div class="text-3xl font-bold text-white mt-2">Free<span class="text-sm text-slate-500 font-normal"> / 30 days</span></div>
          </CardHeader>
          <CardContent class="space-y-4">
            <ul class="space-y-2 text-sm text-slate-400">
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-emerald-400" /> 3-Color Weighted Blending</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-emerald-400" /> Real-time HEX & RGB Output</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-emerald-400" /> Save up to 5 Colors</li>
            </ul>
            <Button 
              @click="handleSubscribe('starter')" 
              :disabled="hasSubscription"
              class="w-full mt-6 border transition-all"
              :class="isPlanActive('starter') ? 'bg-slate-700 text-white cursor-default' : 'bg-slate-800 hover:bg-slate-700 text-white border-slate-600'">
              {{ isPlanActive('starter') ? 'Current Plan' : (hasSubscription ? 'Unavailable' : 'Start Trial') }}
            </Button>
          </CardContent>
        </Card>

        <Card class="bg-slate-900/60 border-slate-700/50 backdrop-blur-xl relative overflow-hidden group hover:border-purple-500/50 transition-all" :class="{'ring-2 ring-purple-500': isPlanActive('monthly')}">
          <CardHeader>
            <CardTitle class="text-xl text-purple-300">Monthly</CardTitle>
            <div class="text-3xl font-bold text-white mt-2">₱49<span class="text-sm text-slate-500 font-normal"> / month</span></div>
          </CardHeader>
          <CardContent class="space-y-4">
            <ul class="space-y-2 text-sm text-slate-400">
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-purple-400" /> All Starter Features</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-purple-400" /> Harmony Palettes (Mono, Analog, Comp)</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-purple-400" /> Color Influence Progress Analysis</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-purple-400" /> Save up to 100 Colors</li>
            </ul>
            <Button 
              @click="handleSubscribe('monthly')"
              :disabled="isPlanActive('monthly') || isPlanHigherThan('monthly')"
              class="w-full mt-6 border transition-all"
              :class="isPlanActive('monthly') ? 'bg-purple-600 text-white cursor-default' : 'bg-purple-600/20 hover:bg-purple-600/30 text-purple-300 border-purple-500/30'">
              {{ isPlanActive('monthly') ? 'Current Plan' : (isPlanHigherThan('monthly') ? 'Included in Plan' : (isPlanActive('starter') ? 'Upgrade to Monthly' : 'Subscribe Monthly')) }}
            </Button>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-b from-slate-800 to-slate-900 border-sky-500/50 backdrop-blur-xl relative overflow-hidden shadow-[0_0_30px_rgba(14,165,233,0.15)] transform md:-translate-y-2 z-10" :class="{'ring-2 ring-sky-400': isPlanActive('half_year')}">
          <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-sky-400 to-blue-600"></div>
          <Badge class="absolute top-4 right-4 bg-sky-500 text-white border-0">Most Popular</Badge>
          <CardHeader>
            <CardTitle class="text-xl text-sky-400">Half-Year</CardTitle>
            <div class="text-3xl font-bold text-white mt-2">₱149<span class="text-sm text-slate-500 font-normal"> / 6 mos</span></div>
            <p class="text-xs text-sky-400/80 mt-1">Save roughly ₱145 annually!</p>
          </CardHeader>
          <CardContent class="space-y-4">
            <ul class="space-y-2 text-sm text-slate-300">
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-sky-400" /> All Monthly Features</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-sky-400" /> Luminance & Readability Check</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-sky-400" /> Temperature & Color Family Data</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-sky-400" /> Unlimited Color Saves</li>
            </ul>
            <Button 
              @click="handleSubscribe('half_year')"
              :disabled="isPlanActive('half_year') || isPlanHigherThan('half_year')"
              class="w-full mt-6 shadow-lg border-0 transition-all"
              :class="isPlanActive('half_year') ? 'bg-sky-600 text-white cursor-default' : 'bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-400 hover:to-blue-500 text-white'">
              {{ isPlanActive('half_year') ? 'Current Plan' : (isPlanHigherThan('half_year') ? 'Included in Plan' : 'Upgrade to Half-Year') }}
            </Button>
          </CardContent>
        </Card>

        <Card class="bg-slate-900/60 border-slate-700/50 backdrop-blur-xl relative overflow-hidden group hover:border-amber-500/50 transition-all" :class="{'ring-2 ring-amber-500': isPlanActive('annual')}">
          <CardHeader>
            <CardTitle class="text-xl text-amber-400">Annual</CardTitle>
            <div class="text-3xl font-bold text-white mt-2">₱349<span class="text-sm text-slate-500 font-normal"> / 12 mos</span></div>
            <p class="text-xs text-amber-500/60 mt-1">Best value for long-term projects</p>
          </CardHeader>
          <CardContent class="space-y-4">
            <ul class="space-y-2 text-sm text-slate-400">
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-amber-400" /> All 6-Month Features</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-amber-400" /> Interactive Orbit Visualizer</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-amber-400" /> Advanced Mixing Insights & Tips</li>
              <li class="flex items-center gap-2"><Check class="w-4 h-4 text-amber-400" /> Unlimited Color Saves</li>
            </ul>
            <Button 
              @click="handleSubscribe('annual')"
              :disabled="isPlanActive('annual')"
              class="w-full mt-6 border transition-all"
              :class="isPlanActive('annual') ? 'bg-amber-600 text-white cursor-default' : 'bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border-amber-500/30'">
              {{ isPlanActive('annual') ? 'Current Plan' : 'Upgrade to Annual' }}
            </Button>
          </CardContent>
        </Card>
      </div>
    </section>

    <!-- Marketing/Info Section -->
    <section class="border-t border-slate-800 pt-12 pb-6">
      <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-10">
        <div class="max-w-3xl">
          <p class="text-[10px] font-black uppercase tracking-[0.4em] text-cyan-500 mb-2">Your Dashboard</p>
          <h2 class="text-3xl md:text-5xl font-bold text-white mb-4">Everything You Need for Your Project</h2>
          <p class="text-slate-400 leading-relaxed border-l-2 border-cyan-500 pl-4">
            Experience a seamless journey from choosing the perfect shade to having the paint delivered to your home. Everything is designed to make your renovation project as easy as possible.
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <div class="bg-slate-900/40 rounded-3xl p-8 relative overflow-hidden group border border-slate-800 hover:border-blue-500/30 transition-colors">
          <div class="relative z-10">
            <div class="w-12 h-12 bg-blue-500/10 rounded-xl flex items-center justify-center mb-6 border border-blue-500/20">
              <Truck class="w-6 h-6 text-blue-400 group-hover:scale-110 transition-transform" />
            </div>
            <span class="text-[10px] font-black text-slate-500 tracking-widest uppercase">Live Tracking</span>
            <h4 class="text-xl font-bold mt-1 text-white">Track Your Deliveries</h4>
            <p class="text-sm text-slate-400 mt-3 leading-relaxed">Watch your paint and materials move from the store directly to your doorstep in real-time, so you know exactly when to expect them.</p>
          </div>
          <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-blue-600/10 rounded-full blur-[60px] group-hover:bg-blue-600/20 transition-all"></div>
        </div>

        <div class="bg-slate-900/40 rounded-3xl p-8 relative overflow-hidden group border border-slate-800 hover:border-purple-500/30 transition-colors">
          <div class="relative z-10">
            <div class="w-12 h-12 bg-purple-500/10 rounded-xl flex items-center justify-center mb-6 border border-purple-500/20">
              <MessageSquare class="w-6 h-6 text-purple-400 group-hover:scale-110 transition-transform" />
            </div>
            <span class="text-[10px] font-black text-slate-500 tracking-widest uppercase">Direct Comms</span>
            <h4 class="text-xl font-bold mt-1 text-white">Talk to Experts</h4>
            <p class="text-sm text-slate-400 mt-3 leading-relaxed">Chat directly with professional painters or service provider. Keep all your project questions, quotes, and history safely stored in one place.</p>
          </div>
          <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-purple-600/10 rounded-full blur-[60px] group-hover:bg-purple-600/20 transition-all"></div>
        </div>

        <div class="bg-slate-900/40 rounded-3xl p-8 relative overflow-hidden group border border-slate-800 hover:border-cyan-500/30 transition-colors">
          <div class="relative z-10">
            <div class="w-12 h-12 bg-cyan-500/10 rounded-xl flex items-center justify-center mb-6 border border-cyan-500/20">
              <LineChart class="w-6 h-6 text-cyan-400 group-hover:scale-110 transition-transform" />
            </div>
            <span class="text-[10px] font-black text-slate-500 tracking-widest uppercase">Smart Insights</span>
            <h4 class="text-xl font-bold mt-1 text-white">Get Color Inspiration</h4>
            <p class="text-sm text-slate-400 mt-3 leading-relaxed">Not sure what to pick? Discover trending colors, popular palettes, and design ideas selected by other homeowners in your area.</p>
          </div>
          <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-cyan-600/10 rounded-full blur-[60px] group-hover:bg-cyan-600/20 transition-all"></div>
        </div>

        <div class="bg-slate-900/40 rounded-3xl p-8 relative overflow-hidden group border border-slate-800 hover:border-emerald-500/30 transition-colors">
          <div class="relative z-10">
            <div class="w-12 h-12 bg-emerald-500/10 rounded-xl flex items-center justify-center mb-6 border border-emerald-500/20">
              <Smartphone class="w-6 h-6 text-emerald-400 group-hover:scale-110 transition-transform" />
            </div>
            <span class="text-[10px] font-black text-slate-500 tracking-widest uppercase">Anywhere Access</span>
            <h4 class="text-xl font-bold mt-1 text-white">Mobile Companion App</h4>
            <p class="text-sm text-slate-400 mt-3 leading-relaxed">Download our app to approve service quotes, track your orders, and play with the Color Lab directly from your smartphone.</p>
          </div>
          <div class="absolute -right-12 -bottom-12 w-48 h-48 bg-emerald-600/10 rounded-full blur-[60px] group-hover:bg-emerald-600/20 transition-all"></div>
        </div>

        <div class="bg-slate-900/40 rounded-3xl p-8 relative overflow-hidden group border border-slate-800 hover:border-pink-500/30 transition-colors md:col-span-2 lg:col-span-2">
          <div class="relative z-10 flex flex-col md:flex-row md:items-center gap-6">
            <div class="flex-1">
              <div class="w-12 h-12 bg-pink-500/10 rounded-xl flex items-center justify-center mb-4 border border-pink-500/20">
                <ShoppingCart class="w-6 h-6 text-pink-400 group-hover:scale-110 transition-transform" />
              </div>
              <span class="text-[10px] font-black text-slate-500 tracking-widest uppercase">E-Commerce Ready</span>
              <h4 class="text-2xl font-bold mt-1 text-white">Integrated Paint Store</h4>
              <p class="text-sm text-slate-400 mt-3 leading-relaxed max-w-lg">
                Calculate exactly how much paint you need, check real-time store availability, and place your orders directly. Get everything required for your project without ever leaving the dashboard.
              </p>
            </div>
            <div class="mt-4 md:mt-0">
               <Button @click="goToShop" variant="outline" class="border-pink-500/30 text-pink-400 hover:bg-pink-500 hover:text-white rounded-full px-6 py-5">
                 Visit E-Commerce <ChevronRight class="w-4 h-4 ml-2" />
               </Button>
            </div>
          </div>
          <div class="absolute -left-12 -bottom-12 w-64 h-64 bg-pink-600/10 rounded-full blur-[80px] group-hover:bg-pink-600/20 transition-all"></div>
        </div>

      </div>
    </section>

  </div>
</template>

<script setup>
import { reactive, ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import api from '@/utils/axios'
import { toast } from 'vue-sonner'

// shadcn-vue Components
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Progress } from '@/components/ui/progress'
import { Button } from '@/components/ui/button'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'

// Lucide Icons
import { 
  Palette, ClipboardList, ChevronRight, 
  User, Paintbrush2, History, FileText, Info, 
  ShoppingCart, Sparkles, Check, Truck, MessageSquare, 
  LineChart, Smartphone, X
} from 'lucide-vue-next'

const router = useRouter()
const route = useRoute()

// --- SUBSCRIPTION STATE ---
const currentSubscription = ref(null)
const subscriptionHistory = ref([])
const isLoadingHistory = ref(false)
const isProcessingPayment = ref(false)
const showHistoryModal = ref(false) // NEW State for Modal

const planHierarchy = {
  'starter': 1,
  'monthly': 2,
  'half_year': 3,
  'annual': 4
};

const hasSubscription = computed(() => !!currentSubscription.value)

const isPlanActive = (planKey) => {
  return currentSubscription.value?.plan_name === planKey
}

const isPlanHigherThan = (planKey) => {
  if (!currentSubscription.value) return false;
  const currentRank = planHierarchy[currentSubscription.value.plan_name];
  const compareRank = planHierarchy[planKey];
  return currentRank > compareRank;
}

// --- INITIALIZE ---
onMounted(() => {
  if (route.query.subscription_ref) {
    verifyGcashPayment(route.query.subscription_ref)
  } else {
    fetchCurrentSubscription()
  }
  
  // Fetch Subscription History on component mount
  fetchSubscriptionHistory()
})

// --- API METHODS ---

const fetchCurrentSubscription = async () => {
  try {
    const response = await api.get('/client/subscription')
    if (response.data.success) {
      currentSubscription.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching subscription:', error)
  }
}

const fetchSubscriptionHistory = async () => {
  isLoadingHistory.value = true;
  try {
    const response = await api.get('/client/subscription/history')
    if (response.data.success) {
      subscriptionHistory.value = response.data.data
    }
  } catch (error) {
    console.error('Error fetching subscription history:', error)
  } finally {
    isLoadingHistory.value = false;
  }
}

const handleSubscribe = async (planKey) => {
  isProcessingPayment.value = true;
  try {
    const response = await api.post('/client/subscription/subscribe', { plan_name: planKey });
    
    if (response.data.success) {
      if (response.data.checkout_url) {
        toast.info('Redirecting for GCash checkout...');
        setTimeout(() => {
          window.location.href = response.data.checkout_url;
        }, 1500);
      } else {
        toast.success(response.data.message);
        await fetchCurrentSubscription();
        await fetchSubscriptionHistory(); // Refresh history table instantly for Starter plan
        isProcessingPayment.value = false;
      }
    }
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to process subscription');
    isProcessingPayment.value = false;
  }
}

const verifyGcashPayment = async (referenceNumber) => {
  isProcessingPayment.value = true;
  toast.info('Verifying GCash Payment... Please wait.');
  
  await new Promise(resolve => setTimeout(resolve, 2500)); 

  try {
    const response = await api.post('/client/subscription/verify', { reference_number: referenceNumber });
    if (response.data.success) {
      toast.success('Subscription Activated!', { description: response.data.message });
      router.replace({ query: {} }); // Strip the params
      await fetchCurrentSubscription();
      await fetchSubscriptionHistory(); // Refresh history table
    }
  } catch (error) {
    toast.error('Payment Verification Failed', { description: error.response?.data?.message || 'Payment could not be verified.' });
    router.replace({ query: {} });
    await fetchCurrentSubscription();
    await fetchSubscriptionHistory();
  } finally {
    isProcessingPayment.value = false;
  }
}

// --- Helper Methods ---

const goToShop = () => {
  router.push('/ECommerceClient/EccommerceShop')
}

const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short', day: 'numeric', year: 'numeric'
  })
}

// Helper to stylize the status badge inside the data table
const getSubStatusClass = (status) => {
  const base = 'px-2 py-0.5 uppercase text-[10px] font-bold tracking-wider border ';
  switch(status.toLowerCase()) {
    case 'active': return base + 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20';
    case 'expired': return base + 'bg-slate-500/10 text-slate-400 border-slate-500/20';
    case 'pending': return base + 'bg-amber-500/10 text-amber-400 border-amber-500/20';
    default: return base + 'bg-slate-500/10 text-slate-400 border-slate-500/20';
  }
}

</script>

<style scoped>
@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.hero-section {
  backdrop-filter: blur(10px);
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
  background: rgba(30, 41, 59, 0.5); 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
  background: rgba(56, 189, 248, 0.3); 
  border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: rgba(56, 189, 248, 0.6); 
}
</style>