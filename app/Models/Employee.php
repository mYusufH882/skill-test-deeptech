<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Employee extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'gender',
        'created_by', // TAMBAHAN BARU
    ];

    /**
     * Get the user that owns the employee.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who created this employee.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the leaves for the employee.
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get full name attribute
     */
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Get approved leaves
     */
    public function approvedLeaves()
    {
        return $this->leaves()->where('status', 'approved');
    }

    /**
     * Get total leave days used this year
     */
    public function getTotalLeaveDaysThisYear(): int
    {
        $currentYear = Carbon::now()->year;

        return $this->approvedLeaves()
            ->whereYear('start_date', $currentYear)
            ->get()
            ->sum(function ($leave) {
                return $leave->start_date->diffInDays($leave->end_date) + 1;
            });
    }

    /**
     * Get remaining leave days this year
     */
    public function getRemainingLeaveDays(): int
    {
        return max(0, 12 - $this->getTotalLeaveDaysThisYear());
    }

    /**
     * Check if employee can take leave in specific month
     */
    public function canTakeLeaveInMonth(int $year, int $month): bool
    {
        return !$this->approvedLeaves()
            ->whereYear('start_date', $year)
            ->whereMonth('start_date', $month)
            ->exists();
    }

    /**
     * Scope for filtering employees by creator (for admin ownership)
     */
    public function scopeForAdmin($query, $adminId)
    {
        return $query->where('created_by', $adminId);
    }

    /**
     * Scope for admin access (SuperAdmin sees all, regular admin sees only theirs)
     */
    public function scopeAccessibleBy($query, $user)
    {
        if ($user->isSuperAdmin()) {
            return $query; // SuperAdmin sees all
        }

        return $query->where('created_by', $user->id); // Regular admin sees only theirs
    }
}
