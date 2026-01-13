<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\Client\ClientRequirementController;
use App\Http\Controllers\Api\ServiceProvider\ServiceProviderRequirementController; // ADD THIS

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
    });

    // Client Requirements - ID Verification
    Route::prefix('client')->group(function () {
        Route::prefix('requirements')->group(function () {
            // Get client's own ID verification status
            Route::get('/', [ClientRequirementController::class, 'index']);
            
            // Submit ID verification
            Route::post('/', [ClientRequirementController::class, 'store']);
            
            // Admin routes
            Route::prefix('admin')->middleware('role:admin')->group(function () {
                // Update verification status
                Route::put('/{id}', [ClientRequirementController::class, 'update']);
                
                // Get all pending verifications
                Route::get('/pending', [ClientRequirementController::class, 'pending']);
            });
        });
    });

    // Service Provider Requirements - ID Verification (NEW)
    Route::prefix('service-provider')->group(function () {
        Route::prefix('requirements')->group(function () {
            // Get service provider's own ID verification status
            Route::get('/', [ServiceProviderRequirementController::class, 'index']);
            
            // Submit ID verification
            Route::post('/', [ServiceProviderRequirementController::class, 'store']);
            
            // Admin routes
            Route::prefix('admin')->middleware('role:admin')->group(function () {
                // Get all pending verifications
                Route::get('/pending', [ServiceProviderRequirementController::class, 'pending']);
                
                // Update verification status
                Route::put('/{id}', [ServiceProviderRequirementController::class, 'update']);
                
                // Get verification statistics
                Route::get('/statistics', [ServiceProviderRequirementController::class, 'statistics']);
            });
        });
    });
});