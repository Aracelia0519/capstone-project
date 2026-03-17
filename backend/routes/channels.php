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