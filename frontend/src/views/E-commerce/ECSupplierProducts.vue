<template>
  <div class="p-4 md:p-6 min-h-screen  text-slate-100 relative selection:bg-blue-500/30">

    <div class="fixed top-0 left-1/2 -translate-x-1/2 w-full max-w-3xl h-[500px] bg-blue-600/10 blur-[120px] rounded-full pointer-events-none opacity-50 z-0"></div>

    <div class="relative z-10 max-w-7xl mx-auto space-y-6 md:space-y-8">
      
      <div class="bg-slate-900/60 backdrop-blur-xl border border-slate-800/80 p-6 md:p-8 rounded-3xl shadow-2xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-64 h-64 bg-indigo-500/10 rounded-full blur-[80px] pointer-events-none"></div>
        
        <div class="flex items-center gap-5 z-10">
          <Button 
            variant="outline" 
            @click="$router.back()" 
            class="h-10 px-4 md:px-5 rounded-full border-slate-700 bg-slate-800/80 hover:bg-slate-700 hover:text-white text-slate-300 transition-all duration-300 shadow-md flex items-center gap-2"
          >
            <i class="fas fa-arrow-left text-sm md:text-base"></i>
            <span class="font-medium">Back</span>
          </Button>

          <div>
            <div class="flex items-center gap-3 mb-1">
              <Badge variant="outline" class="bg-blue-500/10 text-blue-400 border-blue-500/30 tracking-wide uppercase text-[10px] font-bold px-2 py-0.5 rounded-full">
                Partner Catalog
              </Badge>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-white bg-clip-text text-transparent bg-gradient-to-r from-white to-slate-400">
              {{ supplierName }}
            </h1>
          </div>
        </div>

        <div class="w-full md:w-auto relative z-10">
           <div class="relative group">
               <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 group-focus-within:text-blue-400 transition-colors"></i>
               <Input 
                  v-model="searchQuery" 
                  placeholder="Search materials, SKU, category..." 
                  class="pl-11 h-12 w-full md:w-80 bg-slate-950/50 border-slate-700/80 text-slate-100 placeholder:text-slate-500 rounded-2xl focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all shadow-inner"
               />
            </div>
        </div>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-32 space-y-4">
         <div class="relative">
            <div class="absolute inset-0 rounded-full blur-md bg-blue-500/40 animate-pulse"></div>
            <div class="animate-spin relative rounded-full h-14 w-14 border-4 border-slate-800 border-t-blue-500"></div>
         </div>
         <p class="text-slate-400 font-medium tracking-wide animate-pulse">Loading catalog...</p>
      </div>

      <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 xl:gap-8">
        <Card 
          v-for="product in filteredProducts" 
          :key="product.id" 
          class="group bg-slate-900/40 backdrop-blur-sm border-slate-800 hover:border-blue-500/50 hover:shadow-[0_8px_30px_rgb(59,130,246,0.12)] transition-all duration-500 rounded-2xl overflow-hidden flex flex-col"
        >
          <div class="h-56 relative bg-slate-800/50 overflow-hidden">
             <div class="absolute inset-0 bg-gradient-to-t from-slate-900 via-slate-900/20 to-transparent z-10"></div>
             
             <img 
               v-if="product.image_url || product.image_path || product.image" 
               :src="formatImage(product.image_url || product.image_path || product.image)" 
               alt="Product Image" 
               class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-out"
             />
             <div v-else class="absolute inset-0 flex items-center justify-center bg-gradient-to-br from-slate-800 to-slate-900">
                <i class="fas fa-image text-5xl text-slate-700 shadow-sm"></i>
             </div>
             
             <div class="absolute top-4 right-4 z-20">
                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold px-3 py-1.5 rounded-lg shadow-lg shadow-blue-900/50 text-sm tracking-wide border border-blue-400/30">
                   {{ formatCurrency(product.price || product.unit_price) }}
                </div>
             </div>

             <div class="absolute bottom-4 left-4 z-20 flex flex-wrap gap-2 pr-4">
               <Badge class="bg-slate-950/80 hover:bg-slate-900 text-slate-200 border border-slate-700 backdrop-blur-md font-medium text-[10px] md:text-xs">
                  {{ product.category || 'General' }}
               </Badge>
               <Badge v-if="product.type" variant="outline" class="bg-blue-950/60 text-blue-300 border-blue-800/50 backdrop-blur-md font-medium text-[10px] md:text-xs truncate max-w-[120px]">
                  {{ product.type }}
               </Badge>
             </div>
          </div>

          <CardContent class="pt-6 px-6 pb-6 flex-1 flex flex-col z-20 bg-slate-900/20">
             <div class="mb-3">
                <h3 class="text-xl font-bold text-slate-100 group-hover:text-blue-400 transition-colors duration-300 line-clamp-1" :title="product.name || product.material_name">
                  {{ product.name || product.material_name }}
                </h3>
             </div>
             
             <p class="text-sm text-slate-400 line-clamp-2 mb-6 leading-relaxed flex-1">
                {{ product.description || 'No detailed description provided for this product. Contact the supplier for more information.' }}
             </p>

             <div class="grid grid-cols-3 gap-2 mt-auto">
                <div class="bg-slate-950/50 rounded-xl p-2.5 flex flex-col items-center justify-center text-center border border-slate-800/80 group-hover:border-slate-700 transition-colors duration-300">
                   <span class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-0.5">Size</span>
                   <span class="text-xs font-bold text-slate-200 truncate w-full" :title="product.size || 'N/A'">{{ product.size || 'N/A' }}</span>
                </div>
                <div class="bg-slate-950/50 rounded-xl p-2.5 flex flex-col items-center justify-center text-center border border-slate-800/80 group-hover:border-slate-700 transition-colors duration-300">
                   <span class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-0.5">Min Qty</span>
                   <span class="text-xs font-bold text-slate-200 truncate w-full">{{ product.min_order || 1 }}</span>
                </div>
                <div class="bg-slate-950/50 rounded-xl p-2.5 flex flex-col items-center justify-center text-center border border-slate-800/80 group-hover:border-slate-700 transition-colors duration-300">
                   <span class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold mb-0.5">SKU</span>
                   <span class="text-xs font-bold text-slate-200 truncate w-full" :title="product.sku_code || 'N/A'">{{ product.sku_code || 'N/A' }}</span>
                </div>
             </div>
          </CardContent>
        </Card>
      </div>

      <div v-else class="flex flex-col items-center justify-center py-24 bg-slate-900/20 backdrop-blur-sm rounded-3xl border border-slate-800 border-dashed transition-all duration-300 hover:border-slate-700 hover:bg-slate-900/40">
         <div class="h-24 w-24 bg-slate-800/80 rounded-full flex items-center justify-center mb-6 text-slate-500 shadow-inner">
             <i class="fas fa-box-open text-4xl"></i>
         </div>
         <h2 class="text-2xl font-bold text-slate-200 mb-2">No Products Found</h2>
         <p class="text-slate-500 text-center max-w-md leading-relaxed">
            We couldn't find any available products matching your search, or this supplier hasn't populated their catalog yet.
         </p>
         <Button v-if="searchQuery" variant="outline" @click="searchQuery = ''" class="mt-6 border-slate-700 text-slate-300 hover:bg-slate-800">
            Clear Search
         </Button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '@/utils/axios'; 
import { Card, CardContent } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';

const route = useRoute();
const router = useRouter();
const supplierId = route.params.id; 

const products = ref([]);
const supplierName = ref('Supplier');
const loading = ref(true);
const searchQuery = ref('');

const fetchProducts = async () => {
    loading.value = true;
    try {
        const response = await api.get(`/operation-distributor/partner-suppliers/${supplierId}/products`);
        
        if (response.data.success) {
            products.value = response.data.data;
            supplierName.value = response.data.supplier_name;
        }
    } catch (error) {
        console.error("Error fetching products:", error);
    } finally {
        loading.value = false;
    }
};

const filteredProducts = computed(() => {
  if (!searchQuery.value) return products.value;
  const query = searchQuery.value.toLowerCase();
  return products.value.filter(p => 
    (p.name && p.name.toLowerCase().includes(query)) || 
    (p.material_name && p.material_name.toLowerCase().includes(query)) ||
    (p.category && p.category.toLowerCase().includes(query)) ||
    (p.type && p.type.toLowerCase().includes(query)) ||
    (p.sku_code && p.sku_code.toLowerCase().includes(query))
  );
});

const formatCurrency = (value) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(value || 0);
};

const formatImage = (path) => {
    if (!path) return '';
    if (path.startsWith('http')) return path;
    const baseUrl = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000';
    return `${baseUrl}/storage/${path}`;
};

onMounted(() => {
    if (supplierId) {
        fetchProducts();
    } else {
        router.back();
    }
});
</script>