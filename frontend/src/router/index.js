import { createRouter, createWebHistory } from 'vue-router'
import { getCurrentUser, clearAuthData } from '@/utils/auth'
import axios from '@/utils/axios'

// Import all layouts and views
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
import PartneredSupplier from '@/views/distributor/PartneredSupplier.vue'
import PaintInventory from '@/views/distributor/PaintInventory.vue'
import ProductAvailable from '@/views/distributor/ProductAvailable.vue'
import ProductDeployment from '@/views/distributor/ProductDeployment.vue'
import OrdersRequest from '@/views/distributor/OrdersRequest.vue'
import ProcurementApproval from '@/views/distributor/ProcurementApproval.vue'
import ColorDemandInsights from '@/views/distributor/ColorDemandInsights.vue'
import SalesHistory from '@/views/distributor/SalesHistory.vue'
import ServiceProviders from '@/views/distributor/ServiceProviders.vue'
import OperationalDistributors from '@/views/distributor/OperationalDistributors.vue'
import HRManagers from '@/views/distributor/HRManagers.vue'
import FinanceManagers from '@/views/distributor/FinanceManagers.vue'
import WorkingHours from '@/views/distributor/WorkingHours.vue'
import PayrollFrequency from '@/views/distributor/PayrollFrequency.vue'
import PartnerDistributorReq from '@/views/distributor/PartnerDistributorReq.vue'
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
import OfferJobs from '@/views/serviceProvider/OfferJobs.vue'
import SPChat from '@/views/serviceProvider/SPChat.vue'
import SPPaymentSettings from '@/views/serviceProvider/SPPaymentSettings.vue'
import SPCRM from '@/views/serviceProvider/SPCRM.vue'
import SpShop from '@/views/serviceProvider/SpShop.vue'
import SpCart from '@/views/serviceProvider/SpCart.vue'
import SpProductDetails from '@/views/serviceProvider/SpProductDetails.vue'
import SpInventory from '@/views/serviceProvider/SpInventory.vue'

import ClientLayout from '@/layouts/ClientLayout.vue'
import DashboardC from '@/views/client/DashboardC.vue'
import MyServiceRequests from '@/views/client/MyServiceRequests.vue'
import ClientChat from '@/views/client/ClientChat.vue'
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
import PayrollLogin from '@/views/landingPage/PayrollLogin.vue'

import HRLayout from '@/layouts/HRLayout.vue'
import HRDashboard from '@/views/humanResource/HRDashboard.vue'
import EmployeesList from '@/views/humanResource/EmployeesList.vue'
import PositionsRoles from '@/views/humanResource/PositionsRoles.vue'
import Departments from '@/views/humanResource/Departments.vue'
import EmploymentStatus from '@/views/humanResource/EmploymentStatus.vue'
import ReportsHR from '@/views/humanResource/Reports.vue'
import RecruitmentApplication from '@/views/humanResource/RecruitmentApplication.vue'
import PayrollManagement from '@/views/humanResource/PayrollManagement.vue'
import AttendanceRequestHR from '@/views/humanResource/AttendanceRequestHR.vue'
import LeaveRequestHR from '@/views/humanResource/LeaveRequestHR.vue'

import FinanceLayout from '@/layouts/financeLayout.vue'
import FinanceDashboard from '@/views/finance/financeDashboard.vue'
import Transactions from '@/views/finance/Transactions.vue'
import PaymentMethods from '@/views/finance/PaymentMethods.vue'
import Invoices from '@/views/finance/Invoices.vue'
import ReportsFinance from '@/views/finance/ReportsFinance.vue'
import ProcurementRequestFinance from '@/views/finance/ProcurementRequestFinance.vue'
import PayrollRequestFinance from '@/views/finance/PayrollRequestFinance.vue'
import PayrollPaidFinance from '@/views/finance/PayrollPaidFinance.vue'
import procurementBudgetRelease from '@/views/finance/procurementBudgetRelease.vue'

import ECommerceLayout from '@/layouts/ECommerceLayout.vue'
import ECommerceDashboard from '@/views/E-commerce/ECommerceDashboard.vue'
import ProcurementRequests from '@/views/E-commerce/ProcurementRequests.vue'
import ECommerceCategories from '@/views/E-commerce/ECommerceCategories.vue'
import ECProcessProcurement from '@/views/E-commerce/ECProcessProcurement.vue'
import ECPTrackProcurement from '@/views/E-commerce/ECPTrackProcurement.vue'
import ECommerceOrders from '@/views/E-commerce/ECommerceOrders.vue'
import ECommercePayments from '@/views/E-commerce/ECommercePayments.vue'
import ECommerceDelivery from '@/views/E-commerce/ECommerceDelivery.vue'
import ECommerceReturns from '@/views/E-commerce/ECommerceReturns.vue'
import ECommerceReviews from '@/views/E-commerce/ECommerceReviews.vue'
import ECPartnerSupplier from '@/views/E-commerce/ECPartnerSupplier.vue'
import ECServiceProvider from '@/views/E-commerce/ECServiceProvider.vue'
import ECommercePromotions from '@/views/E-commerce/ECommercePromotions.vue'
import ECommerceReports from '@/views/E-commerce/ECommerceReports.vue'
import ECArrivedItem from '@/views/E-commerce/ECArrivedItem.vue'
import ECInventory from '@/views/E-commerce/ECInventory.vue'
import ECPrepareOrder from '@/views/E-commerce/ECPrepareOrder.vue'
import ECMessages from '@/views/E-commerce/ECMessages.vue'
import ECPromoApproval from '@/views/E-commerce/ECPromoApproval.vue'

import ECommerceClientLayout from '@/layouts/ECommerceClientLayout.vue'
import ECommerceShop from '@/views/ClientE-Commerce/ECommerceShop.vue'
import ECommerceServices from '@/views/ClientE-Commerce/ECommerceServices.vue'
import ECommerceCart from '@/views/ClientE-Commerce/ECommerceCart.vue'
import ECommerceCheckout from '@/views/ClientE-Commerce/ECommerceCheckout.vue'
import ClientECommerceOrders from '@/views/ClientE-Commerce/ECommerceOrders.vue'
import ECommerceProfile from '@/views/ClientE-Commerce/ECommerceProfile.vue'
import ServiceDetails from '@/views/ClientE-Commerce/ServiceDetails.vue'
import ProductDetails from '@/views/ClientE-Commerce/ProductDetails.vue'

import EmployeeLayout from '@/layouts/EmployeeLayout.vue'
import DashboardEmployee from '@/views/Employees/DashboardEmployee.vue'
import AttendanceEmployee from '@/views/Employees/AttendanceEmployee.vue'
import PayrollEmployee from '@/views/Employees/PayrollEmployee.vue'
import LeavesEmployee from '@/views/Employees/LeavesEmployee.vue'
import RequestsEmployee from '@/views/Employees/RequestsEmployee.vue'
import ProfileEmployee from '@/views/Employees/ProfileEmployee.vue'
import NotificationsEmployee from '@/views/Employees/NotificationsEmployee.vue'

import SupplierLayout from '@/layouts/SupplierLayout.vue'
import SupplierSettings from '@/views/supplier/SupplierSettings.vue'
import SupplierDashboard from '@/views/supplier/SupplierDashboard.vue'
import DistributorPartnerReq from '@/views/supplier/DistributorPartnerReq.vue'
import SupplierOrderRequest from '@/views/supplier/SupplierOrderRequest.vue'
import SupplierProcessOrders from '@/views/supplier/SupplierProcessOrders.vue'
import SupplierShipments from '@/views/supplier/SupplierShipments.vue'
import SupplierRawMaterials from '@/views/supplier/SupplierRawMaterials.vue'
import PersonnelOfficer from '@/views/supplier/PersonnelOfficer.vue'
import AddPersonnel from '@/views/supplier/AddPersonnel.vue'
import RoleActivation from '@/views/supplier/RoleActivation.vue'
import SupplierDelivery from '@/views/supplier/SupplierDelivery.vue'
import SupplierPayments from '@/views/supplier/SupplierPayments.vue'



import SpecialRBACLayout from '@/layouts/SpecialRBACLayout.vue'
import SupplierReturns from '@/views/supplier/SupplierReturns.vue'


const routes = [
  {
    path: '/',
    redirect: '/Landing/homeLanding'
  },
  {
    path: '/special-rbac',
    component: SpecialRBACLayout,
    meta: { requiresAuth: true }, 
    children: [
      // FIXED: Added missing 'dashboard' route matching LogIn.vue redirect
      {
        path: 'dashboard',
        name: 'rbac_dashboard',
        component: HRDashboard // Using HRDashboard as generic overview landing
      },
      {
        path: 'distributordashboard',
        name: 'rbac_distributordashboard',
        component: DistributorDashboard,
        meta: { requiresVerification: true }
      },
      {
        path: 'PartneredSupplier',
        name: 'rbac_PartneredSupplier',
        component: PartneredSupplier,
        meta: { requiresVerification: true }
      },
      {
        path: 'paintInventory',
        name: 'rbac_paintInventory',
        component: PaintInventory,
        meta: { requiresVerification: true }
      },
      {
        path: 'ProductDeployment',
        name: 'rbac_ProductDeployment',
        component: ProductDeployment,
        meta: { requiresVerification: true }
      },
      {
        path: 'productAvailable',
        name: 'rbac_productAvailable',
        component: ProductAvailable,
        meta: { requiresVerification: true }
      },
      {
        path: 'ordersRequest',
        name: 'rbac_ordersRequest',
        component: OrdersRequest,
        meta: { requiresVerification: true }
      },
      {
        path: 'ProcurementApproval',
        name: 'rbac_ProcurementApproval',
        component: ProcurementApproval,
        meta: { requiresVerification: true }
      },
      {
        path: 'ColorDemandInsights',
        name: 'rbac_ColorDemandInsights',
        component: ColorDemandInsights,
        meta: { requiresVerification: true }
      },
      {
        path: 'SalesHistory',
        name: 'rbac_SalesHistory',
        component: SalesHistory,
        meta: { requiresVerification: true }
      },
      {
        path: 'ServiceProviders',
        name: 'rbac_ServiceProviders',
        component: ServiceProviders,
        meta: { requiresVerification: true }
      }, 
      {
        path: 'OperationalDistributorD',
        name: 'rbac_OperationalDistributorD',
        component: OperationalDistributors,
        meta: { requiresVerification: true }
      },
      {
        path: 'HRmanagerD',
        name: 'rbac_HRmanagerD',
        component: HRManagers,
        meta: { requiresVerification: true }
      },
      {
        path: 'FinanceManagerD',
        name: 'rbac_FinanceManagerD',
        component: FinanceManagers,
        meta: { requiresVerification: true }
      },
      {
        path: 'WorkingHours',
        name: 'rbac_WorkingHours',
        component: WorkingHours,
        meta: { requiresVerification: true }
      },
      {
        path: 'PayrollFrequency',
        name: 'rbac_PayrollFrequency',
        component: PayrollFrequency,
        meta: { requiresVerification: true }
      },
      {
        path: 'PartnerDistributorReq',
        name: 'rbac_PartnerDistributorReq',
        component: PartnerDistributorReq,
        meta: { requiresVerification: true }
      },
      {
        path: 'reportsD',
        name: 'rbac_reportsD',
        component: DistributorReports,
        meta: { requiresVerification: true }
      },
      {
        path: 'profileSettings',
        name: 'rbac_profileSettings',
        component: ProfileSettings,
        meta: { requiresVerification: false }
      },
      {
        path: 'HRdashboard',
        name: 'rbac_HRdashboard',
        component: HRDashboard
      },
      {
        path: 'employeesListHR',
        name: 'rbac_employeesListHR',
        component: EmployeesList
      },
      {
        path: 'positionRolesHR',
        name: 'rbac_positionRolesHR',
        component: PositionsRoles
      },
      {
        path: 'departmentsHR',
        name: 'rbac_departmentsHR',
        component: Departments
      },
      {
        path: 'employmentStatusHR',
        name: 'rbac_employmentStatusHR',
        component: EmploymentStatus
      },
      {
        path: 'recruitmentApplication',
        name: 'rbac_recruitmentApplication',
        component: RecruitmentApplication
      },
      {
        path: 'payrollHR',
        name: 'rbac_payrollHR',
        component: PayrollManagement
      },
      {
        path: 'attendanceRequestHR',
        name: 'rbac_attendanceRequestHR',
        component: AttendanceRequestHR
      },
      {
        path: 'leaveRequestHR',
        name: 'rbac_leaveRequestHR',
        component: LeaveRequestHR
      },
      {
        path: 'reportsHR',
        name: 'rbac_reportsHR',
        component: ReportsHR
      },
      {
        path: 'financeDashboard',
        name: 'rbac_financeDashboard',
        component: FinanceDashboard
      },
      {
        path: 'transactions',
        name: 'rbac_transactions',
        component: Transactions
      },
      {
        path: 'paymentMethods',
        name: 'rbac_paymentMethods',
        component: PaymentMethods
      },
      {
        path: 'invoices',
        name: 'rbac_invoices',
        component: Invoices
      },
      {
        path: 'reportFinance',
        name: 'rbac_reportFinance',
        component: ReportsFinance
      },
      {
        path: 'procurementBudgetRelease',
        name: 'rbac_procurementBudgetRelease',
        component: procurementBudgetRelease
      },
      {
        path: 'procurementRequestFinance',
        name: 'rbac_procurementRequestFinance',
        component: ProcurementRequestFinance
      },
      {
        path: 'PayrollRequestFinance',
        name: 'rbac_PayrollRequestFinance',
        component: PayrollRequestFinance
      },
      {
        path: 'PayrollPaidFinance',
        name: 'rbac_PayrollPaidFinance',
        component: PayrollPaidFinance
      },
      {
        path: 'ECDashboard',
        name: 'rbac_ECDashboard',
        component: ECommerceDashboard
      },
      {
        path: 'ECProcurement',
        name: 'rbac_ECProcurement',
        component: ProcurementRequests
      },
      {
        path: 'ECCategories',
        name: 'rbac_ECCategories',
        component: ECommerceCategories
      },
      {
        path: 'ECProcessProcurement',
        name: 'rbac_ECProcessProcurement',
        component: ECProcessProcurement
      },
      {
        path: 'ECPTrackProcurement',
        name: 'rbac_ECPTrackProcurement',
        component: ECPTrackProcurement
      },
      {
        path: 'ECOrders',
        name: 'rbac_ECOrders',
        component: ECommerceOrders
      },
      {
        path: 'ECPayment',
        name: 'rbac_ECPayment',
        component: ECommercePayments
      },
      {
        path: 'ECDelivery',
        name: 'rbac_ECDelivery',
        component: ECommerceDelivery
      },
      {
        path: 'ECReturns',
        name: 'rbac_ECReturns',
        component: ECommerceReturns
      },
      {
        path: 'ECReviews',
        name: 'rbac_ECReviews',
        component: ECommerceReviews
      },
      {
        path: 'ECPartnerSupplier',
        name: 'rbac_ECPartnerSupplier',
        component: ECPartnerSupplier
      },
      {
        path: 'ECServiceProvider',
        name: 'rbac_ECServiceProvider',
        component: ECServiceProvider
      },
      {
        path: 'ECPromotions',
        name: 'rbac_ECPromotions',
        component: ECommercePromotions
      },
      {
        path: 'ECreports',
        name: 'rbac_ECreports',
        component: ECommerceReports
      },
      {
        path: 'ECArrivedItem',
        name: 'rbac_ECArrivedItem',
        component: ECArrivedItem
      },
      {
        path: 'ECInventory',
        name: 'rbac_ECInventory',
        component: ECInventory
      },
      {
        path: 'ECPrepareOrder',
        name: 'rbac_ECPrepareOrder',
        component: ECPrepareOrder
      },
      {
        path: 'ECMessages',
        name: 'rbac_ECMessages',
        component: ECMessages
      },
      {
        path: 'ECPromoApproval',
        name: 'rbac_ECPromoApproval',
        component: ECPromoApproval
      }, 
    ]
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
    meta: { requiresAuth: true, role: 'distributor', requiresVerification: true },
    children: [
      {
        path: 'distributordashboard',
        name: 'distributordashboard',
        component: DistributorDashboard,
        meta: { requiresVerification: true }
      },
      {
        path: 'PartneredSupplier',
        name: 'PartneredSupplier',
        component: PartneredSupplier,
        meta: { requiresVerification: true }
      },
      {
        path: 'paintInventory',
        name: 'paintInventory',
        component: PaintInventory,
        meta: { requiresVerification: true }
      },
      {
        path: 'ProductDeployment',
        name: 'ProductDeployment',
        component: ProductDeployment,
        meta: { requiresVerification: true }
      },
      {
        path: 'productAvailable',
        name: 'productAvailable',
        component: ProductAvailable,
        meta: { requiresVerification: true }
      },
      {
        path: 'ordersRequest',
        name: 'ordersRequest',
        component: OrdersRequest,
        meta: { requiresVerification: true }
      },
      {
        path: 'ProcurementApproval',
        name: 'ProcurementApproval',
        component: ProcurementApproval,
        meta: { requiresVerification: true }
      },
      {
        path: 'ColorDemandInsights',
        name: 'ColorDemandInsights',
        component: ColorDemandInsights,
        meta: { requiresVerification: true }
      },
      {
        path: 'SalesHistory',
        name: 'SalesHistory',
        component: SalesHistory,
        meta: { requiresVerification: true }
      },
      {
        path: 'ServiceProviders',
        name: 'ServiceProviders',
        component: ServiceProviders,
        meta: { requiresVerification: true }
      }, 
      {
        path: 'OperationalDistributorD',
        name: 'OperationalDistributorD',
        component: OperationalDistributors,
        meta: { requiresVerification: true }
      },
      {
        path: 'HRmanagerD',
        name: 'HRmanagerD',
        component: HRManagers,
        meta: { requiresVerification: true }
      },
      {
        path: 'FinanceManagerD',
        name: 'FinanceManagerD',
        component: FinanceManagers,
        meta: { requiresVerification: true }
      },
      {
        path: 'WorkingHours',
        name: 'WorkingHours',
        component: WorkingHours,
        meta: { requiresVerification: true }
      },
      {
        path: 'PayrollFrequency',
        name: 'PayrollFrequency',
        component: PayrollFrequency,
        meta: { requiresVerification: true }
      },
      {
        path: 'PartnerDistributorReq',
        name: 'PartnerDistributorReq',
        component: PartnerDistributorReq,
        meta: { requiresVerification: true }
      },
      {
        path: 'reportsD',
        name: 'reportsD',
        component: DistributorReports,
        meta: { requiresVerification: true }
      },
      {
        path: 'profileSettings',
        name: 'profileSettings',
        component: ProfileSettings,
        meta: { requiresVerification: false }
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
        path: 'OfferJobs',
        name: 'OfferJobs',
        component: OfferJobs
      },
      {
        path: 'SPChat',
        name: 'SPChat',
        component: SPChat
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
      {
        path: 'SPPaymentSettings',
        name: 'SPPaymentSettings',
        component: SPPaymentSettings
      },
      {
        path: 'SPCRM',
        name: 'SPCRM',
        component: SPCRM
      },
      { path: 'shop/:distributor_id', name: 'sp_shop', component: SpShop },
      { path: 'cart', name: 'sp_cart', component: SpCart },
      { path: 'ProductDetails/:id', name: 'sp_product_details', component: SpProductDetails },
      { path: 'inventory', name: 'sp_inventory', component: SpInventory },
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
      {
        path: 'ClientChat',
        name: 'ClientChat',
        component: ClientChat
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
      {
        path: 'payrollLogin',
        name: 'payrollLogin',
        component: PayrollLogin
      },
    ]
  },

  {
    path: '/HR',
    component: HRLayout,
    meta: { 
      requiresAuth: true,
      allowedRoles: ['hr_manager', 'employee'] 
    },
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
        path: 'recruitmentApplication',
        name: 'recruitmentApplication',
        component: RecruitmentApplication
      },
      {
        path: 'payrollHR',
        name: 'payrollHR',
        component: PayrollManagement
      },
      {
        path: 'attendanceRequestHR',
        name: 'attendanceRequestHR',
        component: AttendanceRequestHR
      },
      {
        path: 'leaveRequestHR',
        name: 'leaveRequestHR',
        component: LeaveRequestHR
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
    meta: { 
      requiresAuth: true,
      allowedRoles: ['finance_manager', 'employee'] 
    },
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
      {
        path: 'procurementBudgetRelease',
        name: 'procurementBudgetRelease',
        component: procurementBudgetRelease
      },
      {
        path: 'procurementRequestFinance',
        name: 'procurementRequestFinance',
        component: ProcurementRequestFinance
      },
      {
        path: 'PayrollRequestFinance',
        name: 'PayrollRequestFinance',
        component: PayrollRequestFinance
      },
      {
        path: 'PayrollPaidFinance',
        name: 'PayrollPaidFinance',
        component: PayrollPaidFinance
      },
    ]
  },
  
  {
    path: '/ECommerce',
    component: ECommerceLayout,
    meta: { requiresAuth: true,
    allowedRoles: ['operational_distributor', 'employee']},
    children: [
      {
        path: 'ECDashboard',
        name: 'ECDashboard',
        component: ECommerceDashboard
      },
      {
        path: 'ECProcurement',
        name: 'ECProcurement',
        component: ProcurementRequests
      },
      {
        path: 'ECCategories',
        name: 'ECCategories',
        component: ECommerceCategories
      },
      {
        path: 'ECProcessProcurement',
        name: 'ECProcessProcurement',
        component: ECProcessProcurement
      },
      {
        path: 'ECPTrackProcurement',
        name: 'ECPTrackProcurement',
        component: ECPTrackProcurement
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
        path: 'ECPartnerSupplier',
        name: 'ECPartnerSupplier',
        component: ECPartnerSupplier
      },
      {
        path: 'ECServiceProvider',
        name: 'ECServiceProvider',
        component: ECServiceProvider
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
      {
        path: 'ECArrivedItem',
        name: 'ECArrivedItem',
        component: ECArrivedItem
      },
      {
        path: 'ECInventory',
        name: 'ECInventory',
        component: ECInventory
      },
      {
        path: 'ECPrepareOrder',
        name: 'ECPrepareOrder',
        component: ECPrepareOrder
      },
      {
        path: 'ECMessages',
        name: 'ECMessages',
        component: ECMessages
      },
      {
        path: 'ECPromoApproval',
        name: 'ECPromoApproval',
        component: ECPromoApproval
      },
    ]
  },
  {
    path: '/Supplier',
    component: SupplierLayout,
    meta: { requiresAuth: true, allowedRoles: ['supplier', 'personnel_officer'], requiresVerification: true },
    children: [
      {
        path: 'SupplierDashboard',
        name: 'SupplierDashboard',
        component: SupplierDashboard
      },
      {
        path: 'SupplierSettings',
        name: 'SupplierSettings',
        component: SupplierSettings
      },
      {
        path: 'DistributorPartnerReq',
        name: 'DistributorPartnerReq',
        component: DistributorPartnerReq
      },
      {
        path: 'SupplierOrderRequest',
        name: 'SupplierOrderRequest',
        component: SupplierOrderRequest
      },
      {
        path: 'SupplierProcessOrders',
        name: 'SupplierProcessOrders',
        component: SupplierProcessOrders
      },
      {
        path: 'SupplierShipments',
        name: 'SupplierShipments',
        component: SupplierShipments
      },
      {
        path: 'SupplierRawMaterials',
        name: 'SupplierRawMaterials',
        component: SupplierRawMaterials
      },
      {
        path: 'PersonnelOfficer',
        name: 'PersonnelOfficer',
        component: PersonnelOfficer
      },
      {
        path: 'AddPersonnel',
        name: 'AddPersonnel',
        component: AddPersonnel
      },
      {
        path: 'RoleActivation',
        name: 'RoleActivation',
        component: RoleActivation
      },
      {
        path: 'SupplierDelivery',
        name: 'SupplierDelivery',
        component: SupplierDelivery
      },
      {
        path: 'SupplierPayments',
        name: 'SupplierPayments',
        component: SupplierPayments
      },     
      {
        path: 'SupplierReturns',
        name: 'SupplierReturns',
        component: SupplierReturns
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
      {
        path: 'ServiceDetails/:id', 
        name: 'ServiceDetails',
        component: ServiceDetails
      },
      {
        path: 'ProductDetails/:id', 
        name: 'ProductDetails',
        component: ProductDetails
      },
    ]
  },

  {
    path: '/Employees',
    component: EmployeeLayout,
    meta: { requiresAuth: true },
    children: [
      {
        path: 'DashboardEmployee',
        name: 'DashboardEmployee',
        component: DashboardEmployee
      },
      {
        path: 'AttendanceEmployee',
        name: 'AttendanceEmployee',
        component: AttendanceEmployee
      },
      {
        path: 'PayrollEmployee',
        name: 'PayrollEmployee',
        component: PayrollEmployee
      },
      {
        path: 'LeavesEmployee',
        name: 'LeavesEmployee',
        component: LeavesEmployee
      },
      {
        path: 'RequestEmployee',
        name: 'RequestEmployee',
        component: RequestsEmployee
      },
      {
        path: 'ProfileEmployee',
        name: 'ProfileEmployee',
        component: ProfileEmployee
      },
      {
        path: 'NotificationEmployee',
        name: 'NotificationEmployee',
        component: NotificationsEmployee
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

const verificationCache = {
  data: null,
  timestamp: null,
  TTL: 5 * 60 * 1000 
}

const clearVerificationCache = () => {
  verificationCache.data = null
  verificationCache.timestamp = null
}

const isVerificationCacheValid = () => {
  if (!verificationCache.data || !verificationCache.timestamp) {
    return false
  }
  return Date.now() - verificationCache.timestamp < verificationCache.TTL
}

router.beforeEach(async (to, from, next) => {
  if (isNavigating) {
    pendingNavigation = { to, from, next }
    return
  }

  isNavigating = true
  const requiresAuth = to.matched.some(record => record.meta.requiresAuth)
  const requiredRole = to.meta.role
  const requiresVerification = to.matched.some(record => record.meta.requiresVerification)
  const isProfileSettings = to.path === '/distributor/profileSettings'

  if (requiresAuth) {
    try {
      const user = await getCurrentUser()
      
      if (!user) {
        clearAuthData()
        clearVerificationCache()
        next('/Landing/logIn')
        isNavigating = false
        processPendingNavigation()
        return
      }
      
      if (requiredRole && user.role !== requiredRole) {
        const roleRoutes = {
          admin: '/admin/dashboard',
          distributor: '/distributor/distributordashboard',
          service_provider: '/serviceProvider/dashboardSP',
          client: '/Clients/dashboardC',
          operational_distributor: '/ECommerce/ECDashboard',
          finance_manager: '/finance/financeDashboard',
          hr_manager: '/HR/HRdashboard'
        }
        
        const redirectRoute = roleRoutes[user.role] || '/Landing/homeLanding'
        
        if (to.path.startsWith(redirectRoute)) {
          next()
        } else {
          next(redirectRoute)
        }
        
        isNavigating = false
        processPendingNavigation()
        return
      }
      
      if (user.role === 'distributor') {
        try {
          let verificationData = null
          
          if (isVerificationCacheValid()) {
            verificationData = verificationCache.data
          } else {
            const response = await axios.get('/distributor/requirements')
            if (response.data.status === 'success') {
              verificationData = response.data.data
              verificationCache.data = verificationData
              verificationCache.timestamp = Date.now()
            }
          }
          
          const hasRequirements = verificationData && verificationData.status
          const isApproved = verificationData && verificationData.status === 'approved'
          
          if (requiresVerification && !isApproved) {
            if (isProfileSettings) {
              next()
            } else {
              next({
                path: '/distributor/profileSettings',
                query: { 
                  verification_required: true,
                  status: hasRequirements ? verificationData.status : 'none'
                }
              })
            }
            isNavigating = false
            processPendingNavigation()
            return
          }
          
          if (isApproved) {
            next()
            isNavigating = false
            processPendingNavigation()
            return
          }
          
        } catch (error) {
          console.error('Failed to check verification status:', error)
          if (isProfileSettings) {
            next()
          } else {
            next('/distributor/profileSettings')
          }
          isNavigating = false
          processPendingNavigation()
          return
        }
      }
      
      next()
    } catch (error) {
      console.error('Auth error:', error)
      clearAuthData()
      clearVerificationCache()
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

function processPendingNavigation() {
  if (pendingNavigation) {
    const { to, from, next } = pendingNavigation
    pendingNavigation = null
    router.beforeEach(() => {})(to, from, next)
  }
}

axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      clearAuthData()
      clearVerificationCache()
      
      const currentPath = router.currentRoute.value.path
      if (!currentPath.includes('/logIn') && !currentPath.includes('/payrollLogin')) {
        router.push('/Landing/logIn')
      }
    }
    return Promise.reject(error)
  }
)

router.afterEach((to, from) => {
  if (from.path.includes('/distributor') && !to.path.includes('/distributor')) {
    clearVerificationCache()
  }
})

export default router