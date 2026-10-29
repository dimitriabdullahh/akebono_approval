@extends('layouts.approval')

@section('title')
Link Tidak Valid - AAIJ Approval System
@endsection

@section('content')
<div class="approval-preview" style="max-width: 600px; margin: 3rem auto; flex: none;">
    <div class="approval-card" style="margin: 0;">
        <div class="approval-card-header" style="background-color: #fff3cd; border-bottom-color: var(--warning-color); color: #856404;">
            <i class="ti ti-alert-triangle"></i>
            Link Tidak Valid
        </div>
        <div class="approval-card-body text-center">
            <div style="font-size: 4rem; margin: 1rem 0; color: var(--warning-color);">
                <i class="ti ti-clock-off"></i>
            </div>
            
            <h3 class="mb-3">{{ $message ?? 'Link approval tidak dapat digunakan.' }}</h3>
            
            @if($token && $token->accessed_at)
                <p class="text-muted mb-2">
                    Link pertama kali dibuka pada: <strong>{{ $token->accessed_at->format('d M Y H:i') }}</strong>
                </p>
            @endif
            
            @if($token && $token->used_at)
                <p class="text-muted mb-4">
                    Link telah digunakan oleh: <strong>{{ $token->user->name ?? 'User' }}</strong>
                </p>
            @endif
            
            <div class="alert alert-warning mt-4">
                <strong>Penyebab Umum:</strong>
                <ul class="text-left mb-0">
                    @if($token && $token->isExpired())
                        <li>Link sudah kadaluarsa</li>
                    @endif
                    @if($token && $token->used_at)
                        <li>Link sudah digunakan</li>
                    @endif
                    @if($token && $token->invalidated_at)
                        <li>Link telah dibatalkan</li>
                    @endif
                </ul>
            </div>
            
            <div class="mt-4">
                <p class="text-muted">
                    Hubungi admin atau PIC Akebono untuk mendapatkan link approval yang baru.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
