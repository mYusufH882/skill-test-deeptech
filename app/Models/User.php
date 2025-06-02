<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->user_type === 'superadmin';
    }

    /**
     * Check if user is admin (includes superadmin)
     */
    public function isAdmin(): bool
    {
        return in_array($this->user_type, ['superadmin', 'admin']);
    }

    /**
     * Check if user is regular admin (not superadmin)
     */
    public function isRegularAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Check if user is employee
     */
    public function isEmployee(): bool
    {
        return $this->user_type === 'employee';
    }

    /**
     * Check if user can manage admins (only superadmin)
     */
    public function canManageAdmins(): bool
    {
        return $this->isSuperAdmin();
    }

    /**
     * Get the admin record associated with the user.
     */
    public function admin()
    {
        return $this->hasOne(Admin::class);
    }

    /**
     * Get the employee record associated with the user.
     */
    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    /**
     * Get employees created by this admin (TAMBAHAN BARU)
     */
    public function createdEmployees()
    {
        return $this->hasMany(Employee::class, 'created_by');
    }

    /**
     * Get the profile based on user type
     */
    public function profile()
    {
        if ($this->isAdmin()) {
            return $this->admin;
        }
        return $this->employee;
    }

    /**
     * Get approved leaves (for admin users)
     */
    public function approvedLeaves()
    {
        return $this->hasMany(Leave::class, 'approved_by');
    }

    /**
     * Get count of employees accessible to this user (TAMBAHAN BARU)
     */
    public function getAccessibleEmployeesCount(): int
    {
        if ($this->isSuperAdmin()) {
            return Employee::count(); // SuperAdmin sees all
        }

        return $this->createdEmployees()->count(); // Regular admin sees only created by them
    }

    /**
     * Get employees accessible to this user (TAMBAHAN BARU)
     */
    public function getAccessibleEmployees()
    {
        if ($this->isSuperAdmin()) {
            return Employee::with('user'); // SuperAdmin sees all
        }

        return $this->createdEmployees()->with('user'); // Regular admin sees only theirs
    }
}
