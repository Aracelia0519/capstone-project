import { createRouter, createWebHistory } from 'vue-router'
import AdminLayout from '@/layouts/AdminLayout.vue'
import Dashboard from '@/views/admin/Dashboard.vue'
import UserManagement from '@/views/admin/UserManagement.vue'
import PaintProducts from '@/views/admin/PaintProducts.vue'
import Inventory from '@/views/admin/Inventory.vue'
import ColorCustomization from '@/views/admin/ColorCustomization.vue'
import ServiceRequest from '@/views/admin/ServiceRequest.vue'
import Reports from '@/views/admin/Reports.vue'
import SystemSettings from '@/views/admin/SystemSettings.vue'
import AuditLogs from '@/views/admin/AuditLogs.vue'

const routes = [
  {
    path: '/admin',
    component: AdminLayout,
    children: [
      {
        path: 'dashboard',
        name: 'Dashboard',
        component: Dashboard
      },
      {
        path: 'userManagement',
        name: 'userManagement',
        component: UserManagement
      },
      {
        path: 'paintProducts',
        name: 'paintProducts',
        component: PaintProducts
      },
      {
        path: 'inventory',
        name: 'inventory',
        component: Inventory
      },
      {
        path: 'colorCustomization',
        name: 'colorCustomization',
        component: ColorCustomization
      },
      {
        path: 'serviceRequest',
        name: 'serviceRequest',
        component: ServiceRequest
      },
      {
        path: 'reports',
        name: 'reports',
        component: Reports
      },
      {
        path: 'systemSettings',
        name: 'systemSettings',
        component: SystemSettings
      },
      {
        path: 'auditLogs',
        name: 'auditLogs',
        component: AuditLogs
      },
      
      // ... other admin routes
    ]
  },
  // ... other routes
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router