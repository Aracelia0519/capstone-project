<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Distributor\DistributorRequirements;
use App\Models\Distributor\OperationalDistributor;
use App\Models\Distributor\HRManager;
use App\Models\Finance\FinanceManager;
use App\Models\Client\ClientRequirement;
use App\Models\ServiceProvider\ServiceProviderRequirement;
use App\Models\Supplier\SupplierRequirements;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class AdminUserController extends Controller
{
    /**
     * Get all users with their verification status
     */
    public function index(Request $request)
    {
        try {
            // Check if user is admin
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $query = User::query();
            
            // Apply filters
            if ($request->has('role') && $request->role !== 'all') {
                $query->where('role', $request->role);
            }
            
            if ($request->has('status') && $request->status !== 'all') {
                $query->where('status', $request->status);
            }
            
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            // Get paginated results
            // UPDATED SORTING LOGIC: Pending users first, then by date descending
            $perPage = $request->get('per_page', 10);
            
            $users = $query
                ->orderByRaw("CASE WHEN status = 'pending' THEN 1 ELSE 2 END ASC")
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);
            
            // Transform users with additional data
            $transformedUsers = $users->getCollection()->map(function ($user) {
                return $this->transformUser($user);
            });
            
            return response()->json([
                'success' => true,
                'users' => $transformedUsers,
                'pagination' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                    'from' => $users->firstItem(),
                    'to' => $users->lastItem(),
                ],
                'statistics' => $this->getUserStatistics()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user statistics
     */
    private function getUserStatistics()
    {
        return [
            'total' => User::count(),
            'admin' => User::where('role', 'admin')->count(),
            'distributor' => User::where('role', 'distributor')->count(),
            'service_provider' => User::where('role', 'service_provider')->count(),
            'client' => User::where('role', 'client')->count(),
            'supplier' => User::where('role', 'supplier')->count(), 
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'pending' => User::where('status', 'pending')->count(),
        ];
    }

    /**
     * Get user statistics (public method for API)
     */
    public function statistics()
    {
        try {
            // Check if user is admin
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            return response()->json([
                'success' => true,
                'statistics' => $this->getUserStatistics()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Transform user data with additional information
     */
    private function transformUser(User $user)
    {
        $verificationStatus = null;
        $verificationDetails = null;
        
        // Get verification status based on user role
        switch ($user->role) {
            case 'distributor':
                $verificationStatus = $user->getDistributorVerificationStatus();
                if ($user->distributorRequirement) {
                    $verificationDetails = $this->getDistributorRequirements($user->distributorRequirement);
                }
                break;
                
            case 'client':
                $verificationStatus = $user->getIdVerificationStatus();
                if ($user->clientRequirement) {
                    $verificationDetails = $this->getClientRequirements($user->clientRequirement);
                }
                break;
                
            case 'service_provider':
                if ($user->serviceProviderRequirement) {
                    $verificationStatus = $user->serviceProviderRequirement->status;
                    $verificationDetails = $this->getServiceProviderRequirements($user->serviceProviderRequirement);
                }
                break;

            case 'supplier': 
                $supplierRequirement = SupplierRequirements::where('user_id', $user->id)->first();
                if ($supplierRequirement) {
                    $verificationStatus = $supplierRequirement->status;
                    $verificationDetails = $this->getSupplierRequirements($supplierRequirement);
                }
                break;
        }
        
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'email' => $user->email,
            'phone' => $user->phone,
            'address' => $user->address,
            'role' => $user->role,
            'role_display' => $this->getRoleDisplayName($user->role),
            'status' => $user->status,
            'status_display' => ucfirst($user->status),
            'verification_status' => $verificationStatus,
            'verification_details' => $verificationDetails,
            'created_at' => $user->created_at->format('M d, Y'),
            'created_at_raw' => $user->created_at,
            'updated_at' => $user->updated_at,
            'avatar_color' => $this->getAvatarColor($user->role),
            'initials' => strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)),
        ];
    }

    /**
     * Get distributor requirements with URLs
     */
    private function getDistributorRequirements(DistributorRequirements $requirement)
    {
        return [
            'id' => $requirement->id,
            'company_name' => $requirement->company_name,
            'valid_id_type' => $requirement->valid_id_type,
            'valid_id_type_display' => $requirement->getIdTypeNameAttribute(),
            'id_number' => $requirement->id_number,
            'valid_id_photo' => $requirement->valid_id_photo,
            'valid_id_photo_url' => $requirement->valid_id_photo ? Storage::url($requirement->valid_id_photo) : null,
            'dti_certificate_photo' => $requirement->dti_certificate_photo,
            'dti_certificate_photo_url' => $requirement->dti_certificate_photo ? Storage::url($requirement->dti_certificate_photo) : null,
            'mayor_permit_photo' => $requirement->mayor_permit_photo,
            'mayor_permit_photo_url' => $requirement->mayor_permit_photo ? Storage::url($requirement->mayor_permit_photo) : null,
            'barangay_clearance_photo' => $requirement->barangay_clearance_photo,
            'barangay_clearance_photo_url' => $requirement->barangay_clearance_photo ? Storage::url($requirement->barangay_clearance_photo) : null,
            'business_registration_number' => $requirement->business_registration_number,
            'business_registration_photo' => $requirement->business_registration_photo,
            'business_registration_photo_url' => $requirement->business_registration_photo ? Storage::url($requirement->business_registration_photo) : null,
            'status' => $requirement->status,
            'rejection_reason' => $requirement->rejection_reason,
            'submitted_at' => $requirement->created_at,
            'reviewed_at' => $requirement->updated_at,
            'is_complete' => $requirement->getIsCompleteAttribute(),
        ];
    }

    /**
     * Get supplier requirements with URLs and Address
     */
    private function getSupplierRequirements(SupplierRequirements $requirement)
    {
        $requirement->load('address'); 
        
        return [
            'id' => $requirement->id,
            'company_name' => $requirement->company_name,
            'valid_id_type' => $requirement->valid_id_type,
            'valid_id_type_display' => $requirement->getIdTypeNameAttribute(),
            'id_number' => $requirement->id_number,
            'business_registration_number' => $requirement->business_registration_number,
            
            'valid_id_photo' => $requirement->valid_id_photo,
            'valid_id_photo_url' => $requirement->valid_id_photo ? Storage::url($requirement->valid_id_photo) : null,
            
            'dti_certificate_photo' => $requirement->dti_certificate_photo,
            'dti_certificate_photo_url' => $requirement->dti_certificate_photo ? Storage::url($requirement->dti_certificate_photo) : null,
            
            'mayor_permit_photo' => $requirement->mayor_permit_photo,
            'mayor_permit_photo_url' => $requirement->mayor_permit_photo ? Storage::url($requirement->mayor_permit_photo) : null,
            
            'barangay_clearance_photo' => $requirement->barangay_clearance_photo,
            'barangay_clearance_photo_url' => $requirement->barangay_clearance_photo ? Storage::url($requirement->barangay_clearance_photo) : null,
            
            'business_registration_photo' => $requirement->business_registration_photo,
            'business_registration_photo_url' => $requirement->business_registration_photo ? Storage::url($requirement->business_registration_photo) : null,
            
            'address' => $requirement->address,
            
            'status' => $requirement->status,
            'rejection_reason' => $requirement->rejection_reason,
            'submitted_at' => $requirement->created_at,
            'reviewed_at' => $requirement->updated_at,
            'is_complete' => $requirement->is_complete,
        ];
    }

    /**
     * Get client requirements with URLs
     */
    private function getClientRequirements(ClientRequirement $requirement)
    {
        return [
            'id' => $requirement->id,
            'valid_id_type' => $requirement->valid_id_type,
            'valid_id_type_display' => $requirement->getIdTypeNameAttribute(),
            'id_number' => $requirement->id_number,
            'valid_id_photo' => $requirement->valid_id_photo,
            'valid_id_photo_url' => $requirement->valid_id_photo ? Storage::url($requirement->valid_id_photo) : null,
            'status' => $requirement->status,
            'rejection_reason' => $requirement->rejection_reason,
            'submitted_at' => $requirement->created_at,
            'reviewed_at' => $requirement->updated_at,
        ];
    }

    /**
     * Get service provider requirements with URLs
     */
    private function getServiceProviderRequirements(ServiceProviderRequirement $requirement)
    {
        return [
            'id' => $requirement->id,
            'valid_id_type' => $requirement->valid_id_type,
            'valid_id_type_display' => $requirement->getReadableIdType(),
            'id_number' => $requirement->id_number,
            'valid_id_photo' => $requirement->valid_id_photo,
            'valid_id_photo_url' => $requirement->getIdPhotoUrl(),
            'selfie_with_id_photo' => $requirement->selfie_with_id_photo,
            'selfie_with_id_photo_url' => $requirement->getSelfiePhotoUrl(),
            'status' => $requirement->status,
            'rejection_reason' => $requirement->rejection_reason,
            'submitted_at' => $requirement->submitted_at,
            'reviewed_at' => $requirement->reviewed_at,
        ];
    }

    /**
     * Get role display name
     */
    private function getRoleDisplayName($role)
    {
        $roleNames = [
            'admin' => 'Administrator',
            'distributor' => 'Distributor',
            'service_provider' => 'Service Provider',
            'client' => 'Client',
            'supplier' => 'Supplier', 
        ];
        
        return $roleNames[$role] ?? ucfirst(str_replace('_', ' ', $role));
    }

    /**
     * Get avatar color based on role
     */
    private function getAvatarColor($role)
    {
        $colors = [
            'admin' => '#4A90E2',
            'distributor' => '#51C16B',
            'service_provider' => '#9B59B6',
            'client' => '#7F8C8D',
            'supplier' => '#FF7043', 
        ];
        
        return $colors[$role] ?? '#95A5A6';
    }

    /**
     * Get single user by ID with full requirements
     */
    public function show($id)
    {
        try {
            // Check if user is admin
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $user = User::with([
                'distributorRequirement',
                'clientRequirement',
                'serviceProviderRequirement'
            ])->find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Get detailed requirements based on role
            $requirements = null;
            switch ($user->role) {
                case 'distributor':
                    if ($user->distributorRequirement) {
                        $requirements = [
                            'distributor' => $this->getDistributorRequirements($user->distributorRequirement)
                        ];
                    }
                    break;
                    
                case 'client':
                    if ($user->clientRequirement) {
                        $requirements = [
                            'client' => $this->getClientRequirements($user->clientRequirement)
                        ];
                    }
                    break;
                    
                case 'service_provider':
                    if ($user->serviceProviderRequirement) {
                        $requirements = [
                            'service_provider' => $this->getServiceProviderRequirements($user->serviceProviderRequirement)
                        ];
                    }
                    break;

                case 'supplier':
                    $supplierRequirement = SupplierRequirements::where('user_id', $user->id)->first();
                    if ($supplierRequirement) {
                         $requirements = [
                            'supplier' => $this->getSupplierRequirements($supplierRequirement)
                        ];
                    }
                    break;
            }
            
            return response()->json([
                'success' => true,
                'user' => $this->transformUser($user),
                'requirements' => $requirements
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new user
     */
    public function store(Request $request)
    {
        try {
            // Check if user is admin
            $user = Auth::user();
            if ($user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                    'regex:/[0-9]/',      // must contain at least one digit
                    'regex:/[@$!%*#?&]/', // must contain a special character
                    'confirmed'           // ensure password match
                ],
                'role' => 'required|in:admin,distributor,service_provider,client,supplier', // removed unnecessary roles
                'phone' => 'required|string|max:20|regex:/^09\d{9}$/', // Must be proper PH length without spaces
                'address' => 'nullable|string',
                'status' => 'in:pending,active,inactive',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = User::create([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                // Passing Raw request password because the model automatically hashes it.
                'password' => $request->password, 
                'role' => $request->role,
                'phone' => $request->phone,
                'address' => $request->address,
                'status' => $request->status ?? 'active',
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => $this->transformUser($user)
            ], 201);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user
     */
    public function destroy($id)
    {
        try {
            // Check if user is admin
            $authUser = Auth::user();
            if ($authUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Check if user is admin (prevent deleting all admins)
            if ($user->isAdmin()) {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete the only admin user'
                    ], 400);
                }
            }
            
            $user->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Activate user
     */
    public function activate($id)
    {
        try {
            // Check if user is admin
            $authUser = Auth::user();
            if ($authUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $user->activate();
            
            return response()->json([
                'success' => true,
                'message' => 'User activated successfully',
                'user' => $this->transformUser($user)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to activate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Deactivate user
     */
    public function deactivate($id)
    {
        try {
            // Check if user is admin
            $authUser = Auth::user();
            if ($authUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $user->deactivate();
            
            return response()->json([
                'success' => true,
                'message' => 'User deactivated successfully',
                'user' => $this->transformUser($user)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to deactivate user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve user and their requirements
     */
    public function approve($id)
    {
        try {
            // Check if user is admin
            $authUser = Auth::user();
            if ($authUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Activate user account
            $user->activate();
            
            // Approve requirements based on user role
            switch ($user->role) {
                case 'distributor':
                    if ($user->distributorRequirement) {
                        $user->distributorRequirement->update([
                            'status' => 'approved',
                            'rejection_reason' => null
                        ]);
                    }
                    break;
                    
                case 'client':
                    if ($user->clientRequirement) {
                        $user->clientRequirement->update([
                            'status' => 'approved',
                            'rejection_reason' => null
                        ]);
                    }
                    break;
                    
                case 'service_provider':
                    if ($user->serviceProviderRequirement) {
                        $user->serviceProviderRequirement->update([
                            'status' => 'verified',
                            'rejection_reason' => null
                        ]);
                    }
                    break;
                
                case 'supplier': 
                    $supplierRequirement = SupplierRequirements::where('user_id', $user->id)->first();
                    if ($supplierRequirement) {
                        $supplierRequirement->update([
                            'status' => 'approved',
                            'rejection_reason' => null
                        ]);
                    }
                    break;
            }

            // Send Email Notification
            try {
                Mail::raw("Hello {$user->first_name},\n\nYour account and requirements have been successfully approved! You can now fully access the CaviteGoPaint system.\n\nThank you,\nCaviteGoPaint Team", function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Account Approved - CaviteGoPaint');
                });
            } catch (\Exception $e) {
                Log::error('Approval email sending failed: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'User and requirements approved successfully',
                'user' => $this->transformUser($user)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject user with reason
     */
    public function reject(Request $request, $id)
    {
        try {
            // Check if user is admin
            $authUser = Auth::user();
            if ($authUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $validator = Validator::make($request->all(), [
                'reason' => 'required|string|max:500',
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            // Deactivate user account
            $user->deactivate();
            
            // Reject requirements based on user role
            switch ($user->role) {
                case 'distributor':
                    if ($user->distributorRequirement) {
                        $user->distributorRequirement->update([
                            'status' => 'rejected',
                            'rejection_reason' => $request->reason
                        ]);
                    }
                    break;
                    
                case 'client':
                    if ($user->clientRequirement) {
                        $user->clientRequirement->update([
                            'status' => 'rejected',
                            'rejection_reason' => $request->reason
                        ]);
                    }
                    break;
                    
                case 'service_provider':
                    if ($user->serviceProviderRequirement) {
                        $user->serviceProviderRequirement->update([
                            'status' => 'rejected',
                            'rejection_reason' => $request->reason
                        ]);
                    }
                    break;

                case 'supplier': 
                    $supplierRequirement = SupplierRequirements::where('user_id', $user->id)->first();
                    if ($supplierRequirement) {
                        $supplierRequirement->update([
                            'status' => 'rejected',
                            'rejection_reason' => $request->reason
                        ]);
                    }
                    break;
            }

            // Send Rejection Email Notification
            try {
                Mail::raw("Hello {$user->first_name},\n\nWe regret to inform you that your account or submitted requirements have been rejected.\n\nReason for rejection:\n{$request->reason}\n\nPlease contact support or re-submit your requirements if applicable.\n\nThank you,\nCaviteGoPaint Team", function ($message) use ($user) {
                    $message->to($user->email)
                            ->subject('Account Status Update - CaviteGoPaint');
                });
            } catch (\Exception $e) {
                Log::error('Rejection email sending failed: ' . $e->getMessage());
            }
            
            return response()->json([
                'success' => true,
                'message' => 'User rejected successfully',
                'user' => $this->transformUser($user)
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Change user password
     */
    public function changePassword(Request $request, $id)
    {
        try {
            // Check if user is admin
            $authUser = Auth::user();
            if ($authUser->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access. Admin privileges required.'
                ], 403);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }
            
            $validator = Validator::make($request->all(), [
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[a-z]/',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                    'confirmed'
                ],
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation error',
                    'errors' => $validator->errors()
                ], 422);
            }
            
            $user->update([
                'password' => $request->password // No hashing necessary, model casts
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}