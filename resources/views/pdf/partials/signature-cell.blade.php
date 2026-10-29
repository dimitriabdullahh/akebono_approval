{{-- 
    Signature Cell Partial untuk PDF
    Menampilkan kolom tanda tangan dengan QR code e-sign
    
    Variables:
    - $role: role_type ('purchasing', 'ppc', 'qa', 'quality', 'approver', 'checker', 'receiver')
    - $requestDocument: RequestDocument model
    - $qrService: SignatureQrService instance (optional, untuk generate QR baru)
    - $width: lebar cell dalam pt (default: 60)
    - $height: tinggi cell dalam pt (default: 80)
    - $label: label custom (default: ucfirst role)
--}}

@php
    $role = $role ?? 'approver';
    $width = $width ?? 60;
    $height = $height ?? 80;
    $label = $label ?? ucfirst($role);
    
    // Get signature untuk role ini
    $signature = $requestDocument->signatures()
        ->where('role_type', $role)
        ->first();
@endphp

<div style="
    position: absolute;
    left: {{ $left ?? '0' }}pt;
    top: {{ $top ?? '0' }}pt;
    width: {{ $width }}pt;
    height: {{ $height }}pt;
    border: 0.5pt solid #000;
    padding: 2pt;
    text-align: center;
    font-family: Helvetica, Arial, sans-serif;
    font-size: 7pt;
    overflow: hidden;
">
    {{-- Header / Label --}}
    <div style="
        font-weight: bold;
        font-size: 8pt;
        margin-bottom: 2pt;
        height: 10pt;
        line-height: 10pt;
        border-bottom: 0.5pt solid #ccc;
    ">
        {{ $label }}
    </div>

    @if($signature)
        {{-- QR Code --}}
        @php
            $qrCode = null;
            if ($qrService && isset($qrService)) {
                try {
                    $qrCode = $qrService->generate(
                        $signature->npk,
                        $signature->name,
                        $signature->role_label
                    );
                } catch (\Exception $e) {
                    \Log::warning('Failed to generate QR for PDF signature cell', [
                        'signature_id' => $signature->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        @endphp

        @if($qrCode)
            <div style="
                margin: 2pt auto;
                width: {{ $width - 6 }}pt;
                height: {{ ($height - 30) * 0.6 }}pt;
                text-align: center;
            ">
                <img 
                    src="{{ $qrCode }}" 
                    style="
                        max-width: 100%;
                        max-height: 100%;
                        display: block;
                        margin: 0 auto;
                    "
                    alt="QR Code"
                >
            </div>
        @else
            {{-- Fallback jika QR tidak bisa di-generate --}}
            <div style="
                margin: 2pt auto;
                height: 20pt;
                line-height: 20pt;
                color: #999;
                font-size: 6pt;
            ">
                [QR Code]
            </div>
        @endif

        {{-- Nama Penandatangan --}}
        <div style="
            font-weight: bold;
            font-size: 6.5pt;
            margin-top: 2pt;
            line-height: 8pt;
            max-height: 16pt;
            overflow: hidden;
            word-break: break-word;
        ">
            {{ $signature->name ?? '' }}
        </div>

        {{-- NPK --}}
        <div style="
            font-size: 6pt;
            color: #666;
            margin-top: 1pt;
            line-height: 7pt;
        ">
            {{ $signature->npk ?? '' }}
        </div>

        {{-- Tanggal Tanda Tangan --}}
        <div style="
            font-size: 5pt;
            color: #999;
            margin-top: 1pt;
            line-height: 6pt;
        ">
            {{ $signature->signed_at ? $signature->signed_at->format('d/m/Y') : '' }}
        </div>
    @else
        {{-- Kosong (belum ditandatangani) --}}
        <div style="
            height: {{ $height - 18 }}pt;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ccc;
            font-size: 6pt;
        ">
            [Kosong]
        </div>
    @endif
</div>
