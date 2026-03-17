<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Client\ClientRequirementController;
use App\Http\Controllers\Api\ServiceProvider\ServiceProviderRequirementController;
use App\Http\Controllers\Api\Distributor\DistributorRequirementController;
use App\Http\Controllers\Api\Distributor\OperationalDistributorController; 
use App\Http\Controllers\Api\Distributor\HRManagerController; 
use App\Http\Controllers\Api\Distributor\FinanceManagerController; 
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Distributor\ProductController;
use App\Http\Controllers\Api\Employee\AttendanceRequestController;
use App\Http\Controllers\Api\Employee\PayrollController;
use App\Http\Controllers\Api\Supplier\PersonnelOfficerController;
use App\Http\Controllers\Api\PersonnelOfficer\PersonnelController;
use App\Http\Controllers\Api\EcommerceClient\ECommerceOrderController;
use App\Http\Controllers\Api\OperationDistributor\ECommerceDashboardController;
use App\Http\Controllers\Api\OperationDistributor\PaymentManagementController;
use App\Http\Controllers\Api\OperationDistributor\ReviewManagementController;
use App\Http\Controllers\Api\ServiceProvider\ServiceOfferingController;
use App\Http\Controllers\Api\ServiceProvider\ServiceJobController;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);

// ==========================================
// PUBLIC ROUTES (Accessible by Guests)
// ==========================================

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
});

// Public E-Commerce Client Routes - Shop
Route::prefix('client/shop')->group(function () {
    Route::get('/products', [\App\Http\Controllers\Api\EcommerceClient\ShopController::class, 'getProducts']);
    Route::get('/cart-items', [\App\Http\Controllers\Api\EcommerceClient\CartController::class, 'index']); // Public so guests don't get 401
});

// Public E-Commerce Client Routes - Services
Route::prefix('client/services')->group(function () {
    Route::get('/', [\App\Http\Controllers\Api\EcommerceClient\ClientServiceController::class, 'getServices']);
});

// Public E-Commerce Client Routes - Orders (Allows Guests to fetch safely)
Route::get('/client/orders', [ECommerceOrderController::class, 'index']);


// ==========================================
// PROTECTED ROUTES (Requires Authentication)
// ==========================================

Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    Route::prefix('special-rbac')->group(function () {
        Route::get('/sidebar', [\App\Http\Controllers\Api\SpecialRBAC\SpecialRBACSidebarController::class, 'getSidebarAccess']);
    });

    // Dashboard data routes
    Route::prefix('dashboard')->group(function () {
        Route::get('/admin', [DashboardController::class, 'adminDashboard']);
        Route::get('/distributor', [DashboardController::class, 'distributorDashboard']);
        Route::get('/service-provider', [DashboardController::class, 'serviceProviderDashboard']);
        Route::get('/client', [DashboardController::class, 'clientDashboard']);
    });

    // User management (for admin)
    Route::prefix('users')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
        Route::post('/{id}/activate', [UserController::class, 'activate']);
        Route::post('/{id}/deactivate', [UserController::class, 'deactivate']);
    });

    // Profile routes (for all authenticated users)
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'profile']);
        Route::put('/', [UserController::class, 'updateProfile']);
        Route::put('/password', [UserController::class, 'updatePassword']);
    });

    // Client Requirements - ID Verification & Ecommerce
    Route::prefix('client')->group(function () {

        // -----------------------------------------------------
        // CLIENT PAYMENT SETTINGS (GCash)
        // -----------------------------------------------------
        Route::prefix('payment-settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Client\ClientPaymentSettingController::class, 'show']);
            Route::put('/', [\App\Http\Controllers\Api\Client\ClientPaymentSettingController::class, 'update']);
        });

        // -----------------------------------------------------
        // CHATS: galing sa selected Servive Provider ng Client
        // -----------------------------------------------------
        Route::prefix('chat')->group(function () {
            Route::get('/contacts', [\App\Http\Controllers\Api\Client\ClientChatController::class, 'getContacts']);
            Route::get('/messages/{providerId}', [\App\Http\Controllers\Api\Client\ClientChatController::class, 'getMessages']);
            Route::post('/send', [\App\Http\Controllers\Api\Client\ClientChatController::class, 'sendMessage']);
            
            Route::post('/deals/{dealId}/respond', [\App\Http\Controllers\Api\Client\ClientChatController::class, 'respondToDeal']);
            Route::post('/payment-terms/{termId}/respond', [\App\Http\Controllers\Api\Client\ClientChatController::class, 'respondToPaymentTerm']); // NEW
        });

        Route::prefix('requirements')->group(function () {
            Route::get('/', [ClientRequirementController::class, 'index']);
            Route::post('/', [ClientRequirementController::class, 'store']);
            
            Route::prefix('address')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Client\ClientAddressController::class, 'index']);
                Route::post('/coordinates', [\App\Http\Controllers\Api\Client\ClientAddressController::class, 'updateCoordinates']);
            });
            
            Route::prefix('admin')->group(function () {
                Route::put('/{id}', [ClientRequirementController::class, 'update']);
                Route::get('/pending', [ClientRequirementController::class, 'pending']);
            });
        });

        // -----------------------------------------------------
        // ECOMMERCE SERVICES: Actions requiring Auth
        // -----------------------------------------------------
        Route::prefix('services')->group(function () {
            // NEW: Reviews Route
            Route::post('/requests/{id}/review', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'submitReview']);
            // NEW: Client Reply Route
            Route::post('/reviews/{id}/reply', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'submitClientReply']);
            Route::post('/request', [\App\Http\Controllers\Api\EcommerceClient\ClientServiceController::class, 'requestService']);
            Route::get('/my-requests', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'index']);

            // NEW PAYMENT ROUTES FOR OFFICIAL TERMS
            Route::post('/payment-terms/{termId}/upload-proof', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'uploadProof']);
            Route::post('/payment-terms/{termId}/pay-gcash', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'payWithGcash']);
            Route::post('/payment-terms/verify-gcash', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'verifyGcashPayment']);
            
            // NEW COMPLETION REVIEW ROUTES
            Route::post('/requests/{id}/approve-completion', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'approveCompletion']);
            Route::post('/requests/{id}/reject-completion', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'rejectCompletion']);
            
            // NEW: Reviews Route
            Route::post('/requests/{id}/review', [\App\Http\Controllers\Api\Client\ClientServiceRequestController::class, 'submitReview']);
        });

        // E-Commerce Client Shop & Cart Routes
        Route::prefix('shop')->group(function () {
            // Protected Shop Endpoints (Actions)
            Route::post('/shipping-fee', [\App\Http\Controllers\Api\EcommerceClient\ShopController::class, 'calculateShipping']);
            Route::post('/cart', [\App\Http\Controllers\Api\EcommerceClient\ShopController::class, 'addToCart']);
            Route::post('/order-now', [\App\Http\Controllers\Api\EcommerceClient\ShopController::class, 'orderNow']);

            Route::post('/verify-gcash', [\App\Http\Controllers\Api\EcommerceClient\ShopController::class, 'verifyGcashPayment']);

            // Cart Management Endpoints (Actions)
            Route::put('/cart-items/{id}', [\App\Http\Controllers\Api\EcommerceClient\CartController::class, 'update']);
            Route::delete('/cart-items/{id}', [\App\Http\Controllers\Api\EcommerceClient\CartController::class, 'destroy']);
            Route::delete('/cart-items', [\App\Http\Controllers\Api\EcommerceClient\CartController::class, 'clear']);
            Route::post('/cart-items/checkout', [\App\Http\Controllers\Api\EcommerceClient\CartController::class, 'checkout']);
            
            // NEW: Dedicated verify-gcash specifically for Cart checkouts
            Route::post('/cart-items/verify-gcash', [\App\Http\Controllers\Api\EcommerceClient\CartController::class, 'verifyGcashPayment']);
        });

        Route::post('/save-color', [\App\Http\Controllers\Api\Client\ColorController::class, 'saveColor']);
        Route::get('/colors', [\App\Http\Controllers\Api\Client\ColorController::class, 'getSavedColors']);
        Route::get('/colors/{id}', [\App\Http\Controllers\Api\Client\ColorController::class, 'getColor']);
        Route::delete('/colors/{id}', [\App\Http\Controllers\Api\Client\ColorController::class, 'deleteColor']);
        Route::post('/colors/{id}/toggle-favorite', [\App\Http\Controllers\Api\Client\ColorController::class, 'toggleFavorite']);
        Route::get('/color-stats', [\App\Http\Controllers\Api\Client\ColorController::class, 'getColorStats']);
        
        // E-Commerce Protected Order Actions
        Route::prefix('orders')->group(function () {
            Route::post('/reviews', [ECommerceOrderController::class, 'submitReview']);
            Route::get('/{id}/pickup-details', [ECommerceOrderController::class, 'getPickUpDetails']);
            Route::post('/{id}/pickup-submit', [ECommerceOrderController::class, 'submitPickUp']);

            Route::post('/return-request', [ECommerceOrderController::class, 'submitReturnRequest']);
            Route::get('/items/{itemId}/return-chat', [ECommerceOrderController::class, 'getReturnChat']);
            Route::post('/returns/{id}/message', [ECommerceOrderController::class, 'sendReturnMessage']);
            Route::post('/returns/{id}/tracking', [ECommerceOrderController::class, 'submitReturnTracking']);
        });
    });

    // Service Provider Requirements - ID Verification
    Route::prefix('service-provider')->group(function () {

        // -----------------------------------------------------
        // SERVICE PROVIDER PAYMENT SETTINGS SHITS
        // -----------------------------------------------------
        Route::prefix('payment-settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\ServiceProvider\SPPaymentSettingController::class, 'show']);
            Route::put('/', [\App\Http\Controllers\Api\ServiceProvider\SPPaymentSettingController::class, 'update']);
        });

        // -----------------------------------------------------
        // Chat to between Client and Service Provider
        // -----------------------------------------------------
        Route::prefix('chat')->group(function () {
            Route::get('/contacts', [\App\Http\Controllers\Api\ServiceProvider\SPChatController::class, 'getContacts']);
            Route::get('/messages/{clientId}', [\App\Http\Controllers\Api\ServiceProvider\SPChatController::class, 'getMessages']);
            Route::post('/send', [\App\Http\Controllers\Api\ServiceProvider\SPChatController::class, 'sendMessage']);
        });

        // -----------------------------------------------------
        // NEW CRM ROUTES (Reviews & Feedback)
        // -----------------------------------------------------
        Route::prefix('crm')->group(function () {
            Route::get('/reviews', [\App\Http\Controllers\Api\ServiceProvider\SPCRMController::class, 'getReviews']);
            Route::post('/reviews/{id}/reply', [\App\Http\Controllers\Api\ServiceProvider\SPCRMController::class, 'replyToReview']);
            Route::patch('/reviews/{id}/toggle-visibility', [\App\Http\Controllers\Api\ServiceProvider\SPCRMController::class, 'toggleVisibility']);
        });

        Route::get('/job-requests/gcash-details', [\App\Http\Controllers\Api\ServiceProvider\ServiceJobController::class, 'getGcashDetails']);
        Route::post('/job-requests/payment-terms/{termId}/approve', [\App\Http\Controllers\Api\ServiceProvider\ServiceJobController::class, 'approvePaymentProof']);

        Route::get('/job-requests', [ServiceJobController::class, 'index']);
        Route::post('/job-requests/{id}/approve', [ServiceJobController::class, 'approve']);
        Route::post('/job-requests/{id}/reject', [ServiceJobController::class, 'reject']);
        Route::post('/job-requests/{id}/complete', [ServiceJobController::class, 'submitCompletion']); // NEW

        Route::prefix('requirements')->group(function () {
            Route::get('/', [ServiceProviderRequirementController::class, 'index']);
            Route::post('/', [ServiceProviderRequirementController::class, 'store']);
            
            // NEW: Address specific routes
            Route::prefix('address')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderAddressController::class, 'index']);
                Route::post('/coordinates', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderAddressController::class, 'updateCoordinates']);
            });

            Route::prefix('admin')->group(function () {
                Route::get('/pending', [ServiceProviderRequirementController::class, 'pending']);
                Route::put('/{id}', [ServiceProviderRequirementController::class, 'update']);
                Route::get('/statistics', [ServiceProviderRequirementController::class, 'statistics']);
            });
        });

        Route::prefix('services')->group(function () {
            Route::get('/', [ServiceOfferingController::class, 'index']);
            Route::post('/', [ServiceOfferingController::class, 'store']);
            Route::post('/{id}', [ServiceOfferingController::class, 'update']); // Using POST for file-uploads in forms
            Route::delete('/{id}', [ServiceOfferingController::class, 'destroy']);
            Route::patch('/{id}/toggle', [ServiceOfferingController::class, 'toggleStatus']);
        });

        Route::post('/save-color', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorController::class, 'saveColor']);
        Route::get('/colors', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorController::class, 'getSavedColors']);
        Route::get('/colors/{id}', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorController::class, 'getColor']);
        Route::delete('/colors/{id}', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorController::class, 'deleteColor']);
        Route::post('/colors/{id}/toggle-favorite', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorController::class, 'toggleFavorite']);
        Route::get('/color-stats', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorController::class, 'getColorStats']);

        Route::get('/color-history', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderColorHistoryController::class, 'index']);

        Route::get('/distributors', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderDistributorController::class, 'index']);
        Route::post('/distributors/request', [\App\Http\Controllers\Api\ServiceProvider\ServiceProviderDistributorController::class, 'requestPartnership']);
    });

    // Distributor Requirements - Business Verification
    Route::prefix('distributor')->group(function () {
        

        Route::prefix('procurement-approvals')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Distributor\DistributorProcurementApprovalController::class, 'index']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\Distributor\DistributorProcurementApprovalController::class, 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\Distributor\DistributorProcurementApprovalController::class, 'reject']);
        });

        Route::prefix('requirements')->group(function () {
            Route::get('/', [DistributorRequirementController::class, 'index']);       
            Route::post('/', [DistributorRequirementController::class, 'store']);
            
            Route::prefix('address')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Distributor\DistributorAddressController::class, 'index']);
                Route::post('/coordinates', [\App\Http\Controllers\Api\Distributor\DistributorAddressController::class, 'updateCoordinates']);
            });
            
            Route::prefix('admin')->group(function () {
                Route::get('/pending', [DistributorRequirementController::class, 'pending']);
                
                Route::put('/{id}', [DistributorRequirementController::class, 'update']);
                
                Route::get('/statistics', [DistributorRequirementController::class, 'statistics']);
            });
        });
        

        Route::get('/partnered-suppliers', [\App\Http\Controllers\Api\Distributor\PartneredSupplierController::class, 'index']);


        Route::prefix('payroll-settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Distributor\PayrollSettingController::class, 'show']);
            Route::put('/', [\App\Http\Controllers\Api\Distributor\PayrollSettingController::class, 'update']);
        });

        
        
        // Operational Distributors Routes
        Route::prefix('operational-distributors')->group(function () {
            Route::get('/', [OperationalDistributorController::class, 'index']);
            Route::get('/statistics', [OperationalDistributorController::class, 'statistics']);
            Route::post('/', [OperationalDistributorController::class, 'store']);
            Route::get('/{id}', [OperationalDistributorController::class, 'show']);
            Route::put('/{id}', [OperationalDistributorController::class, 'update']);
            Route::delete('/{id}', [OperationalDistributorController::class, 'destroy']);
            Route::post('/{id}/activate', [OperationalDistributorController::class, 'activate']);
            Route::post('/{id}/deactivate', [OperationalDistributorController::class, 'deactivate']);

            
        });

        // HR Managers Routes
        Route::prefix('hr-managers')->group(function () {
            Route::get('/', [HRManagerController::class, 'index']);
            Route::get('/statistics', [HRManagerController::class, 'statistics']);
            Route::post('/', [HRManagerController::class, 'store']);
            Route::get('/{id}', [HRManagerController::class, 'show']);
            Route::put('/{id}', [HRManagerController::class, 'update']);
            Route::delete('/{id}', [HRManagerController::class, 'destroy']);
            Route::post('/{id}/activate', [HRManagerController::class, 'activate']);
            Route::post('/{id}/deactivate', [HRManagerController::class, 'deactivate']);
            Route::post('/{id}/put-on-leave', [HRManagerController::class, 'putOnLeave']);
        });

        

        // Finance Managers Routes
        Route::prefix('finance-managers')->group(function () {
            Route::get('/', [FinanceManagerController::class, 'index']);
            Route::get('/statistics', [FinanceManagerController::class, 'statistics']);
            Route::post('/', [FinanceManagerController::class, 'store']);
            Route::get('/{id}', [FinanceManagerController::class, 'show']);
            Route::put('/{id}', [FinanceManagerController::class, 'update']);
            Route::delete('/{id}', [FinanceManagerController::class, 'destroy']);
            Route::post('/{id}/activate', [FinanceManagerController::class, 'activate']);
            Route::post('/{id}/deactivate', [FinanceManagerController::class, 'deactivate']);
            Route::post('/{id}/put-on-leave', [FinanceManagerController::class, 'putOnLeave']);
        });

        // Add this to your existing distributor routes in api.php
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'index']);
            Route::post('/', [ProductController::class, 'store']);
            Route::get('/statistics', [ProductController::class, 'statistics']);
            Route::get('/{id}', [ProductController::class, 'show']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
        });

        Route::prefix('working-hours')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Distributor\WorkingHourController::class, 'index']);
            Route::put('/', [\App\Http\Controllers\Api\Distributor\WorkingHourController::class, 'update']);
        });
        
        Route::prefix('partner-requests')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Distributor\PartnerRequestController::class, 'index']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\Distributor\PartnerRequestController::class, 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\Distributor\PartnerRequestController::class, 'reject']);
        });

        Route::prefix('procurement-fulfillment')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OperationDistributor\ProcurementReadyController::class, 'index']);
            Route::post('/{id}/op-approve', [\App\Http\Controllers\Api\OperationDistributor\ProcurementReadyController::class, 'markAsOpApproved']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\OperationDistributor\ProcurementReadyController::class, 'reject']);
        });

        Route::get('/service-providers', [\App\Http\Controllers\Api\Distributor\ServiceProviderController::class, 'index']);


        //for products deployment to cuz
        Route::prefix('deployments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Distributor\ProductDeploymentController::class, 'index']);
            Route::post('/{id}/deploy', [\App\Http\Controllers\Api\Distributor\ProductDeploymentController::class, 'deploy']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\Distributor\ProductDeploymentController::class, 'reject']);
        });

        
    });

    // Admin User Management Routes
    Route::prefix('admin')->group(function () {
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminUserController::class, 'index']);
            Route::post('/', [AdminUserController::class, 'store']);
            Route::get('/statistics', [AdminUserController::class, 'statistics']);
            Route::get('/{id}', [AdminUserController::class, 'show']);
            Route::put('/{id}', [AdminUserController::class, 'update']);
            Route::delete('/{id}', [AdminUserController::class, 'destroy']);
            Route::post('/{id}/activate', [AdminUserController::class, 'activate']);
            Route::post('/{id}/deactivate', [AdminUserController::class, 'deactivate']);
            Route::post('/{id}/change-password', [AdminUserController::class, 'changePassword']);
            
            Route::post('/{id}/approve', [AdminUserController::class, 'approve']);
            Route::post('/{id}/reject', [AdminUserController::class, 'reject']);
            
        });
    });

    // HR Employees Routes
    Route::prefix('hr')->middleware(['auth:sanctum'])->group(function () {
        Route::prefix('employees')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'index']);
            Route::get('/statistics', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'statistics']);
            Route::post('/', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'store']);
            Route::get('/{id}', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'show']);
            Route::put('/{id}', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'destroy']);
            Route::post('/{id}/regularize', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'regularize']);
            // New route for employee accessibility
            Route::get('/{id}/accessibility', [\App\Http\Controllers\Api\HR\EmployeeController::class, 'getEmployeeAccessibility']);
            // Add this route in your HR routes section
            
            
        });

        Route::prefix('payroll')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\HR\PayrollController::class, 'index']); // History
            Route::post('/calculate', [\App\Http\Controllers\Api\HR\PayrollController::class, 'calculate']); // Preview
            Route::post('/process', [\App\Http\Controllers\Api\HR\PayrollController::class, 'store']); // Save
        });

        // Attendance Requests Management
        Route::prefix('attendance-requests')->group(function () {
            Route::get('/', [AttendanceRequestController::class, 'index']); // List all requests
            Route::post('/{id}/approve', [AttendanceRequestController::class, 'approve']); // Approve request
            Route::post('/{id}/reject', [AttendanceRequestController::class, 'reject']); // Reject request
        });

        Route::prefix('positions')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\HR\PositionController::class, 'index']);
            Route::get('/statistics', [\App\Http\Controllers\Api\HR\PositionController::class, 'statistics']);
            Route::get('/departments', [\App\Http\Controllers\Api\HR\PositionController::class, 'departments']);
            Route::get('/distributors', [\App\Http\Controllers\Api\HR\PositionController::class, 'distributors']);
            Route::post('/', [\App\Http\Controllers\Api\HR\PositionController::class, 'store']);
            Route::get('/{id}', [\App\Http\Controllers\Api\HR\PositionController::class, 'show']);
            Route::put('/{id}', [\App\Http\Controllers\Api\HR\PositionController::class, 'update']);
            Route::delete('/{id}', [\App\Http\Controllers\Api\HR\PositionController::class, 'destroy']);
            Route::get('/{id}/accessibility', [\App\Http\Controllers\Api\HR\PositionController::class, 'getEmployeeAccessibility']);
            Route::get('/hr/positions/employee-accessibility/{id}', [\App\Http\Controllers\Api\HR\PositionController::class, 'getEmployeeSidebarAccessibility']);
        });

        Route::prefix('leaves')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\HR\LeaveRequestController::class, 'index']);
            Route::put('/{id}/status', [\App\Http\Controllers\Api\HR\LeaveRequestController::class, 'updateStatus']);
        });
    });

    // Procurement Requests Routes
    Route::prefix('procurement')->group(function () {
        Route::get('/requests', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'index']);
        Route::get('/available-products', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'availableProducts']);
        
        //Fetch specific products from a partnered supplier
        Route::get('/supplier-products/{supplierId}', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'supplierProducts']);
        
        Route::post('/requests', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'store']);
        Route::get('/requests/{id}', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'show']);
        Route::put('/requests/{id}', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'update']);
        Route::post('/requests/{id}/cancel', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'cancel']);
        Route::get('/form-options', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'formOptions']);
        Route::get('/statistics', [\App\Http\Controllers\Api\OperationDistributor\ProcurementController::class, 'statistics']);
    });

    // Finance Procurement Routes
    Route::prefix('finance')->middleware(['auth:sanctum'])->group(function () {
        Route::prefix('transactions')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Finance\FinanceTransactionController::class, 'index']);
            Route::post('/refund/{id}/process', [\App\Http\Controllers\Api\Finance\FinanceTransactionController::class, 'processRefund']);
            Route::post('/refund/verify-gcash', [\App\Http\Controllers\Api\Finance\FinanceTransactionController::class, 'verifyGcashPayment']);
            Route::post('/refund/{id}/reject', [\App\Http\Controllers\Api\Finance\FinanceTransactionController::class, 'rejectRefund']);
        });
        
        Route::prefix('budget-release')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Finance\ProcurementBudgetReleaseController::class, 'index']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\Finance\ProcurementBudgetReleaseController::class, 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\Finance\ProcurementBudgetReleaseController::class, 'reject']);
            Route::post('/verify-gcash', [\App\Http\Controllers\Api\Finance\ProcurementBudgetReleaseController::class, 'verifyGcashPayment']);

            
        });

        Route::prefix('procurement')->group(function () {
            Route::get('/requests', [\App\Http\Controllers\Api\Finance\FinanceProcurementController::class, 'index']);
            Route::get('/requests/{id}', [\App\Http\Controllers\Api\Finance\FinanceProcurementController::class, 'show']);
            Route::post('/requests/{id}/approve', [\App\Http\Controllers\Api\Finance\FinanceProcurementController::class, 'approve']);
            Route::post('/requests/{id}/reject', [\App\Http\Controllers\Api\Finance\FinanceProcurementController::class, 'reject']);
            Route::get('/statistics', [\App\Http\Controllers\Api\Finance\FinanceProcurementController::class, 'statistics']);
        });

        Route::prefix('payroll-requests')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Finance\PayrollRequestController::class, 'index']);
            Route::get('/{id}', [\App\Http\Controllers\Api\Finance\PayrollRequestController::class, 'show']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\Finance\PayrollRequestController::class, 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\Finance\PayrollRequestController::class, 'reject']);
        });

        Route::prefix('disbursement')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Finance\PayrollDisbursementController::class, 'index']);
            Route::post('/{id}/pay', [\App\Http\Controllers\Api\Finance\PayrollDisbursementController::class, 'markAsPaid']);
        });
    });

    // Employee Attendance Routes
    Route::prefix('employee')->group(function () {
        Route::get('/schedule', [\App\Http\Controllers\Api\Employee\AttendanceController::class, 'getSchedule']);
        
        Route::prefix('attendance')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Employee\AttendanceController::class, 'index']);
            Route::post('/clock-in', [\App\Http\Controllers\Api\Employee\AttendanceController::class, 'clockIn']);
            Route::post('/clock-out', [\App\Http\Controllers\Api\Employee\AttendanceController::class, 'clockOut']);
            
            // New Route for Requesting Adjustment
            Route::post('/request', [\App\Http\Controllers\Api\Employee\AttendanceController::class, 'submitRequest']);
        });

        Route::prefix('payroll')->group(function () {
            Route::get('/', [PayrollController::class, 'index']);
            Route::get('/{id}', [PayrollController::class, 'show']);
        });

        Route::prefix('leaves')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Employee\LeaveRequestController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Employee\LeaveRequestController::class, 'store']);
        });
    });

    Route::prefix('supplier')->group(function () {

        Route::get('/sidebar-access', [\App\Http\Controllers\Api\Supplier\SupplierSidebarController::class, 'getSidebarAccess']);

        // -----------------------------------------------------
        //  SUPPLIER PAYMENT SETTINGS SHITS DAMN
        // -----------------------------------------------------
        Route::prefix('payment-settings')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\SupplierPaymentSettingController::class, 'show']);
            Route::put('/', [\App\Http\Controllers\Api\Supplier\SupplierPaymentSettingController::class, 'update']);
        });

        Route::prefix('personnel-officers')->group(function () {
            Route::post('/', [PersonnelOfficerController::class, 'store']);
        });

        Route::prefix('requirements')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\SupplierRequirementController::class, 'index']);
            
            Route::post('/', [\App\Http\Controllers\Api\Supplier\SupplierRequirementController::class, 'store']);
            
            Route::put('/info', [\App\Http\Controllers\Api\Supplier\SupplierRequirementController::class, 'updateSupplierInfo']);

            Route::prefix('address')->group(function () {
                Route::get('/', [\App\Http\Controllers\Api\Supplier\SupplierAddressController::class, 'index']);
                Route::post('/coordinates', [\App\Http\Controllers\Api\Supplier\SupplierAddressController::class, 'updateCoordinates']);
            });
        });

        Route::prefix('distributor-requests')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\DistributorRequestController::class, 'index']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\Supplier\DistributorRequestController::class, 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\Supplier\DistributorRequestController::class, 'reject']);
        });

        Route::prefix('orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\OrderFulfillmentController::class, 'index']);
            Route::post('/{id}/confirm', [\App\Http\Controllers\Api\Supplier\OrderFulfillmentController::class, 'confirmOrder']);
        });

        Route::prefix('processing-orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\SupplierOrderProcessingController::class, 'index']);
            Route::post('/{id}/prepare', [\App\Http\Controllers\Api\Supplier\SupplierOrderProcessingController::class, 'store']);
        });

        Route::prefix('shipments')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\SupplierShipmentController::class, 'index']);
            Route::post('/{id}/ship', [\App\Http\Controllers\Api\Supplier\SupplierShipmentController::class, 'ship']);
        });

        Route::prefix('raw-materials')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\Supplier\SupplierRawMaterialController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Api\Supplier\SupplierRawMaterialController::class, 'store']);
            Route::post('/{id}', [\App\Http\Controllers\Api\Supplier\SupplierRawMaterialController::class, 'update']); 
            Route::delete('/{id}', [\App\Http\Controllers\Api\Supplier\SupplierRawMaterialController::class, 'destroy']);
        });

        Route::prefix('personnel-officer')->group(function () {
            Route::post('/personnel', [PersonnelController::class, 'store']);

            Route::get('/role-activation', [\App\Http\Controllers\Api\PersonnelOfficer\RoleActivationController::class, 'index']);
            Route::post('/role-activation/{id}/activate', [\App\Http\Controllers\Api\PersonnelOfficer\RoleActivationController::class, 'activate']);
        });
    });

    // Delivery Personnel Routing
    Route::prefix('supplier-delivery')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\SupplierDelivery\SupplierDeliveryController::class, 'index']);
        Route::post('/{id}/start', [\App\Http\Controllers\Api\SupplierDelivery\SupplierDeliveryController::class, 'startDelivery']);
        Route::post('/{id}/arrive', [\App\Http\Controllers\Api\SupplierDelivery\SupplierDeliveryController::class, 'arrive']);
        Route::post('/{id}/remit', [\App\Http\Controllers\Api\SupplierDelivery\SupplierDeliveryController::class, 'remit']); 
    });

    Route::put('/profile/supplier', [\App\Http\Controllers\Api\Supplier\SupplierRequirementController::class, 'updateSupplierInfo']);

    Route::prefix('partners')->group(function () {
        Route::get('/suppliers', [\App\Http\Controllers\Api\OperationDistributor\PartnerSupplierController::class, 'index']);
        Route::post('/request', [\App\Http\Controllers\Api\OperationDistributor\PartnerSupplierController::class, 'store']);
    });

    // ==========================================
    // E-Commerce Delivery Routes for Personnel
    // ==========================================
    Route::prefix('distributor-delivery')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\DistributorDelivery\ECommerceDeliveryController::class, 'index']);
        Route::post('/{id}/start', [\App\Http\Controllers\Api\DistributorDelivery\ECommerceDeliveryController::class, 'startDelivery']);
        Route::post('/{id}/arrive', [\App\Http\Controllers\Api\DistributorDelivery\ECommerceDeliveryController::class, 'arrive']);
        Route::post('/{id}/remit', [\App\Http\Controllers\Api\DistributorDelivery\ECommerceDeliveryController::class, 'remit']); 
    });

    Route::prefix('operation-distributor')->group(function () {
        Route::get('/sidebar-access', [\App\Http\Controllers\Api\OperationDistributor\OperationDistributorSidebarController::class, 'getSidebarAccess']);

        Route::get('/categories', [\App\Http\Controllers\Api\OperationDistributor\CategoryController::class, 'index']);
        Route::get('/categories/products', [\App\Http\Controllers\Api\OperationDistributor\CategoryController::class, 'products']);

        // -----------------------------------------------------
        //  ECOMMERCE PAYMENTS (Operational Distributor)
        // -----------------------------------------------------
        Route::prefix('payments')->group(function () {
            Route::get('/', [PaymentManagementController::class, 'index']);
        });

        // -----------------------------------------------------
        //  ECOMMERCE PAYMENT SETTINGS (Merged in PaymentManagementController)
        // -----------------------------------------------------
        Route::prefix('payment-settings')->group(function () {
            Route::get('/', [PaymentManagementController::class, 'getSettings']);
            Route::put('/', [PaymentManagementController::class, 'updateSettings']);
        });

        // -------------------------------------------------------------
        // E-Commerce Reviews Management (Operational bitch)
        // -------------------------------------------------------------
        Route::prefix('reviews')->group(function () {
            Route::get('/', [ReviewManagementController::class, 'index']);
            Route::put('/{id}/status', [ReviewManagementController::class, 'updateStatus']);
            Route::post('/{id}/respond', [ReviewManagementController::class, 'respond']);
        });

        Route::prefix('service-provider-requests')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OperationDistributor\ServiceProviderRequestController::class, 'index']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\OperationDistributor\ServiceProviderRequestController::class, 'approve']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\OperationDistributor\ServiceProviderRequestController::class, 'reject']);
        });

        Route::get('/arrived-items', [\App\Http\Controllers\Api\OperationDistributor\ArrivedItemController::class, 'index']);
        Route::post('/arrived-items/{id}/move-to-inventory', [\App\Http\Controllers\Api\OperationDistributor\ArrivedItemController::class, 'moveToInventory']);

        // EC Inventory Routes to bitch
        Route::get('/ec-inventory', [\App\Http\Controllers\Api\OperationDistributor\ECInventoryController::class, 'index']);
        Route::post('/ec-inventory/{id}/request-deployment', [\App\Http\Controllers\Api\OperationDistributor\ECInventoryController::class, 'requestDeployment']);

        Route::prefix('ecommerce-orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OperationDistributor\ECOrderController::class, 'index']);
            Route::post('/{id}/confirm', [\App\Http\Controllers\Api\OperationDistributor\ECOrderController::class, 'confirmOrder']);
        });

        Route::prefix('prepare-orders')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OperationDistributor\ECPrepareOrderController::class, 'index']);
            Route::get('/personnel', [\App\Http\Controllers\Api\OperationDistributor\ECPrepareOrderController::class, 'deliveryPersonnel']);
            Route::post('/{id}/dispatch', [\App\Http\Controllers\Api\OperationDistributor\ECPrepareOrderController::class, 'dispatchOrder']);
        });

        Route::get('/ecommerce-dashboard', [ECommerceDashboardController::class, 'index']);

        Route::prefix('returns')->group(function () {
            Route::get('/', [\App\Http\Controllers\Api\OperationDistributor\ECReturnController::class, 'index']);
            Route::post('/{id}/approve', [\App\Http\Controllers\Api\OperationDistributor\ECReturnController::class, 'approveReturn']);
            Route::post('/{id}/reject', [\App\Http\Controllers\Api\OperationDistributor\ECReturnController::class, 'rejectReturn']);
            Route::post('/{id}/receive-refund', [\App\Http\Controllers\Api\OperationDistributor\ECReturnController::class, 'receiveItemAndRequestRefund']);
            Route::get('/{id}/chat', [\App\Http\Controllers\Api\OperationDistributor\ECReturnController::class, 'getReturnChat']);
            Route::post('/{id}/chat', [\App\Http\Controllers\Api\OperationDistributor\ECReturnController::class, 'sendReturnMessage']);
        });

    });

    Route::prefix('crm')->group(function () {
        Route::get('/promotions', [\App\Http\Controllers\Api\CRM\PromotionController::class, 'index']);
        Route::post('/promotions', [\App\Http\Controllers\Api\CRM\PromotionController::class, 'store']);
        Route::get('/promotions/products', [\App\Http\Controllers\Api\CRM\PromotionController::class, 'getProducts']);
    });

    Route::prefix('crm/promotions-approval')->group(function () {
        Route::get('/', [App\Http\Controllers\Api\CRM\PromotionApprovalController::class, 'index']);
        Route::post('/{id}/approve', [App\Http\Controllers\Api\CRM\PromotionApprovalController::class, 'approve']);
        Route::post('/{id}/reject', [App\Http\Controllers\Api\CRM\PromotionApprovalController::class, 'reject']);
    });
    
});