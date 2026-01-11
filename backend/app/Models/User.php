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
        'address',
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
     * Check if user is a service provider
     */
    public function isServiceProvider(): bool
    {
        return $this->role === 'service_provider';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
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