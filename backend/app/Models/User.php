<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'full_name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'email_verified_at',
        'remember_token',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Automatically hash the password when setting it
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Hash::make($value),
        );
    }

    /**
     * Get the user's full name
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => trim($this->first_name . ' ' . $this->last_name),
        );
    }

    /**
     * Check if user is a client
     */
    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    /**
     * Check if user is a distributor
     */
    public function isDistributor(): bool
    {
        return $this->role === 'distributor';
    }

    /**
     * Check if user is an operational distributor
     */
    public function isOperationalDistributor(): bool
    {
        return $this->role === 'operational_distributor';
    }

    /**
     * Check if user is a service provider
     */
    public function isServiceProvider(): bool
    {
        return $this->role === 'service_provider';
    }
    
    /**
     * Check if user is a supplier
     */
    public function isSupplier(): bool
    {
        return $this->role === 'supplier';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an employee
     */
    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    /**
     * Check if user is an HR manager
     */
    public function isHRManager(): bool
    {
        return $this->role === 'hr_manager';
    }

    /**
     * Check if user is a finance manager
     */
    public function isFinanceManager(): bool
    {
        return $this->role === 'finance_manager';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if user is pending verification
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Get the client requirement for this user (if they are a client)
     */
    public function clientRequirement()
    {
        return $this->hasOne(Client\ClientRequirement::class);
    }

    /**
     * Get the distributor requirement for this user (if they are a distributor)
     */
    public function distributorRequirement()
    {
        return $this->hasOne(Distributor\DistributorRequirements::class);
    }

    /**
     * Get the operational distributor record (if this user is an operational distributor)
     */
    public function operationalDistributor()
    {
        return $this->hasOne(Distributor\OperationalDistributor::class, 'user_id');
    }

    /**
     * Get the HR manager record (if this user is an HR manager)
     */
    public function hrManager()
    {
        return $this->hasOne(Distributor\HRManager::class, 'user_id');
    }

    /**
     * Get the finance manager record (if this user is a finance manager)
     */
    public function financeManager()
    {
        return $this->hasOne(Finance\FinanceManager::class, 'user_id');
    }

    /**
     * Get the employee record (if this user is an employee)
     */
    public function employee()
    {
        return $this->hasOne(HR\Employee::class, 'user_id');
    }

    /**
     * Get the service provider requirement for this user (if they are a service provider)
     */
    public function serviceProviderRequirement()
    {
        return $this->hasOne(ServiceProvider\ServiceProviderRequirement::class);
    }
    
    /**
     * Check if distributor is verified
     */
    public function isDistributorVerified(): bool
    {
        if (!$this->isDistributor()) {
            return false;
        }
        
        $requirement = $this->distributorRequirement;
        return $requirement && $requirement->status === 'approved';
    }

    /**
     * Get distributor verification status
     */
    public function getDistributorVerificationStatus(): ?string
    {
        if (!$this->isDistributor()) {
            return null;
        }
        
        return $this->distributorRequirement?->status;
    }

    /**
     * Check if user has submitted ID verification
     */
    public function hasIdVerification(): bool
    {
        return $this->clientRequirement()->exists();
    }

    /**
     * Get ID verification status
     */
    public function getIdVerificationStatus(): ?string
    {
        return $this->clientRequirement?->status;
    }

    /**
     * Check if ID verification is approved
     */
    public function isIdVerified(): bool
    {
        return $this->getIdVerificationStatus() === 'approved';
    }

    /**
     * Check if ID verification is pending
     */
    public function isIdVerificationPending(): bool
    {
        return $this->getIdVerificationStatus() === 'pending';
    }

    /**
     * Activate user account
     */
    public function activate(): void
    {
        $this->update(['status' => 'active']);
    }

    /**
     * Deactivate user account
     */
    public function deactivate(): void
    {
        $this->update(['status' => 'inactive']);
    }

    /**
     * Create personal access token and store it in remember_token
     */
    public function createPersonalToken($remember = false): string
    {
        // Create Sanctum token
        $token = $this->createToken('auth_token')->plainTextToken;
        
        // Store token in remember_token for session management
        if ($remember) {
            $this->update(['remember_token' => $token]);
        }
        
        return $token;
    }

    /**
     * Revoke all tokens
     */
    public function revokeTokens(): void
    {
        $this->tokens()->delete();
        $this->update(['remember_token' => null]);
    }
}