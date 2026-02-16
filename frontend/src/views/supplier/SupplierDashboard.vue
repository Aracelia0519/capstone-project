<script setup lang="ts">
import { ref } from 'vue'
import { 
  DollarSign, 
  ShoppingCart, 
  Package, 
  Activity, 
  Download,
  AlertCircle,
  MoreHorizontal,
  Calendar
} from 'lucide-vue-next'

// --- Mock Data ---

const stats = [
  {
    title: "Total Revenue",
    value: "$45,231.89",
    change: "+20.1% from last month",
    icon: DollarSign,
  },
  {
    title: "Orders",
    value: "+2350",
    change: "+180.1% from last month",
    icon: ShoppingCart,
  },
  {
    title: "Products",
    value: "12,234",
    change: "+19% from last month",
    icon: Package,
  },
  {
    title: "Active Now",
    value: "+573",
    change: "+201 since last hour",
    icon: Activity,
  }
]

const recentOrders = [
  { id: "ORD-001", customer: "Liam Johnson", product: "Mech Keyboard", status: "Paid", amount: "$250.00" },
  { id: "ORD-002", customer: "Olivia Smith", product: "USB-C Hub", status: "Processing", amount: "$150.00" },
  { id: "ORD-003", customer: "Noah Williams", product: "Monitor 24\"", status: "Shipped", amount: "$350.00" },
  { id: "ORD-004", customer: "Emma Brown", product: "Ergo Mouse", status: "Paid", amount: "$450.00" },
  { id: "ORD-005", customer: "James Jones", product: "Laptop Sleeve", status: "Paid", amount: "$550.00" },
]

const lowStock = [
  { name: "Gaming Mouse Pad", sku: "GMP-001", stock: 2 },
  { name: "HDMI Cable 2m", sku: "HDMI-200", stock: 0 },
  { name: "Webcam 4K", sku: "WC-400", stock: 4 },
]
</script>

<template>
  <div class="flex-1 space-y-4 p-4 md:p-8 pt-6 overflow-x-hidden">
    
    <div class="flex flex-col space-y-4 md:flex-row md:items-center md:justify-between md:space-y-0">
      <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-foreground">Dashboard</h2>
      
      <div class="flex flex-col space-y-2 sm:flex-row sm:space-x-2 sm:space-y-0 w-full md:w-auto">
        <button class="inline-flex items-center justify-start whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 border border-input bg-background hover:bg-accent hover:text-accent-foreground h-9 px-4 py-2 w-full md:w-[240px] text-left font-normal">
          <Calendar class="mr-2 h-4 w-4 opacity-50" />
          <span class="text-muted-foreground truncate">Oct 20, 2023 - Nov 20, 2023</span>
        </button>
        
        <button class="inline-flex items-center justify-center whitespace-nowrap rounded-md text-sm font-medium ring-offset-background transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 bg-primary text-primary-foreground hover:bg-primary/90 h-9 px-4 py-2 w-full sm:w-auto">
          <Download class="mr-2 h-4 w-4" />
          Download
        </button>
      </div>
    </div>

    <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
      <div v-for="(stat, i) in stats" :key="i" class="rounded-xl border bg-card text-card-foreground shadow-sm">
        <div class="p-6 flex flex-row items-center justify-between space-y-0 pb-2">
          <h3 class="tracking-tight text-sm font-medium">{{ stat.title }}</h3>
          <component :is="stat.icon" class="h-4 w-4 text-muted-foreground" />
        </div>
        <div class="p-6 pt-0">
          <div class="text-2xl font-bold">{{ stat.value }}</div>
          <p class="text-xs text-muted-foreground">{{ stat.change }}</p>
        </div>
      </div>
    </div>

    <div class="grid gap-4 grid-cols-1 lg:grid-cols-7">
      
      <div class="col-span-1 lg:col-span-4 rounded-xl border bg-card text-card-foreground shadow-sm">
        <div class="p-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="grid gap-1">
            <h3 class="font-semibold text-xl leading-none tracking-tight">Recent Orders</h3>
            <p class="text-sm text-muted-foreground">You made 265 sales this month.</p>
          </div>
        </div>
        
        <div class="p-0">
          <div class="relative w-full overflow-x-auto">
            <table class="w-full caption-bottom text-sm text-left">
              <thead class="[&_tr]:border-b">
                <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                  <th class="h-12 px-4 align-middle font-medium text-muted-foreground">Order</th>
                  <th class="h-12 px-4 align-middle font-medium text-muted-foreground">Customer</th>
                  <th class="h-12 px-4 align-middle font-medium text-muted-foreground hidden sm:table-cell">Status</th>
                  <th class="h-12 px-4 align-middle font-medium text-muted-foreground text-right">Amount</th>
                </tr>
              </thead>
              <tbody class="[&_tr:last-child]:border-0">
                <tr v-for="order in recentOrders" :key="order.id" class="border-b transition-colors hover:bg-muted/50">
                  <td class="p-4 font-medium">{{ order.id }}</td>
                  <td class="p-4">
                    <div class="font-medium whitespace-nowrap">{{ order.customer }}</div>
                    <div class="text-xs text-muted-foreground hidden md:block">{{ order.product }}</div>
                  </td>
                  <td class="p-4 hidden sm:table-cell">
                    <div class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                      :class="{
                        'border-transparent bg-green-100 text-green-700': order.status === 'Paid',
                        'border-transparent bg-blue-100 text-blue-700': order.status === 'Shipped',
                        'border-transparent bg-yellow-100 text-yellow-700': order.status === 'Processing',
                      }">
                      {{ order.status }}
                    </div>
                  </td>
                  <td class="p-4 text-right font-medium">{{ order.amount }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="col-span-1 lg:col-span-3 grid gap-4">
        
        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
          <div class="p-6">
             <h3 class="font-semibold text-lg">Revenue Overview</h3>
             <div class="mt-4 h-[180px] sm:h-[200px] flex items-end justify-between gap-2">
                <div v-for="h in [40, 70, 45, 90, 60, 80, 50]" :key="h" 
                     class="w-full bg-primary rounded-t-md transition-all hover:opacity-80"
                     :style="{ height: `${h}%` }"></div>
             </div>
             <div class="flex justify-between mt-2 text-xs text-muted-foreground">
                <span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span><span>S</span>
             </div>
          </div>
        </div>

        <div class="rounded-xl border bg-card text-card-foreground shadow-sm">
          <div class="p-6 pb-3">
             <h3 class="font-semibold text-lg flex items-center gap-2">
               <AlertCircle class="h-5 w-5 text-red-500" />
               Low Stock
             </h3>
          </div>
          <div class="p-6 pt-0 grid gap-4">
            <div v-for="(item, i) in lowStock" :key="i" class="flex items-center justify-between border-b pb-4 last:border-0 last:pb-0">
               <div class="space-y-1 min-w-0">
                  <p class="text-sm font-medium leading-none truncate pr-2">{{ item.name }}</p>
                  <p class="text-xs text-muted-foreground">{{ item.sku }}</p>
               </div>
               <div class="flex items-center gap-2 sm:gap-4 shrink-0">
                  <span class="text-sm font-bold whitespace-nowrap" :class="item.stock === 0 ? 'text-red-600' : 'text-orange-600'">
                    {{ item.stock }} left
                  </span>
                  <button class="h-8 w-8 inline-flex items-center justify-center rounded-md border border-input bg-transparent hover:bg-accent text-sm">
                    <MoreHorizontal class="h-4 w-4" />
                  </button>
               </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>