@extends('layouts.app')

@section('title', 'Request Detail - ' . $documentRequest->model)

@section('content')
<div class="container-fluid py-4">
    <!-- Back Button -->
    <div class="mb-3">
        <a href="{{ route('supplier.dashboard') }}" class="btn btn-outline-secondary">
            <i class="ti ti-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- Documents Table -->
    <div id="documents-container">
        @include('supplier.partials.documents-table', ['part' => $firstPart])
    </div>
</div>

@endsection
