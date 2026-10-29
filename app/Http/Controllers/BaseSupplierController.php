<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * Base Controller untuk semua Supplier Controllers
 * Menyediakan helper methods dan proteksi otomatis
 */
abstract class BaseSupplierController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * Get authenticated supplier with automatic validation
     * Throws exception if supplier is not valid
     * 
     * @return \App\Models\Supplier
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function getAuthenticatedSupplier(): Supplier
    {
        $user = auth()->user();

        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        if (!$user->isSupplier()) {
            abort(403, 'Only suppliers can access this resource');
        }

        $supplier = $user->getSupplierSafely();

        if (!$supplier) {
            \Log::error('BaseSupplierController: Supplier not found for authenticated user', [
                'user_id' => $user->id,
                'email' => $user->email,
                'supplier_id' => $user->supplier_id
            ]);

            // Force logout
            auth()->logout();
            request()->session()->invalidate();
            request()->session()->regenerateToken();

            abort(403, 'Data supplier tidak ditemukan. Silakan hubungi administrator.');
        }

        return $supplier;
    }

    /**
     * Get authenticated user
     * 
     * @return \App\Models\User
     */
    protected function getAuthenticatedUser()
    {
        return auth()->user();
    }

    /**
     * Check if authenticated user owns the document request
     * 
     * @param int $requestId
     * @return bool
     */
    protected function ownsDocumentRequest(int $requestId): bool
    {
        $supplier = $this->getAuthenticatedSupplier();
        
        return \App\Models\DocumentRequest::where('id', $requestId)
            ->where('supplier_id', $supplier->id)
            ->exists();
    }
}
