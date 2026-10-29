<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Employee extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'npk',
        'division',
        'role',
        'email',
        'password',
        'is_active',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Division constants
     */
    const DIVISION_QA = 'QA';
    const DIVISION_PCC = 'PCC';
    const DIVISION_PURCHASING = 'Purchasing';

    /**
     * Role constants
     */
    const ROLE_CHECKER = 'Checker';
    const ROLE_APPROVER = 'Approver';
    const ROLE_RECEIVER = 'Receiver';

    /**
     * Get the name of the unique identifier for the user.
     * Override to use NPK instead of email
     *
     * @return string
     */
    public function getAuthIdentifierName()
    {
        return 'npk';
    }

    /**
     * Get the name of the password field used for authentication
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the column name for the "username" (NPK in this case)
     * Used by authentication
     *
     * @return string
     */
    public function username()
    {
        return 'npk';
    }

    /**
     * Check if employee is a Checker
     */
    public function isChecker(): bool
    {
        return $this->role === self::ROLE_CHECKER;
    }

    /**
     * Check if employee is an Approver
     */
    public function isApprover(): bool
    {
        return $this->role === self::ROLE_APPROVER;
    }

    /**
     * Check if employee is a Receiver (for IS documents)
     */
    public function isReceiver(): bool
    {
        return $this->role === self::ROLE_RECEIVER;
    }

    /**
     * Check if employee is Admin QA (QA division with Approver role)
     */
    public function isAdminQA(): bool
    {
        return $this->isQA() && $this->isApprover();
    }

    /**
     * Check if employee belongs to QA division
     */
    public function isQA(): bool
    {
        return $this->division === self::DIVISION_QA;
    }

    /**
     * Check if employee belongs to PCC division
     */
    public function isPCC(): bool
    {
        return $this->division === self::DIVISION_PCC;
    }

    /**
     * Check if employee belongs to Purchasing division
     */
    public function isPurchasing(): bool
    {
        return $this->division === self::DIVISION_PURCHASING;
    }

    /**
     * Get review scope for this employee based on division
     * Maps Employee division to Document Type scope
     * 
     * @return string|null 'QA', 'PURCHASING', 'PPC', or null
     */
    public function getReviewScope(): ?string
    {
        return match (strtoupper($this->division)) {
            'QA' => 'QA',
            'PURCHASING' => 'PURCHASING',
            'PCC', 'PPC' => 'PPC',
            default => null,
        };
    }

    /**
     * Get full role description
     */
    public function getFullRoleAttribute(): string
    {
        return "{$this->division} {$this->role}";
    }

    /**
     * Relation: Employee has many document approvals
     */
    public function documentApprovals()
    {
        return $this->hasMany(DocumentApproval::class);
    }

    /**
     * Relation: Employee has many document histories (as actor)
     */
    public function documentHistories()
    {
        return $this->hasMany(DocumentHistory::class, 'actor_id');
    }

    /**
     * Check if employee can create new document request
     * Only QA Checker can create requests
     */
    public function canCreateRequest(): bool
    {
        return $this->is_active && $this->isQA() && $this->isChecker();
    }

    /**
     * Check if employee can check a document based on division and role
     *
     * @param string $requiredDivision
     * @return bool
     */
    public function canCheck(string $requiredDivision): bool
    {
        return $this->is_active 
            && $this->isChecker() 
            && $this->division === $requiredDivision;
    }

    /**
     * Check if employee can approve a document based on division and role
     *
     * @param string $requiredDivision
     * @return bool
     */
    public function canApprove(string $requiredDivision): bool
    {
        return $this->is_active 
            && $this->isApprover() 
            && $this->division === $requiredDivision;
    }

    /**
     * Get pending tasks for this employee
     * Tasks are documents awaiting action from this employee's division and role
     */
    public function getPendingTasks()
    {
        // This will be implemented when we build the approval workflow
        // For now, return empty collection
        return collect([]);
    }

    /**
     * Scope: Filter active employees
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: Filter by division
     */
    public function scopeDivision($query, string $division)
    {
        return $query->where('division', $division);
    }

    /**
     * Scope: Filter by role
     */
    public function scopeRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope: Get checkers only
     */
    public function scopeCheckers($query)
    {
        return $query->where('role', self::ROLE_CHECKER);
    }

    /**
     * Scope: Get approvers only
     */
    public function scopeApprovers($query)
    {
        return $query->where('role', self::ROLE_APPROVER);
    }
}
