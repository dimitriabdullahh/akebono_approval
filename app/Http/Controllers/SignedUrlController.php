<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\DocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Log;

class SignedUrlController extends Controller
{
    /**
     * Handle auto-login from signed URL and redirect to document upload page
     */
    public function autoLogin(Request $request, $userId, $requestId)
    {
        try {
            // Verify the signed URL
            if (!$request->hasValidSignature()) {
                Log::warning('Invalid signed URL attempt', [
                    'user_id' => $userId,
                    'request_id' => $requestId,
                    'ip' => $request->ip()
                ]);
                
                return redirect()->route('login')
                    ->with('error', 'Link tidak valid atau sudah kadaluarsa. Silakan login secara manual.');
            }

            // Find the user
            $user = User::find($userId);
            if (!$user) {
                Log::error('User not found for signed URL', ['user_id' => $userId]);
                return redirect()->route('login')
                    ->with('error', 'User tidak ditemukan. Silakan hubungi administrator.');
            }

            // Verify user is a supplier
            if ($user->role !== 'supplier') {
                Log::warning('Non-supplier attempted auto-login', [
                    'user_id' => $userId,
                    'role' => $user->role
                ]);
                return redirect()->route('login')
                    ->with('error', 'Akses ditolak. Link ini hanya untuk supplier.');
            }

            // Verify the document request exists and belongs to this supplier
            $documentRequest = DocumentRequest::find($requestId);
            if (!$documentRequest) {
                Log::error('Document request not found', ['request_id' => $requestId]);
                return redirect()->route('login')
                    ->with('error', 'Request dokumen tidak ditemukan.');
            }

            if ($documentRequest->supplier_id !== $user->supplier_id) {
                Log::warning('Supplier attempted to access another supplier\'s request', [
                    'user_id' => $userId,
                    'supplier_id' => $user->supplier_id,
                    'request_supplier_id' => $documentRequest->supplier_id,
                    'request_id' => $requestId
                ]);
                return redirect()->route('login')
                    ->with('error', 'Anda tidak memiliki akses ke request dokumen ini.');
            }

            // Log the user in
            Auth::login($user);
            
            Log::info('Supplier auto-login successful', [
                'user_id' => $userId,
                'supplier_id' => $user->supplier_id,
                'request_id' => $requestId
            ]);

            // Redirect to the document request page
            return redirect()->route('supplier.request', ['requestId' => $requestId])
                ->with('success', 'Selamat datang, ' . $user->name . '! Anda telah berhasil login.');

        } catch (\Exception $e) {
            Log::error('Error in auto-login', [
                'user_id' => $userId,
                'request_id' => $requestId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with('error', 'Terjadi kesalahan. Silakan login secara manual.');
        }
    }

    /**
     * Generate signed URL for supplier auto-login (static helper)
     */
    public static function generateSignedLoginUrl($userId, $requestId, $expiresInHours = 72)
    {
        return URL::temporarySignedRoute(
            'auto-login',
            now()->addHours($expiresInHours),
            [
                'userId' => $userId,
                'requestId' => $requestId
            ]
        );
    }
}
