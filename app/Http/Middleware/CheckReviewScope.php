<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\RequestDocument;

class CheckReviewScope
{
    /**
     * Handle an incoming request untuk memastikan user hanya bisa 
     * mengakses dokumen sesuai scope mereka
     * Supports both Employee (new system) and User (legacy) authentication
     */
    public function handle(Request $request, Closure $next)
    {
        // Check Employee authentication first (new system)
        if (auth()->guard('employee')->check()) {
            $employee = auth()->guard('employee')->user();
            
            // Jika ada parameter requestDocument dalam route
            $requestDocument = $request->route('requestDocument');
            
            if ($requestDocument && $requestDocument instanceof RequestDocument) {
                // Cek apakah employee memiliki akses untuk review dokumen ini
                $employeeCanReview = $this->employeeCanReviewDocument($employee, $requestDocument);
                
                if (!$employeeCanReview) {
                    \Log::warning('[CheckReviewScope] Employee denied access', [
                        'npk' => $employee->npk,
                        'division' => $employee->division,
                        'role' => $employee->role,
                        'document_id' => $requestDocument->id,
                        'document_type' => $requestDocument->documentType->code,
                    ]);
                    abort(403, 'Anda tidak memiliki akses untuk mereview dokumen dengan scope ini');
                }
            }
            
            return $next($request);
        }
        
        // Fallback to legacy User authentication
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();

        // Jika ada parameter requestDocument dalam route
        $requestDocument = $request->route('requestDocument');
        
        if ($requestDocument && $requestDocument instanceof RequestDocument) {
            // Cek apakah user memiliki akses untuk review dokumen ini
            $userCanReview = $this->userCanReviewDocument($user, $requestDocument);
            
            if (!$userCanReview) {
                abort(403, 'Anda tidak memiliki akses untuk mereview dokumen dengan scope ini');
            }
        }

        return $next($request);
    }

    /**
     * Check apakah employee memiliki akses untuk review dokumen ini berdasarkan scope
     * Employee system: division (QA/Purchasing/PCC) + role (Checker/Approver)
     */
    private function employeeCanReviewDocument($employee, RequestDocument $requestDocument): bool
    {
        // Get document scopes (e.g., ['QA', 'PURCHASING', 'PPC'])
        $scopes = $requestDocument->documentType->scope ?? [];

        // Map employee division ke document scope
        $employeeScope = match (strtoupper($employee->division)) {
            'QA' => 'QA',
            'PURCHASING' => 'PURCHASING',
            'PCC', 'PPC' => 'PPC',
            default => null,
        };

        if (!$employeeScope) {
            \Log::warning('[CheckReviewScope] Employee has invalid division', [
                'npk' => $employee->npk,
                'division' => $employee->division,
            ]);
            return false;
        }

        // Check if employee's division matches any of the document's required scopes
        $hasAccess = in_array($employeeScope, $scopes);
        
        \Log::info('[CheckReviewScope] Employee scope check', [
            'npk' => $employee->npk,
            'division' => $employee->division,
            'role' => $employee->role,
            'employee_scope' => $employeeScope,
            'document_scopes' => $scopes,
            'has_access' => $hasAccess,
        ]);
        
        return $hasAccess;
    }

    /**
     * Check apakah user memiliki akses untuk review dokumen ini berdasarkan scope
     * Legacy User system
     */
    private function userCanReviewDocument($user, RequestDocument $requestDocument): bool
    {
        // Get document scopes
        $scopes = $requestDocument->documentType->scope ?? [];

        // Map user roles ke scopes sesuai Implementation Document
        $userScope = match ($user->role) {
            'admin_qa' => 'QA',
            'admin_purchasing' => 'PURCHASING', 
            'admin_ppc' => 'PPC',
            default => null,
        };

        if (!$userScope) {
            return false;
        }

        return in_array($userScope, $scopes);
    }
}
