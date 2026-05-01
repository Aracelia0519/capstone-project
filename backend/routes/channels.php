<?php

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;

// Existing general chat channel
Broadcast::channel('chat.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * Authorization for E-Commerce Return Chats
 * Allows the client, distributor, operational_distributor, or employee to listen.
 */
Broadcast::channel('return.chat.{id}', function ($user, $id) {
    // If the user is the exact client or direct distributor
    if ((int) $user->id === (int) $id) {
        return true;
    }

    // If the user is an employee, check if they belong to this distributor ID
    if (isset($user->role) && $user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int) $employee->parent_distributor_id === (int) $id) {
            return true;
        }
    }

    // If the user is an operational distributor, check if they belong to this distributor ID
    if (isset($user->role) && $user->role === 'operational_distributor') {
        $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
        if ($opDist && (int) $opDist->parent_distributor_id === (int) $id) {
            return true;
      }
    }

    return false;
});

Broadcast::channel('distributor.{distributorId}.procurement', function ($user, $distributorId) {
    // In a real scenario, you can add RBAC checks here to ensure $user belongs to $distributorId
    return true; 
});

// Allow admins to listen to the global procurement channel
Broadcast::channel('admin.procurement', function ($user) {
    return $user->role === 'admin';
});

Broadcast::channel('supplier.{supplierId}.orders', function ($user, $supplierId) {
    // Basic verification to ensure user belongs to the supplier ID
    if ($user->role === 'supplier' && (int) $user->id === (int) $supplierId) return true;
    
    if ($user->role === 'supplier_employee') {
        $personnel = DB::table('supplier_personnels')->where('user_id', $user->id)->first();
        if ($personnel && (int) $personnel->supplier_id === (int) $supplierId) return true;
    }
    
    if ($user->role === 'personnel_officer') {
        $officer = DB::table('supplier_personnel_officers')->where('user_id', $user->id)->first();
        if ($officer && (int) $officer->supplier_id === (int) $supplierId) return true;
    }

    return false;
});

// ------------- NEW INVENTORY CHANNELS -------------

Broadcast::channel('distributor.{distributorId}.inventory', function ($user, $distributorId) {
    // 1. Admin always has access
    if ($user->role === 'admin') return true;

    // 2. Direct distributor check
    if ($user->role === 'distributor' && (int)$user->id === (int)$distributorId) return true;

    // 3. Operational distributor check
    if ($user->role === 'operational_distributor') {
        $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
        if ($opDist && (int)$opDist->parent_distributor_id === (int)$distributorId) return true;
    }

    // 4. Employee check
    if ($user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int)$employee->parent_distributor_id === (int)$distributorId) return true;
    }

    // 5. HR / Finance Manager check
    if (in_array($user->role, ['hr_manager', 'finance_manager'])) {
        $table = $user->role === 'hr_manager' ? 'hr_managers' : 'finance_managers';
        $manager = DB::table($table)->where('user_id', $user->id)->first();
        if ($manager && (int)$manager->parent_distributor_id === (int)$distributorId) return true;
    }

    return false;
});

// Admin Global Channel
Broadcast::channel('admin.inventory', function ($user) {
    return $user->role === 'admin';
});


// ------------- NEW SERVICE PROVIDER CHANNELS -------------

Broadcast::channel('provider.{providerId}.requests', function ($user, $providerId) {
    // Ensures only the actual service provider or their employees can listen
    if ($user->role === 'service_provider' && (int)$user->id === (int)$providerId) return true;
    
    return false;
});

    //Client Channel Authorization
Broadcast::channel('client.{clientId}.requests', function ($user, $clientId) {
    if ($user->role === 'client' && (int)$user->id === (int)$clientId) return true;
    return false;
});


// ------------- NEW E-COMMERCE ORDER CHANNELS -------------

Broadcast::channel('distributor.{distributorId}.orders', function ($user, $distributorId) {
    if ($user->role === 'admin') return true;
    if ($user->role === 'distributor' && (int)$user->id === (int)$distributorId) return true;

    if ($user->role === 'operational_distributor') {
        $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
        if ($opDist && (int)$opDist->parent_distributor_id === (int)$distributorId) return true;
    }

    if ($user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int)$employee->parent_distributor_id === (int)$distributorId) return true;
    }

    return false;
});

Broadcast::channel('admin.orders', function ($user) {
    return $user->role === 'admin';
});

// Delivery Personnel Channel
Broadcast::channel('personnel.{personnelId}.deliveries', function ($user, $personnelId) {
    if ($user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int) $employee->id === (int) $personnelId) return true;
    }
    return false;
});


// End-User Client/Provider Order Tracking Channels
Broadcast::channel('client.{clientId}.orders', function ($user, $clientId) {
    if ($user->role === 'client' && (int)$user->id === (int)$clientId) return true;
    return false;
});

Broadcast::channel('provider.{providerId}.orders', function ($user, $providerId) {
    if ($user->role === 'service_provider' && (int)$user->id === (int)$providerId) return true;
    return false;
});


// Return Requests Channel
Broadcast::channel('distributor.{distributorId}.returns', function ($user, $distributorId) {
    if ($user->role === 'admin') return true;
    if ($user->role === 'distributor' && (int)$user->id === (int)$distributorId) return true;

    if ($user->role === 'operational_distributor') {
        $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
        if ($opDist && (int)$opDist->parent_distributor_id === (int)$distributorId) return true;
    }

    if ($user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int)$employee->parent_distributor_id === (int)$distributorId) return true;
    }

    return false;
});

// Finance Channel
Broadcast::channel('distributor.{distributorId}.finance', function ($user, $distributorId) {
    if ($user->role === 'admin') return true;
    if ($user->role === 'distributor' && (int)$user->id === (int)$distributorId) return true;

    if (in_array($user->role, ['hr_manager', 'finance_manager', 'operational_distributor'])) {
        $table = $user->role . 's';
        $manager = DB::table($table)->where('user_id', $user->id)->first();
        if ($manager && (int)$manager->parent_distributor_id === (int)$distributorId) return true;
    }

    if ($user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int)$employee->parent_distributor_id === (int)$distributorId) return true;
    }

    return false;
});

Broadcast::channel('admin.finance', function ($user) {
    return $user->role === 'admin';
});


// Reviews Channel
Broadcast::channel('distributor.{distributorId}.reviews', function ($user, $distributorId) {
    if ($user->role === 'admin') return true;
    if ($user->role === 'distributor' && (int)$user->id === (int)$distributorId) return true;

    if ($user->role === 'operational_distributor') {
        $opDist = DB::table('operational_distributors')->where('user_id', $user->id)->first();
        if ($opDist && (int)$opDist->parent_distributor_id === (int)$distributorId) return true;
    }

    if ($user->role === 'employee') {
        $employee = DB::table('hr_employees')->where('user_id', $user->id)->first();
        if ($employee && (int)$employee->parent_distributor_id === (int)$distributorId) return true;
    }

    return false;
});

Broadcast::channel('admin.reviews', function ($user) {
    return $user->role === 'admin';
});