@extends('layouts.approval')

@section('title')
Link Tidak Ditemukan - AAIJ Approval System
@endsection

@section('content')
<div class="approval-preview" style="max-width: 600px; margin: 3rem auto; flex: none;">
    <div class="approval-card" style="margin: 0;">
        <div class="approval-card-header" style="background-color: #f8d7da; border-bottom-color: var(--danger-color); color: #721c24;">
            <i class="ti ti-alert-circle"></i>
            Link Tidak Ditemukan
        </div>
        <div class="approval-card-body text-center">
            <div style="font-size: 4rem; margin: 1rem 0;">
                <i class="ti ti-search-off"></i>
            </div>
            
            <h3 class="mb-3">{{ $message ?? 'Link approval tidak ditemukan.' }}</h3>
            
            <p class="text-muted mb-4">
                Link yang Anda gunakan mungkin:
            </p>
            
            <ul class="text-left mb-4" style="display: inline-block;">
                <li>Sudah kadaluarsa (berlaku selama 7 hari)</li>
                <li>Sudah pernah digunakan</li>
                <li>Sudah dibatalkan</li>
                <li>Tidak valid atau salah</li>
            </ul>
            
            <div class="alert alert-info mt-4">
                <strong>Saran:</strong> Hubungi admin atau PIC untuk mendapatkan link approval yang baru.
            </div>
        </div>
    </div>
</div>
@endsection
