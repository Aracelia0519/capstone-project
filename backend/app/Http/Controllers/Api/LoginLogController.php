<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LoginLog;
use Illuminate\Http\Request;

class LoginLogController extends Controller
{
    /**
     * Fetch all login logs.
     */
    public function index()
    {
        $logs = LoginLog::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $logs
        ]);
    }
}