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
     */
    public function handle(Request $request, Closure $next)
    {
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
     * Check apakah user memiliki akses untuk review dokumen ini berdasarkan scope
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