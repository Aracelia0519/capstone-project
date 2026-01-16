import { createRouter, createWebHistory } from 'vue-router'
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'

// Import all layouts and views (keep your existing imports)
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
import LogIn from '@/views/landingPage/LogIn.vue'
import SignUp from '@/views/landingPage/SignUp.vue'

import HRLayout from '@/layouts/HRLayout.vue'
import HRDashboard from '@/views/humanResource/HRDashboard.vue'
import EmployeesList from '@/views/humanResource/EmployeesList.vue'
import PositionsRoles from '@/views/humanResource/PositionsRoles.vue'
import Departments from '@/views/humanResource/Departments.vue'
import EmploymentStatus from '@/views/humanResource/EmploymentStatus.vue'
import ReportsHR from '@/views/humanResource/Reports.vue'

import FinanceLayout from '@/layouts/financeLayout.vue'
import FinanceDashboard from '@/views/finance/financeDashboard.vue'
import Transactions from '@/views/finance/Transactions.vue'
import PaymentMethods from '@/views/finance/PaymentMethods.vue'
import Invoices from '@/views/finance/Invoices.vue'
import ReportsFinance from '@/views/finance/ReportsFinance.vue'

import CRMLayout from '@/layouts/CRMLayout.vue'
import CRMDashboard from '@/views/CRM/CRMDashboard.vue'
import CRMClients from '@/views/CRM/CRMClients.vue'
import CRMDistributors from '@/views/CRM/CRMDistributors.vue'
import CRMServiceProviders from '@/views/CRM/CRMServiceProviders.vue'
import CRMInteractions from '@/views/CRM/CRMInteractions.vue'
import CRMFollowUps from '@/views/CRM/CRMFollowUps.vue'
import CRMReports from '@/views/CRM/CRMReports.vue'

import ECommerceLayout from '@/layouts/ECommerceLayout.vue'
import ECommerceDashboard from '@/views/E-commerce/ECommerceDashboard.vue'
import ECommerceProducts from '@/views/E-commerce/ECommerceProducts.vue'
import ECommerceCategories from '@/views/E-commerce/ECommerceCategories.vue'
import ECommerceOrders from '@/views/E-commerce/ECommerceOrders.vue'
import ECommercePayments from '@/views/E-commerce/ECommercePayments.vue'
import ECommerceDelivery from '@/views/E-commerce/ECommerceDelivery.vue'
import ECommerceReturns from '@/views/E-commerce/ECommerceReturns.vue'
import ECommerceReviews from '@/views/E-commerce/ECommerceReviews.vue'
import ECommercePromotions from '@/views/E-commerce/ECommercePromotions.vue'
import ECommerceReports from '@/views/E-commerce/ECommerceReports.vue'

import ECommerceClientLayout from '@/layouts/ECommerceClientLayout.vue'
import ECommerceShop from '@/views/ClientE-Commerce/ECommerceShop.vue'
import ECommerceProductDetail from '@/views/ClientE-Commerce/ECommerceProductDetail.vue'
import ECommerceServices from '@/views/ClientE-Commerce/ECommerceServices.vue'
import ECommerceCart from '@/views/ClientE-Commerce/ECommerceCart.vue'
import ECommerceCheckout from '@/views/ClientE-Commerce/ECommerceCheckout.vue'
import ClientECommerceOrders from '@/views/ClientE-Commerce/ECommerceOrders.vue'
import ECommerceProfile from '@/views/ClientE-Commerce/ECommerceProfile.vue'

const routes = [
  {
    path: '/',
    redirect: '/Landing/homeLanding'
  },
  {
    path: '/admin',
    component: AdminLayout,
    meta: { requiresAuth: true, role: 'admin' },
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
    meta: { requiresAuth: true, role: 'distributor' },
    children: [
      {
        path: 'distributordashboard',
        name: 'distributordashboard',
        component: DistributorDashboard
      },
      {
        path: 'paintInventory',
        name: 'paintInventory',
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
    meta: { requiresAuth: true, role: 'service_provider' },
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
    meta: { requiresAuth: true, role: 'client' },
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
    meta: { requiresAuth: false },
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
      {
        path: 'logIn',
        name: 'logIn',
        component: LogIn
      },
      {
        path: 'signUp',
        name: 'signUp',
        component: SignUp
      },
    ]
  },


  /*
  no authentication yet
  */

  {
    path: '/HR',
    component: HRLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: 'HRdashboard',
        name: 'HRdashboard',
        component: HRDashboard
      },
      {
        path: 'employeesListHR',
        name: 'employeesListHR',
        component: EmployeesList
      },
      {
        path: 'positionRolesHR',
        name: 'positionRolesHR',
        component: PositionsRoles
      },
      {
        path: 'departmentsHR',
        name: 'departmentsHR',
        component: Departments
      },
      {
        path: 'employmentStatusHR',
        name: 'employmentStatusHR',
        component: EmploymentStatus
      },
      {
        path: 'reportsHR',
        name: 'reportsHR',
        component: ReportsHR
      },
    ]
  },
  {
    path: '/finance',
    component: FinanceLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: 'financeDashboard',
        name: 'financeDashboard',
        component: FinanceDashboard
      },
      {
        path: 'transactions',
        name: 'transactions',
        component: Transactions
      },
      {
        path: 'paymentMethods',
        name: 'paymentMethods',
        component: PaymentMethods
      },
      {
        path: 'invoices',
        name: 'invoices',
        component: Invoices
      },
      {
        path: 'reportFinance',
        name: 'reportFinance',
        component: ReportsFinance
      },
    ]
  },
  {
    path: '/CRM',
    component: CRMLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: 'CRMDashboard',
        name: 'CRMDashboard',
        component: CRMDashboard
      },
      {
        path: 'CRMClients',
        name: 'CRMClients',
        component: CRMClients
      },
      {
        path: 'CRMDistributors',
        name: 'CRMDistributors',
        component: CRMDistributors
      },
      {
        path: 'CRMServiceProviders',
        name: 'CRMServiceProviders',
        component: CRMServiceProviders
      },
      {
        path: 'CRMInteractions',
        name: 'CRMInteractions',
        component: CRMInteractions
      },
      {
        path: 'CRMFollowUps',
        name: 'CRMFollowUps',
        component: CRMFollowUps
      },
      {
        path: 'CRMReports',
        name: 'CRMReports',
        component: CRMReports
      },
    ]
  },
  {
    path: '/ECommerce',
    component: ECommerceLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: 'ECDashboard',
        name: 'ECDashboard',
        component: ECommerceDashboard
      },
      {
        path: 'ECProducts',
        name: 'ECProducts',
        component: ECommerceProducts
      },
      {
        path: 'ECCategories',
        name: 'ECCategories',
        component: ECommerceCategories
      },
      {
        path: 'ECOrders',
        name: 'ECOrders',
        component: ECommerceOrders
      },
      {
        path: 'ECPayment',
        name: 'ECPayment',
        component: ECommercePayments
      },
      {
        path: 'ECDelivery',
        name: 'ECDelivery',
        component: ECommerceDelivery
      },
      {
        path: 'ECReturns',
        name: 'ECReturns',
        component: ECommerceReturns
      },
      {
        path: 'ECReviews',
        name: 'ECReviews',
        component: ECommerceReviews
      },
      {
        path: 'ECPromotions',
        name: 'ECPromotions',
        component: ECommercePromotions
      },
      {
        path: 'ECreports',
        name: 'ECreports',
        component: ECommerceReports
      },
    ]
  },
  {
    path: '/ECommerceClient',
    component: ECommerceClientLayout,
    meta: { requiresAuth: false },
    children: [
      {
        path: 'EccommerceShop',
        name: 'EccommerceShop',
        component: ECommerceShop
      },
      {
        path: 'EccommerceProductDetails',
        name: 'EccommerceProductDetails',
        component: ECommerceProductDetail
      },
      {
        path: 'EccommerceServices',
        name: 'EccommerceServices',
        component: ECommerceServices
      },
      {
        path: 'EccommerceCart',
        name: 'EccommerceCart',
        component: ECommerceCart
      },
      {
        path: 'EccommerceCheckOut',
        name: 'EccommerceCheckOut',
        component: ECommerceCheckout
      },
      {
        path: 'EccommerceOrders',
        name: 'EccommerceOrders',
        component: ClientECommerceOrders
      },
      {
        path: 'EccommerceProfile',
        name: 'EccommerceProfile',
        component: ECommerceProfile
      },
    ]
  },


  // Catch all route - redirect to home
  {
    path: '/:pathMatch(.*)*',
    redirect: '/Landing/homeLanding'
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

// Track navigation to prevent duplicate API calls
let isNavigating = false
let pendingNavigation = null

// Route guard to check authentication
router.beforeEach(async (to, from, next) => {
  // Skip if already navigating
  if (isNavigating) {
    pendingNavigation = { to, from, next }
    return
  }

  isNavigating = true
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiredRole = to.meta.role

  if (requiresAuth) {
    try {
      // Get user from cache (fast) or API (first time)
      const user = await getCurrentUser()
      
      if (!user) {
        clearAuthData()
        next('/Landing/logIn')
        isNavigating = false
        processPendingNavigation()
        return
      }
      
      // Check role if required
      if (requiredRole && user.role !== requiredRole) {
        const roleRoutes = {
          admin: '/admin/dashboard',
          distributor: '/distributor/distributordashboard',
          service_provider: '/serviceProvider/dashboardSP',
          client: '/Clients/dashboardC'
        }
        next(roleRoutes[user.role] || '/Landing/homeLanding')
        isNavigating = false
        processPendingNavigation()
        return
      }
      
      next()
    } catch (error) {
      clearAuthData()
      next('/Landing/logIn')
    } finally {
      isNavigating = false
      processPendingNavigation()
    }
  } else {
    next()
    isNavigating = false
    processPendingNavigation()
  }
})

// Process any pending navigation
function processPendingNavigation() {
  if (pendingNavigation) {
    const { to, from, next } = pendingNavigation
    pendingNavigation = null
    router.beforeEach(() => {})(to, from, next)
  }
}

// Add axios interceptor to handle 401 responses globally
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      clearAuthData()
      // Only redirect if not already going to login
      if (!router.currentRoute.value.path.includes('/logIn')) {
        router.push('/Landing/logIn')
      }
    }
    return Promise.reject(error)
  }
)

export default router