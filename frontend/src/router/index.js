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

import DistributorLayout from '@/layouts/DistributorLayout.vue'
import DistributorDashboard from '@/views/distributor/Dashboard.vue'
import PaintInventory from '@/views/distributor/PaintInventory.vue'
import OrdersRequest from '@/views/distributor/OrdersRequest.vue'
import ColorDemandInsights from '@/views/distributor/ColorDemandInsights.vue'
import SalesHistory from '@/views/distributor/SalesHistory.vue'
import ServiceProviders from '@/views/distributor/ServiceProviders.vue'
import ProfileSettings from '@/views/distributor/ProfileSettings.vue'
import DistributorReports from '@/views/distributor/Reports.vue'

import ServiceProviderLayout from '@/layouts/ServiceProviderLayout.vue'
import DashboardSP from '@/views/serviceProvider/DashboardSP.vue'
import Clients from '@/views/serviceProvider/Clients.vue'
import ServiceRequestsJobs from '@/views/serviceProvider/ServiceRequestsJobs.vue'
import VirtualPaintColor from '@/views/serviceProvider/VirtualPaintColor.vue'
import ColorHistory from '@/views/serviceProvider/ColorHistory.vue'
import PaintProductsSP from '@/views/serviceProvider/PaintProductsSP.vue'
import Distributors from '@/views/serviceProvider/Distributors.vue'
import ReportsSP from '@/views/serviceProvider/ReportsSP.vue'
import ProfileSettingsSP from '@/views/serviceProvider/ProfileSettingsSP.vue'

import ClientLayout from '@/layouts/ClientLayout.vue'
import DashboardC from '@/views/client/DashboardC.vue'
import MyServiceRequests from '@/views/client/MyServiceRequests.vue'
import ColorPreview from '@/views/client/ColorPreview.vue'
import ColorHistoryC from '@/views/client/ColorHistoryC.vue'
import Recommendations from '@/views/client/Recommendations.vue'
import ServiceProvidersC from '@/views/client/ServiceProvidersC.vue'
import ProfileC from '@/views/client/ProfileC.vue'

import LandingLayout from '@/layouts/LandingLayout.vue'
import homeLanding from '@/views/landingPage/homeLanding.vue'
import HowItWorks from '@/views/landingPage/HowItWorks.vue'
import ExploreColors from '@/views/landingPage/ExploreColors.vue'
import Services from '@/views/landingPage/Services.vue'

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
      
    ]
  },
  {
    path: '/distributor',
    component: DistributorLayout,
    children: [
      {
        path: 'distributordashboard',
        name: 'distributordashboard',
        component: DistributorDashboard
      },
      {
        path: 'paintInventory',
        path: 'paintInventory',
        component: PaintInventory
      },
      {
        path: 'ordersRequest',
        name: 'ordersRequest',
        component: OrdersRequest
      },
      {
        path: 'ColorDemandInsights',
        name: 'ColorDemandInsights',
        component: ColorDemandInsights
      },
      {
        path: 'SalesHistory',
        name: 'SalesHistory',
        component: SalesHistory
      },
      {
        path: 'ServiceProviders',
        name: 'ServiceProviders',
        component: ServiceProviders
      },
      {
        path: 'reportsD',
        name: 'reportsD',
        component: DistributorReports
      },
      {
        path: 'profileSettings',
        name: 'profileSettings',
        component: ProfileSettings
      },

    ]
  },
  {
    path: '/serviceProvider',
    component: ServiceProviderLayout,
    children: [
      {
        path: 'dashboardSP',
        name: 'dashboardSP',
        component: DashboardSP
      },
      {
        path: 'Clients',
        name: 'Clients',
        component: Clients
      },
      {
        path: 'ServiceRequestsJobs',
        name: 'ServiceRequestsJobs',
        component: ServiceRequestsJobs
      },
      {
        path: 'VirtualPaintColor',
        name: 'VirtualPaintColor',
        component: VirtualPaintColor
      },
      {
        path: 'ColorHistory',
        name: 'ColorHistory',
        component: ColorHistory
      },    
      {
        path: 'PaintProductsSP',
        name: 'PaintProductsSP',
        component: PaintProductsSP
      },
      {
        path: 'Distributors',
        name: 'Distributors',
        component: Distributors
      },
      {
        path: 'ReportsSP',
        name: 'ReportsSP',
        component: ReportsSP
      },
      {
        path: 'ProfileSettingsSP',
        name: 'ProfileSettingsSP',
        component: ProfileSettingsSP
      },
      
    ]
  },
  {
    path: '/Clients',
    component: ClientLayout,
    children: [
      {
        path: 'dashboardC',
        name: 'dashboardC',
        component: DashboardC
      },
      {
        path: 'myServiceRequest',
        name: 'myServiceRequest',
        component: MyServiceRequests
      },
      {
        path: 'colorPreview',
        name: 'colorPreview',
        component: ColorPreview
      },
      {
        path: 'colorHistoryC',
        name: 'colorHistoryC',
        component: ColorHistoryC
      },
      {
        path: 'recommendation',
        name: 'recommendation',
        component: Recommendations
      },
      {
        path: 'serviceProviderC',
        name: 'serviceProviderC',
        component: ServiceProvidersC
      },
      {
        path: 'profileC',
        name: 'profileC',
        component: ProfileC
      },
      
    ]
  },
  {
    path: '/Landing',
    component: LandingLayout,
    children: [
      {
        path: 'homeLanding',
        name: 'homeLanding',
        component: homeLanding
      },
      {
        path: 'howItWorks',
        name: 'howItWorks',
        component: HowItWorks
      },
      {
        path: 'exploreColors',
        name: 'exploreColors',
        component: ExploreColors
      },
      {
        path: 'servicesLanding',
        name: 'servicesLanding',
        component: Services
      },

    ]
  },
  // other routes bitch
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router