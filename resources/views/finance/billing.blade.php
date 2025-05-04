@extends('components.dashboard.default')

@section('content')
<div class="card">
    <div class="card-header">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="card-title">
                <i class="ki-duotone ki-dollar fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                Student Billing Management
            </h1>
            <div>
                <button type="button" class="btn btn-primary" onclick="Livewire.emit('openBillCreateModal')">
                    <i class="ki-duotone ki-plus fs-2"></i> New Bill
                </button>
            </div>
        </div>
    </div>
    <div class="card-body">
        <!-- Embedding the Livewire component for student billing management -->
        <livewire:finance.student-billing-manager />
    </div>
</div>
@endsection