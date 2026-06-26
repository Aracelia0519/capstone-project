<template>
  <div class="p-4 md:p-6 min-h-screen bg-white text-slate-900 relative selection:bg-indigo-500/30">

    <div class="relative z-10 max-w-7xl mx-auto space-y-6 md:space-y-8">
      
      <div class="bg-slate-50 border border-slate-100 p-6 md:p-8 rounded-3xl shadow-sm flex flex-col md:flex-row justify-between items-start md:items-center gap-6 relative overflow-hidden">
        
        <div class="flex items-center gap-5 z-10">
          <Button 
            variant="outline" 
            @click="$router.back()" 
            class="h-10 px-4 md:px-5 rounded-full border-slate-200 bg-white hover:bg-slate-50 text-slate-700 transition-all duration-300 shadow-sm flex items-center gap-2"
          >
            <i class="fas fa-arrow-left text-sm md:text-base"></i>
            <span class="font-medium">Back</span>
          </Button>

          <div>
            <div class="flex items-center gap-3 mb-1">
              <Badge variant="outline" class="bg-indigo-50 text-indigo-700 border-indigo-200 tracking-wide uppercase text-[10px] font-bold px-2 py-0.5 rounded-full">
                Partner Catalog
              </Badge>
            </div>
            <h1 class="text-3xl md:text-4xl font-extrabold tracking-tight text-slate-900">
              {{ supplierName }}
            </h1>
          </div>
        </div>

        <div class="w-full md:w-auto relative z-10">
           <div class="relative group">
               <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-indigo-600 transition-colors"></i>
               <Input 
                  v-model="searchQuery" 
                  placeholder="Search materials, SKU, category..." 
                  class="pl-11 h-12 w-full md:w-80 bg-white border-slate-200 text-slate-900 placeholder:text-slate-400 rounded-2xl focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 transition-all shadow-sm"
               />
            </div>
        </div>
      </div>

      <div v-if="loading" class="flex flex-col items-center justify-center py-32 space-y-4">
         <div class="animate-spin rounded-full h-14 w-14 border-4 border-slate-100 border-t-indigo-600"></div>
         <p class="text-slate-500 font-medium tracking-wide animate-pulse">Loading catalog...</p>
      </div>

      <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 xl:gap-8">
        <Card 
          v-for="product in filteredProducts" 
          :key="product.id" 
          class="group bg-white border-slate-100 hover:border-indigo-200 hover:shadow-lg transition-all duration-300 rounded-2xl overflow-hidden flex flex-col"
        >
          <div class="h-56 relative bg-slate-50 overflow-hidden">
             <img 
               v-if="product.image_url || product.image_path || product.image" 
               :src="formatImage(product.image_url || product.image_path || product.image)" 
               alt="Product Image" 
               class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out"
             />
             <div v-else class="absolute inset-0 flex items-center justify-center bg-slate-50">
                <i class="fas fa-box text-5xl text-slate-200"></i>
             </div>
             
             <div class="absolute top-4 right-4 z-20">
                <div class="bg-indigo-600 text-white font-bold px-3 py-1.5 rounded-lg shadow-md text-sm tracking-wide">
                   {{ formatCurrency(product.price || product.unit_price) }}
                </div>
             </div>

             <div class="absolute bottom-4 left-4 z-20 flex flex-wrap gap-2 pr-4">
               <Badge class="bg-white/90 text-slate-700 border-slate-200 backdrop-blur-md font-medium text-[10px] md:text-xs">
                  {{ product.category || 'General' }}
               </Badge>
             </div>
          </div>

          <CardContent class="pt-6 px-6 pb-6 flex-1 flex flex-col z-20">
             <div class="mb-3">
                <h3 class="text-xl font-bold text-slate-900 group-hover:text-indigo-700 transition-colors duration-300 line-clamp-1" :title="product.name || product.material_name">
                  {{ product.name || product.material_name }}
                </h3>
             </div>
             
             <p class="text-sm text-slate-500 line-clamp-2 mb-6 leading-relaxed flex-1">
                {{ product.description || 'No description provided.' }}
             </p>

             <div class="grid grid-cols-3 gap-2 mt-auto">
                <div class="bg-slate-50 rounded-xl p-2.5 flex flex-col items-center justify-center text-center border border-slate-100">
                   <span class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Size</span>
                   <span class="text-xs font-bold text-slate-700 truncate w-full">{{ product.size || 'N/A' }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-2.5 flex flex-col items-center justify-center text-center border border-slate-100">
                   <span class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">Min Qty</span>
                   <span class="text-xs font-bold text-slate-700 truncate w-full">{{ product.min_order || 1 }}</span>
                </div>
                <div class="bg-slate-50 rounded-xl p-2.5 flex flex-col items-center justify-center text-center border border-slate-100">
                   <span class="text-[10px] uppercase tracking-wider text-slate-400 font-semibold mb-0.5">SKU</span>
                   <span class="text-xs font-bold text-slate-700 truncate w-full">{{ product.sku_code || 'N/A' }}</span>
                </div>
             </div>
          </CardContent>
        </Card>
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
        const response = await api.get(`/distributor/supplier-products/${supplierId}`);
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