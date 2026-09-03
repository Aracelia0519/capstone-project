<?php

namespace App\Http\Controllers\Api\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\PwdApplicationSubmitted; // Import event

class PwdApplicationController extends Controller
{
    public function show(Request $request)
    {
        $application = DB::table('pwd_applications')
            ->where('user_id', Auth::id())
            ->first();

        if (!$application) {
            return response()->json(['status' => 'unsubmitted']);
        }

        return response()->json([
            'status' => $application->status,
            'data' => $application
        ]);
    }

    public function store(Request $request)
    {
        $existing = DB::table('pwd_applications')
            ->where('user_id', Auth::id())
            ->first();

        if ($existing && in_array($existing->status, ['pending', 'verified'])) {
            return response()->json(['message' => 'Application already exists and is under review or verified.'], 400);
        }

        $request->validate([
            'full_name' => 'required|string|max:255',
            'dob' => 'required|date',
            'id_number' => 'required|string|max:255',
            'disability_type' => 'required|string|max:255',
            'issuing_lgu' => 'required|string|max:255',
            'expiration_date' => 'required|date',
            'photo_front' => $existing ? 'nullable|image|mimes:jpeg,png,jpg|max:5120' : 'required|image|mimes:jpeg,png,jpg|max:5120',
            'photo_back' => $existing ? 'nullable|image|mimes:jpeg,png,jpg|max:5120' : 'required|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        $updateData = [
            'full_name' => $request->full_name,
            'dob' => $request->dob,
            'id_number' => $request->id_number,
            'disability_type' => $request->disability_type,
            'issuing_lgu' => $request->issuing_lgu,
            'expiration_date' => $request->expiration_date,
            'status' => 'pending',
            'rejection_reason' => null, 
            'updated_at' => now(),
        ];

        if ($request->hasFile('photo_front')) {
            $updateData['photo_front_path'] = $request->file('photo_front')->store('pwd_ids', 'public');
        }
        if ($request->hasFile('photo_back')) {
            $updateData['photo_back_path'] = $request->file('photo_back')->store('pwd_ids', 'public');
        }

        DB::table('pwd_applications')->updateOrInsert(
            ['user_id' => Auth::id()],
            $updateData
        );

        // Fire the real-time event for the admin dashboard
        event(new PwdApplicationSubmitted(Auth::id(), $request->full_name));

        return response()->json([
            'status' => 'success',
            'message' => 'PWD Application submitted successfully'
        ]);
    }
}