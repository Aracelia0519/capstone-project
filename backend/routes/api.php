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


// Public routes
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/check-email', [AuthController::class, 'checkEmail']);
});

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
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
        Route::get('/', [UserController::class, 'index'])->middleware('role:admin');
        Route::get('/{id}', [UserController::class, 'show'])->middleware('role:admin');
        Route::put('/{id}', [UserController::class, 'update'])->middleware('role:admin');
        Route::delete('/{id}', [UserController::class, 'destroy'])->middleware('role:admin');
        Route::post('/{id}/activate', [UserController::class, 'activate'])->middleware('role:admin');
        Route::post('/{id}/deactivate', [UserController::class, 'deactivate'])->middleware('role:admin');
    });

    // Profile routes (for all authenticated users)
    Route::prefix('profile')->group(function () {
        Route::get('/', [UserController::class, 'profile']);
        Route::put('/', [UserController::class, 'updateProfile']);
        Route::put('/password', [UserController::class, 'updatePassword']);
        // NEW: Distributor profile update route
        //Route::put('/distributor', [DistributorRequirementController::class, 'updateDistributorInfo']);
    });

    // Client Requirements - ID Verification
    Route::prefix('client')->group(function () {
        Route::prefix('requirements')->group(function () {
            Route::get('/', [ClientRequirementController::class, 'index']);
            Route::post('/', [ClientRequirementController::class, 'store']);
            
            Route::prefix('admin')->middleware('role:admin')->group(function () {
                Route::put('/{id}', [ClientRequirementController::class, 'update']);
                Route::get('/pending', [ClientRequirementController::class, 'pending']);
            });
        });
    });

    // Service Provider Requirements - ID Verification
    Route::prefix('service-provider')->group(function () {
        Route::prefix('requirements')->group(function () {
            Route::get('/', [ServiceProviderRequirementController::class, 'index']);
            Route::post('/', [ServiceProviderRequirementController::class, 'store']);
            
            Route::prefix('admin')->middleware('role:admin')->group(function () {
                Route::get('/pending', [ServiceProviderRequirementController::class, 'pending']);
                Route::put('/{id}', [ServiceProviderRequirementController::class, 'update']);
                Route::get('/statistics', [ServiceProviderRequirementController::class, 'statistics']);
            });
        });
    });

    // Distributor Requirements - Business Verification (NEW)
    Route::prefix('distributor')->group(function () {
        Route::prefix('requirements')->group(function () {
            // Get distributor's own business verification status
            Route::get('/', [DistributorRequirementController::class, 'index']);
            
            // Submit business verification
            Route::post('/', [DistributorRequirementController::class, 'store']);
            
            // Admin routes
            Route::prefix('admin')->middleware('role:admin')->group(function () {
                // Get all pending verifications
                Route::get('/pending', [DistributorRequirementController::class, 'pending']);
                
                // Update verification status
                Route::put('/{id}', [DistributorRequirementController::class, 'update']);
                
                // Get verification statistics
                Route::get('/statistics', [DistributorRequirementController::class, 'statistics']);
            });
        });
        
        // NEW: Operational Distributors Routes
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

        // Add to the distributor routes group (around line 139):

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
    });

});